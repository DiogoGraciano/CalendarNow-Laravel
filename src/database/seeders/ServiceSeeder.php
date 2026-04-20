<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
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

        $services = [
            [
                'name' => 'Consulta Básica',
                'description' => 'Consulta padrão com duração de 1 hora',
                'price' => 150.00,
                'duration' => '01:00:00',
                'order' => 1,
            ],
            [
                'name' => 'Consulta Avançada',
                'description' => 'Consulta detalhada com duração de 2 horas',
                'price' => 300.00,
                'duration' => '02:00:00',
                'order' => 2,
            ],
            [
                'name' => 'Avaliação Inicial',
                'description' => 'Primeira avaliação do cliente',
                'price' => 200.00,
                'duration' => '01:30:00',
                'order' => 3,
            ],
        ];

        foreach ($tenants as $tenant) {
            foreach ($services as $serviceData) {
                Service::firstOrCreate(
                    [
                        'name' => $serviceData['name'],
                        'tenant_id' => $tenant->id,
                    ],
                    array_merge($serviceData, ['tenant_id' => $tenant->id])
                );
            }

            $this->command->info("Serviços criados para o tenant {$tenant->name}");
        }

        $this->command->info('Serviços criados com sucesso!');
    }
}
