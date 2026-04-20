<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DreReportExport implements FromCollection, WithHeadings
{
    /**
     * @param  array<int, array{dre_code: string, dre_description: string, schedulings: array}>  $schedulingsByDre
     * @param  array{total_agendamentos: int, total_geral: float, ticket_medio: float}  $totals
     */
    public function __construct(
        private array $schedulingsByDre,
        private array $totals,
    ) {}

    public function headings(): array
    {
        return [
            'Conta DRE',
            'Código DRE',
            'ID Agendamento',
            'Cliente',
            'Agenda',
            'Funcionário',
            'Data Início',
            'Data Fim',
            'Total',
        ];
    }

    public function collection(): Collection
    {
        $rows = collect();

        foreach ($this->schedulingsByDre as $group) {
            foreach ($group['schedulings'] as $scheduling) {
                $rows->push([
                    $group['dre_description'],
                    $group['dre_code'],
                    $scheduling['id'],
                    $scheduling['customer']['name'] ?? '-',
                    $scheduling['calendar']['name'] ?? '-',
                    $scheduling['employee']['name'] ?? '-',
                    Carbon::parse($scheduling['start_time'])->format('d/m/Y H:i'),
                    Carbon::parse($scheduling['end_time'])->format('d/m/Y H:i'),
                    $scheduling['total'],
                ]);
            }
        }

        return $rows;
    }
}
