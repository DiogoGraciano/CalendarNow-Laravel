<?php

namespace App\Actions\Report\Concerns;

use App\Models\Scheduling;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\ActionRequest;

trait DreReportData
{
    /**
     * @return array{schedulingsByDre: array, totals: array, filters: array}
     */
    protected function getDreReportData(ActionRequest $request): array
    {
        $calendarId = $request->input('calendar');
        $employeeId = $request->input('employee');
        $dtIni = $request->input('dt_ini');
        $dtFim = $request->input('dt_fim');

        $schedulings = $this->getFilteredSchedulings($calendarId, $employeeId, $dtIni, $dtFim);
        $groupedByDre = $this->groupByDre($schedulings);
        $totals = $this->calculateTotals($calendarId, $employeeId, $dtIni, $dtFim);

        return [
            'schedulingsByDre' => $groupedByDre,
            'totals' => $totals,
            'filters' => [
                'calendar' => $calendarId,
                'employee' => $employeeId,
                'dt_ini' => $dtIni,
                'dt_fim' => $dtFim,
            ],
        ];
    }

    protected function getFilteredSchedulings(?int $calendarId = null, ?int $employeeId = null, ?string $dtIni = null, ?string $dtFim = null)
    {
        $query = Scheduling::with(['calendar', 'employee.user', 'customer', 'items'])
            ->where('status', '!=', 'cancelled');

        if ($calendarId) {
            $query->where('calendar_id', $calendarId);
        }

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        if ($dtIni) {
            $query->where('start_time', '>=', Carbon::parse($dtIni));
        }

        if ($dtFim) {
            $query->where('end_time', '<=', Carbon::parse($dtFim));
        }

        return $query->latest('start_time')->get();
    }

    /**
     * @return array{total_agendamentos: int, total_geral: float, ticket_medio: float}
     */
    protected function calculateTotals(?int $calendarId = null, ?int $employeeId = null, ?string $dtIni = null, ?string $dtFim = null): array
    {
        $query = DB::table('schedulings')
            ->join('scheduling_items', 'schedulings.id', '=', 'scheduling_items.scheduling_id')
            ->join('services', 'scheduling_items.service_id', '=', 'services.id')
            ->where('schedulings.status', '!=', 'cancelled');

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

        $result = $query->selectRaw('
                COUNT(DISTINCT schedulings.id) as total_agendamentos,
                COALESCE(SUM(scheduling_items.total_amount), 0) as total_geral,
                COALESCE(AVG(scheduling_items.total_amount), 0) as ticket_medio
            ')
            ->first();

        return [
            'total_agendamentos' => (int) ($result->total_agendamentos ?? 0),
            'total_geral' => (float) ($result->total_geral ?? 0),
            'ticket_medio' => (float) ($result->ticket_medio ?? 0),
        ];
    }

    protected function groupByDre($schedulings): array
    {
        $grouped = [];
        $noDreGroup = [
            'dre' => null,
            'dre_code' => '0',
            'dre_description' => 'SEM DRE CADASTRADO',
            'total' => 0,
            'schedulings' => [],
        ];

        foreach ($schedulings as $scheduling) {
            $total = $scheduling->items->sum('total_amount');

            $dre = null;
            if ($scheduling->account_id) {
                $account = \App\Models\Accounts::with('dre')->find($scheduling->account_id);
                if ($account?->dre) {
                    $dre = $account->dre;
                }
            }

            $schedulingData = [
                'id' => $scheduling->id,
                'code' => $scheduling->code,
                'start_time' => $scheduling->start_time->toIso8601String(),
                'end_time' => $scheduling->end_time->toIso8601String(),
                'customer' => $scheduling->customer ? [
                    'id' => $scheduling->customer->id,
                    'name' => $scheduling->customer->name,
                ] : null,
                'calendar' => $scheduling->calendar ? [
                    'id' => $scheduling->calendar->id,
                    'name' => $scheduling->calendar->name,
                ] : null,
                'employee' => $scheduling->employee ? [
                    'id' => $scheduling->employee->id,
                    'name' => $scheduling->employee->user?->name ?? "Funcionário #{$scheduling->employee->id}",
                ] : null,
                'total' => $total,
            ];

            if ($dre) {
                $key = $dre->id;

                if (! isset($grouped[$key])) {
                    $grouped[$key] = [
                        'dre' => [
                            'id' => $dre->id,
                            'code' => $dre->code,
                            'description' => $dre->description,
                            'type' => $dre->type,
                        ],
                        'dre_code' => $dre->code,
                        'dre_description' => $dre->description,
                        'total' => 0,
                        'schedulings' => [],
                    ];
                }

                $grouped[$key]['total'] += $total;
                $grouped[$key]['schedulings'][] = $schedulingData;
            } else {
                $noDreGroup['total'] += $total;
                $noDreGroup['schedulings'][] = $schedulingData;
            }
        }

        if (count($noDreGroup['schedulings']) > 0) {
            $grouped[0] = $noDreGroup;
        }

        usort($grouped, function ($a, $b) {
            return strcmp($a['dre_code'], $b['dre_code']);
        });

        return array_values($grouped);
    }
}
