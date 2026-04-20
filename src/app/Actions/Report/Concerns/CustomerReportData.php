<?php

namespace App\Actions\Report\Concerns;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\ActionRequest;

trait CustomerReportData
{
    /**
     * @return array{customers: array, newVsReturning: array, summary: array, filters: array}
     */
    protected function getCustomerReportData(ActionRequest $request): array
    {
        $dtIni = $request->input('dt_ini');
        $dtFim = $request->input('dt_fim');
        $minVisits = $request->input('min_visits');

        $customers = $this->getCustomerMetrics($dtIni, $dtFim, $minVisits);
        $newVsReturning = $this->getNewVsReturning($dtIni, $dtFim, $customers);
        $summary = $this->calculateCustomerSummary($customers);

        return [
            'customers' => $customers,
            'newVsReturning' => $newVsReturning,
            'summary' => $summary,
            'filters' => [
                'dt_ini' => $dtIni,
                'dt_fim' => $dtFim,
                'min_visits' => $minVisits,
            ],
        ];
    }

    protected function getCustomerMetrics(?string $dtIni = null, ?string $dtFim = null, ?int $minVisits = null): array
    {
        $query = DB::table('schedulings')
            ->join('scheduling_items', 'schedulings.id', '=', 'scheduling_items.scheduling_id')
            ->join('customers', 'schedulings.customer_id', '=', 'customers.id')
            ->where('schedulings.status', '!=', 'cancelled')
            ->whereNull('schedulings.deleted_at')
            ->whereNull('scheduling_items.deleted_at')
            ->whereNull('customers.deleted_at');

        if ($dtIni) {
            $query->where('schedulings.start_time', '>=', Carbon::parse($dtIni));
        }

        if ($dtFim) {
            $query->where('schedulings.end_time', '<=', Carbon::parse($dtFim));
        }

        $query->groupBy('customers.id', 'customers.name', 'customers.email', 'customers.phone');

        if ($minVisits) {
            $query->havingRaw('COUNT(DISTINCT schedulings.id) >= ?', [$minVisits]);
        }

        $results = $query
            ->selectRaw("
                customers.id as customer_id,
                customers.name as customer_name,
                customers.email as customer_email,
                customers.phone as customer_phone,
                COUNT(DISTINCT schedulings.id) as visit_count,
                COALESCE(SUM(scheduling_items.total_amount), 0) as total_spent,
                COALESCE(AVG(scheduling_items.total_amount), 0) as avg_ticket,
                MAX(schedulings.start_time) as last_visit
            ")
            ->orderByDesc('total_spent')
            ->get();

        return $results->map(function ($row) {
            return [
                'customer_id' => (int) $row->customer_id,
                'customer_name' => $row->customer_name,
                'customer_email' => $row->customer_email,
                'customer_phone' => $row->customer_phone,
                'visit_count' => (int) $row->visit_count,
                'total_spent' => (float) $row->total_spent,
                'avg_ticket' => (float) $row->avg_ticket,
                'last_visit' => $row->last_visit,
            ];
        })->toArray();
    }

    /**
     * @return array{new_customers: int, returning_customers: int}
     */
    protected function getNewVsReturning(?string $dtIni, ?string $dtFim, array $customers): array
    {
        if (empty($customers)) {
            return ['new_customers' => 0, 'returning_customers' => 0];
        }

        $customerIds = array_column($customers, 'customer_id');

        $firstVisits = DB::table('schedulings')
            ->where('status', '!=', 'cancelled')
            ->whereNull('deleted_at')
            ->whereIn('customer_id', $customerIds)
            ->groupBy('customer_id')
            ->selectRaw('customer_id, MIN(start_time) as first_visit')
            ->pluck('first_visit', 'customer_id');

        $newCount = 0;
        $returningCount = 0;

        foreach ($customerIds as $customerId) {
            $firstVisit = $firstVisits[$customerId] ?? null;

            if (! $firstVisit) {
                continue;
            }

            $isNew = true;

            if ($dtIni) {
                $isNew = Carbon::parse($firstVisit)->gte(Carbon::parse($dtIni));
            }

            if ($isNew) {
                $newCount++;
            } else {
                $returningCount++;
            }
        }

        return [
            'new_customers' => $newCount,
            'returning_customers' => $returningCount,
        ];
    }

    /**
     * @return array{total_customers: int, total_visits: int, total_revenue: float, avg_ticket: float}
     */
    protected function calculateCustomerSummary(array $customers): array
    {
        $totalVisits = array_sum(array_column($customers, 'visit_count'));
        $totalRevenue = array_sum(array_column($customers, 'total_spent'));

        return [
            'total_customers' => count($customers),
            'total_visits' => $totalVisits,
            'total_revenue' => $totalRevenue,
            'avg_ticket' => $totalVisits > 0 ? round($totalRevenue / $totalVisits, 2) : 0,
        ];
    }
}
