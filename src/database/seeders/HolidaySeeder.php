<?php

namespace Database\Seeders;

use App\Models\Holiday;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
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

        $holidays = [
            [
                'name' => 'Confraternização Universal',
                'date' => '2026-01-01',
                'recurring' => true,
            ],
            [
                'name' => 'Carnaval',
                'date' => '2026-02-16',
                'recurring' => false,
            ],
            [
                'name' => 'Carnaval',
                'date' => '2026-02-17',
                'recurring' => false,
            ],
            [
                'name' => 'Sexta-feira Santa',
                'date' => '2026-04-03',
                'recurring' => false,
            ],
            [
                'name' => 'Tiradentes',
                'date' => '2026-04-21',
                'recurring' => true,
            ],
            [
                'name' => 'Dia do Trabalho',
                'date' => '2026-05-01',
                'recurring' => true,
            ],
            [
                'name' => 'Corpus Christi',
                'date' => '2026-06-04',
                'recurring' => false,
            ],
            [
                'name' => 'Independência do Brasil',
                'date' => '2026-09-07',
                'recurring' => true,
            ],
            [
                'name' => 'Nossa Senhora Aparecida',
                'date' => '2026-10-12',
                'recurring' => true,
            ],
            [
                'name' => 'Finados',
                'date' => '2026-11-02',
                'recurring' => true,
            ],
            [
                'name' => 'Proclamação da República',
                'date' => '2026-11-15',
                'recurring' => true,
            ],
            [
                'name' => 'Natal',
                'date' => '2026-12-25',
                'recurring' => true,
            ],
        ];

        foreach ($tenants as $tenant) {
            foreach ($holidays as $holidayData) {
                Holiday::firstOrCreate(
                    [
                        'name' => $holidayData['name'],
                        'date' => $holidayData['date'],
                        'tenant_id' => $tenant->id,
                    ],
                    array_merge($holidayData, ['tenant_id' => $tenant->id])
                );
            }

            $this->command->info("Feriados criados para o tenant {$tenant->name}");
        }

        $this->command->info('Feriados criados com sucesso!');
    }
}
