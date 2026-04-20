<?php

namespace App\Actions\Report\Concerns;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\ActionRequest;

trait ServiceReportData
{
    /**
     * @return array{services: array, summary: array, filters: array}
     */
    protected function getServiceReportData(ActionRequest $request): array
    {
        $calendarId = $request->input('calendar');
        $employeeId = $request->input('employee');
        $dtIni = $request->input('dt_ini');
        $dtFim = $request->input('dt_fim');

        $services = $this->getServiceMetrics($calendarId, $employeeId, $dtIni, $dtFim);
        $summary = $this->calculateServiceSummary($services);

        return [
            'services' => $services,
            'summary' => $summary,
            'filters' => [
                'calendar' => $calendarId,
                'employee' => $employeeId,
                'dt_ini' => $dtIni,
                'dt_fim' => $dtFim,
            ],
        ];
    }

    protected function getServiceMetrics(?int $calendarId = null, ?int $employeeId = null, ?string $dtIni = null, ?string $dtFim = null): array
    {
        $query = DB::table('scheduling_items')
            ->join('schedulings', 'scheduling_items.scheduling_id', '=', 'schedulings.id')
            ->join('services', 'scheduling_items.service_id', '=', 'services.id')
            ->where('schedulings.status', '!=', 'cancelled')
            ->whereNull('schedulings.deleted_at')
            ->whereNull('scheduling_items.deleted_at');

        if ($calendarId) {
            $query->where('schedulings.calendar_id', $calendarId);
        }

        if ($employeeId) {
            $query->where('schedulings.employee_id', $employeeId);
        }

        if ($dtIni) {
            $query->where('schedulings.start_time', '>=', Carbon::parse($dtIni));
        }

        if ($dtFim) {
            $query->where('schedulings.end_time', '<=', Carbon::parse($dtFim));
        }

        $results = $query
            ->groupBy('services.id', 'services.name', 'services.price')
            ->selectRaw("
                services.id as service_id,
                services.name as service_name,
                services.price as catalog_price,
                SUM(scheduling_items.quantity) as times_booked,
                COALESCE(SUM(scheduling_items.total_amount), 0) as total_revenue,
                COALESCE(AVG(scheduling_items.unit_amount), 0) as avg_price,
                COALESCE(SUM(scheduling_items.discount), 0) as total_discount
            ")
            ->orderByDesc('total_revenue')
            ->get();

        $grandTotal = $results->sum('total_revenue');

        return $results->map(function ($row) use ($grandTotal) {
            return [
                'service_id' => (int) $row->service_id,
                'service_name' => $row->service_name,
                'catalog_price' => (float) $row->catalog_price,
                'times_booked' => (int) $row->times_booked,
                'total_revenue' => (float) $row->total_revenue,
                'avg_price' => (float) $row->avg_price,
                'total_discount' => (float) $row->total_discount,
                'revenue_share' => $grandTotal > 0
                    ? round(((float) $row->total_revenue / $grandTotal) * 100, 1)
                    : 0,
            ];
        })->toArray();
    }

    /**
     * @return array{total_services: int, total_bookings: int, total_revenue: float, avg_price: float}
     */
    protected function calculateServiceSummary(array $services): array
    {
        $totalBookings = array_sum(array_column($services, 'times_booked'));
        $totalRevenue = array_sum(array_column($services, 'total_revenue'));

        return [
            'total_services' => count($services),
            'total_bookings' => $totalBookings,
            'total_revenue' => $totalRevenue,
            'avg_price' => $totalBookings > 0 ? round($totalRevenue / $totalBookings, 2) : 0,
        ];
    }
}
