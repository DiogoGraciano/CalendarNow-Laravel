<?php

namespace Database\Seeders;

use App\Models\Calendar;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CalendarSeeder extends Seeder
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

        $calendars = [
            ['name' => 'Calendário Principal'],
            ['name' => 'Calendário Secundário'],
        ];

        foreach ($tenants as $tenant) {
            foreach ($calendars as $calendarData) {
                Calendar::firstOrCreate(
                    [
                        'name' => $calendarData['name'],
                        'tenant_id' => $tenant->id,
                    ],
                    array_merge($calendarData, [
                        'code' => 'CAL-'.strtoupper(Str::random(8)),
                        'tenant_id' => $tenant->id,
                    ])
                );
            }

            $this->command->info("Calendários criados para o tenant {$tenant->name}");
        }

        $this->command->info('Calendários criados com sucesso!');
    }
}
