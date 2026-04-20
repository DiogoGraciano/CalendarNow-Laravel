<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeDayOff;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class EmployeeDayOffSeeder extends Seeder
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
            $employees = Employee::where('tenant_id', $tenant->id)->get();

            if ($employees->isEmpty()) {
                $this->command->warn("Nenhum funcionário encontrado para o tenant {$tenant->name}. Execute EmployeeSeeder primeiro.");

                continue;
            }

            foreach ($employees as $employee) {
                $daysOff = [
                    [
                        'start_date' => '2026-04-06',
                        'end_date' => '2026-04-06',
                        'type' => 'day_off',
                        'reason' => 'Folga semanal compensada',
                    ],
                    [
                        'start_date' => '2026-07-01',
                        'end_date' => '2026-07-15',
                        'type' => 'vacation',
                        'reason' => 'Férias de julho',
                    ],
                    [
                        'start_date' => '2026-05-15',
                        'end_date' => '2026-05-15',
                        'type' => 'personal',
                        'reason' => 'Consulta médica pessoal',
                    ],
                    [
                        'start_date' => '2026-08-10',
                        'end_date' => '2026-08-12',
                        'type' => 'medical_leave',
                        'reason' => 'Atestado médico',
                        'notes' => 'Apresentou atestado de 3 dias',
                    ],
                ];

                foreach ($daysOff as $dayOffData) {
                    EmployeeDayOff::firstOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'start_date' => $dayOffData['start_date'],
                            'end_date' => $dayOffData['end_date'],
                            'tenant_id' => $tenant->id,
                        ],
                        array_merge($dayOffData, [
                            'employee_id' => $employee->id,
                            'tenant_id' => $tenant->id,
                        ])
                    );
                }

                $this->command->info("Folgas criadas para {$employee->email} no tenant {$tenant->name}");
            }
        }

        $this->command->info('Folgas criadas com sucesso!');
    }
}
