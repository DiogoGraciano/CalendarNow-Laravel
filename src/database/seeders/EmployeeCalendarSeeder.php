<?php

namespace Database\Seeders;

use App\Models\Calendar;
use App\Models\Employee;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class EmployeeCalendarSeeder extends Seeder
{
    /**
     * Vincula funcionários às agendas do tenant e define uma agenda pública por funcionário.
     * Deve rodar após EmployeeSeeder e CalendarSeeder.
     */
    public function run(): void
    {
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->command->warn('Nenhum tenant encontrado. Execute TenantSeeder primeiro.');

            return;
        }

        foreach ($tenants as $tenant) {
            $employees = Employee::where('tenant_id', $tenant->id)->get();
            $calendars = Calendar::where('tenant_id', $tenant->id)->orderBy('id')->get();

            if ($employees->isEmpty() || $calendars->isEmpty()) {
                $this->command->warn("Tenant {$tenant->name}: sem funcionários ou calendários para vincular.");

                continue;
            }

            $publicCalendar = $calendars->first();

            foreach ($employees as $employee) {
                $sync = [];
                foreach ($calendars as $calendar) {
                    $sync[$calendar->id] = ['is_public' => $calendar->id === $publicCalendar->id];
                }
                $employee->calendars()->sync($sync);
            }

            $this->command->info("Vínculos employee-calendar criados para o tenant {$tenant->name}");
        }

        $this->command->info('Seeder EmployeeCalendar concluído.');
    }
}
