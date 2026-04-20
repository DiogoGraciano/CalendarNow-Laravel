<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ServiceReportExport implements FromCollection, WithHeadings
{
    /**
     * @param  array<int, array{service_name: string, catalog_price: float, times_booked: int, total_revenue: float, avg_price: float, total_discount: float, revenue_share: float}>  $services
     * @param  array{total_services: int, total_bookings: int, total_revenue: float, avg_price: float}  $summary
     */
    public function __construct(
        private array $services,
        private array $summary,
    ) {}

    public function headings(): array
    {
        return [
            'Serviço',
            'Preço Catálogo',
            'Vezes Agendado',
            'Receita Total',
            'Preço Médio',
            'Desconto Total',
            'Participação (%)',
        ];
    }

    public function collection(): Collection
    {
        $rows = collect();

        foreach ($this->services as $service) {
            $rows->push([
                $service['service_name'],
                $service['catalog_price'],
                $service['times_booked'],
                $service['total_revenue'],
                $service['avg_price'],
                $service['total_discount'],
                $service['revenue_share'],
            ]);
        }

        return $rows;
    }
}
