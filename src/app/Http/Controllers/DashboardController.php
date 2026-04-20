<?php

namespace App\Http\Controllers;

use App\Models\Scheduling;
use App\Models\SchedulingItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index(Request $request): Response
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // Total de agendamentos do mês
        $totalAppointments = Scheduling::whereBetween('start_time', [$startOfMonth, $endOfMonth])
            ->count();

        // Serviço mais utilizado do mês
        $mostUsedService = SchedulingItem::with('service')
            ->whereHas('scheduling', function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('start_time', [$startOfMonth, $endOfMonth]);
            })
            ->selectRaw('service_id, SUM(quantity) as total_quantity')
            ->groupBy('service_id')
            ->orderByDesc('total_quantity')
            ->first();

        $serviceName = 'Nenhum';
        $serviceCount = 0;
        if ($mostUsedService && $mostUsedService->service) {
            $serviceName = $mostUsedService->service->name;
            $serviceCount = $mostUsedService->total_quantity;
        }

        // Faturamento total do mês
        $totalRevenue = SchedulingItem::whereHas('scheduling', function ($query) use ($startOfMonth, $endOfMonth) {
            $query->whereBetween('start_time', [$startOfMonth, $endOfMonth]);
        })
            ->sum('total_amount');

        // Agendamentos diários do mês
        $dailyAppointments = Scheduling::whereBetween('start_time', [$startOfMonth, $endOfMonth])
            ->selectRaw('DATE(start_time) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('d'),
                    'count' => (int) $item->count,
                ];
            });

        // Preencher dias sem agendamentos com 0
        $dailyAppointmentsMap = $dailyAppointments->pluck('count', 'date')->toArray();
        $allDays = [];
        for ($day = 1; $day <= $endOfMonth->day; $day++) {
            $dayStr = str_pad($day, 2, '0', STR_PAD_LEFT);
            $allDays[] = [
                'date' => $dayStr,
                'count' => $dailyAppointmentsMap[$dayStr] ?? 0,
            ];
        }

        // Faturamento diário do mês
        $dailyRevenue = SchedulingItem::join('schedulings', 'scheduling_items.scheduling_id', '=', 'schedulings.id')
            ->whereBetween('schedulings.start_time', [$startOfMonth, $endOfMonth])
            ->selectRaw('DATE(schedulings.start_time) as date, SUM(scheduling_items.total_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('d'),
                    'total' => (float) $item->total,
                ];
            });

        // Preencher dias sem faturamento com 0
        $dailyRevenueMap = $dailyRevenue->pluck('total', 'date')->toArray();
        $allDaysRevenue = [];
        for ($day = 1; $day <= $endOfMonth->day; $day++) {
            $dayStr = str_pad($day, 2, '0', STR_PAD_LEFT);
            $allDaysRevenue[] = [
                'date' => $dayStr,
                'total' => $dailyRevenueMap[$dayStr] ?? 0,
            ];
        }

        // Distribuição de serviços (para gráfico de pizza)
        $servicesDistribution = SchedulingItem::with('service')
            ->whereHas('scheduling', function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('start_time', [$startOfMonth, $endOfMonth]);
            })
            ->selectRaw('service_id, SUM(quantity) as total_quantity')
            ->groupBy('service_id')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->service ? $item->service->name : 'Serviço Desconhecido',
                    'value' => (int) $item->total_quantity,
                ];
            });

        // Se não houver serviços, criar um item padrão
        if ($servicesDistribution->isEmpty()) {
            $servicesDistribution = collect([
                [
                    'name' => 'Nenhum serviço',
                    'value' => 0,
                ],
            ]);
        }

        // Dados para gráfico de linha (tendência - usando agendamentos diários)
        $trendData = $allDays;

        return Inertia::render('dashboard', [
            'stats' => [
                'totalAppointments' => $totalAppointments,
                'mostUsedService' => [
                    'name' => $serviceName,
                    'count' => $serviceCount,
                ],
                'totalRevenue' => (float) $totalRevenue,
                'dateRange' => [
                    'start' => $startOfMonth->format('d/m/Y'),
                    'end' => $endOfMonth->format('d/m/Y'),
                ],
            ],
            'charts' => [
                'dailyAppointments' => $allDays,
                'dailyRevenue' => $allDaysRevenue,
                'servicesDistribution' => $servicesDistribution,
                'trend' => $trendData,
            ],
        ]);
    }
}
