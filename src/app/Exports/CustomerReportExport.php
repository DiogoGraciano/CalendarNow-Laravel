<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustomerReportExport implements FromCollection, WithHeadings
{
    /**
     * @param  array<int, array{customer_name: string, customer_email: ?string, customer_phone: ?string, visit_count: int, total_spent: float, avg_ticket: float, last_visit: string}>  $customers
     * @param  array{total_customers: int, total_visits: int, total_revenue: float, avg_ticket: float}  $summary
     */
    public function __construct(
        private array $customers,
        private array $summary,
    ) {}

    public function headings(): array
    {
        return [
            'Cliente',
            'Email',
            'Telefone',
            'Visitas',
            'Total Gasto',
            'Ticket Médio',
            'Última Visita',
        ];
    }

    public function collection(): Collection
    {
        $rows = collect();

        foreach ($this->customers as $customer) {
            $rows->push([
                $customer['customer_name'],
                $customer['customer_email'] ?? '-',
                $customer['customer_phone'] ?? '-',
                $customer['visit_count'],
                $customer['total_spent'],
                $customer['avg_ticket'],
                $customer['last_visit'] ? Carbon::parse($customer['last_visit'])->format('d/m/Y H:i') : '-',
            ]);
        }

        return $rows;
    }
}
