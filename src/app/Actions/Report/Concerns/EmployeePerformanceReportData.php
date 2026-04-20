<?php

namespace App\Actions\Report\Concerns;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\ActionRequest;

trait EmployeePerformanceReportData
{
    /**
     * @return array{employees: array, summary: array, filters: array}
     */
    protected function getEmployeePerformanceData(ActionRequest $request): array
    {
        $employeeId = $request->input('employee');
        $serviceId = $request->input('service');
        $dtIni = $request->input('dt_ini');
        $dtFim = $request->input('dt_fim');

        $employees = $this->getEmployeeMetrics($employeeId, $serviceId, $dtIni, $dtFim);
        $summary = $this->calculateEmployeeSummary($employees);

        return [
            'employees' => $employees,
            'summary' => $summary,
            'filters' => [
                'employee' => $employeeId,
                'service' => $serviceId,
                'dt_ini' => $dtIni,
                'dt_fim' => $dtFim,
            ],
        ];
    }

    protected function getEmployeeMetrics(?int $employeeId = null, ?int $serviceId = null, ?string $dtIni = null, ?string $dtFim = null): array
    {
        $query = DB::table('schedulings')
            ->join('scheduling_items', 'schedulings.id', '=', 'scheduling_items.scheduling_id')
            ->join('employees', 'schedulings.employee_id', '=', 'employees.id')
            ->join('users', 'employees.user_id', '=', 'users.id')
            ->whereNull('schedulings.deleted_at')
            ->whereNull('scheduling_items.deleted_at');

        if ($employeeId) {
            $query->where('schedulings.employee_id', $employeeId);
        }

        if ($serviceId) {
            $query->where('scheduling_items.service_id', $serviceId);
        }

        if ($dtIni) {
            $query->where('schedulings.start_time', '>=', Carbon::parse($dtIni));
        }

        if ($dtFim) {
            $query->where('schedulings.end_time', '<=', Carbon::parse($dtFim));
        }

        $results = $query
            ->groupBy('employees.id', 'users.name')
            ->selectRaw("
                employees.id as employee_id,
                users.name as employee_name,
                COUNT(DISTINCT schedulings.id) as total_schedulings,
                COUNT(DISTINCT CASE WHEN schedulings.status = 'cancelled' THEN schedulings.id END) as cancelled_count,
                COUNT(DISTINCT CASE WHEN schedulings.status != 'cancelled' THEN schedulings.id END) as completed_count,
                COALESCE(SUM(CASE WHEN schedulings.status != 'cancelled' THEN scheduling_items.total_amount ELSE 0 END), 0) as total_revenue,
                COALESCE(AVG(CASE WHEN schedulings.status != 'cancelled' THEN scheduling_items.total_amount ELSE NULL END), 0) as avg_ticket
            ")
            ->orderByDesc('total_revenue')
            ->get();

        return $results->map(function ($row) {
            $totalSchedulings = (int) $row->total_schedulings;
            $cancelledCount = (int) $row->cancelled_count;

            return [
                'employee_id' => (int) $row->employee_id,
                'employee_name' => $row->employee_name,
                'total_schedulings' => $totalSchedulings,
                'completed_count' => (int) $row->completed_count,
                'cancelled_count' => $cancelledCount,
                'cancellation_rate' => $totalSchedulings > 0
                    ? round(($cancelledCount / $totalSchedulings) * 100, 1)
                    : 0,
                'total_revenue' => (float) $row->total_revenue,
                'avg_ticket' => (float) $row->avg_ticket,
            ];
        })->toArray();
    }

    /**
     * @return array{total_employees: int, total_schedulings: int, total_revenue: float, avg_ticket: float}
     */
    protected function calculateEmployeeSummary(array $employees): array
    {
        $totalSchedulings = array_sum(array_column($employees, 'completed_count'));
        $totalRevenue = array_sum(array_column($employees, 'total_revenue'));

        return [
            'total_employees' => count($employees),
            'total_schedulings' => $totalSchedulings,
            'total_revenue' => $totalRevenue,
            'avg_ticket' => $totalSchedulings > 0 ? round($totalRevenue / $totalSchedulings, 2) : 0,
        ];
    }
}
