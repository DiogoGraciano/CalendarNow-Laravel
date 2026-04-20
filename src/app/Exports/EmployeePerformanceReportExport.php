<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EmployeePerformanceReportExport implements FromCollection, WithHeadings
{
    /**
     * @param  array<int, array{employee_name: string, total_schedulings: int, completed_count: int, cancelled_count: int, cancellation_rate: float, total_revenue: float, avg_ticket: float}>  $employees
     * @param  array{total_employees: int, total_schedulings: int, total_revenue: float, avg_ticket: float}  $summary
     */
    public function __construct(
        private array $employees,
        private array $summary,
    ) {}

    public function headings(): array
    {
        return [
            'Funcionário',
            'Total Agendamentos',
            'Concluídos',
            'Cancelados',
            'Taxa Cancelamento (%)',
            'Receita Total',
            'Ticket Médio',
        ];
    }

    public function collection(): Collection
    {
        $rows = collect();

        foreach ($this->employees as $employee) {
            $rows->push([
                $employee['employee_name'],
                $employee['total_schedulings'],
                $employee['completed_count'],
                $employee['cancelled_count'],
                $employee['cancellation_rate'],
                $employee['total_revenue'],
                $employee['avg_ticket'],
            ]);
        }

        return $rows;
    }
}
