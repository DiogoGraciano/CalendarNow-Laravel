<?php

namespace Database\Seeders;

use App\Models\Scheduling;
use App\Models\SchedulingItem;
use App\Models\Service;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class SchedulingItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->command->warn('Nenhum tenant encontrado. Execute TenantSeeder primeiro.');

            return;
        }

        foreach ($tenants as $tenant) {
            $scheduling = Scheduling::where('tenant_id', $tenant->id)->first();
            $service = Service::where('tenant_id', $tenant->id)->first();

            if (! $scheduling || ! $service) {
                $this->command->warn("Agendamento ou Serviço não encontrado para o tenant {$tenant->name}. Execute os seeders anteriores primeiro.");

                continue;
            }

            $items = [
                [
                    'tenant_id' => $tenant->id,
                    'scheduling_id' => $scheduling->id,
                    'service_id' => $service->id,
                    'total_amount' => 150.00,
                    'unit_amount' => 150.00,
                    'discount' => 0.00,
                    'quantity' => 1,
                    'duration' => '01:00:00',
                ],
            ];

            foreach ($items as $itemData) {
                SchedulingItem::firstOrCreate(
                    [
                        'tenant_id' => $itemData['tenant_id'],
                        'scheduling_id' => $itemData['scheduling_id'],
                        'service_id' => $itemData['service_id'],
                    ],
                    $itemData
                );
            }

            $this->command->info("Itens de agendamento criados para o tenant {$tenant->name}");
        }

        $this->command->info('Itens de agendamento criados com sucesso!');
    }
}
