<?php

namespace Database\Seeders;

use App\Models\Accounts;
use App\Models\Calendar;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Scheduling;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SchedulingSeeder extends Seeder
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
            $employee = Employee::where('tenant_id', $tenant->id)->first();
            $calendar = Calendar::where('tenant_id', $tenant->id)->first();
            $account = Accounts::where('tenant_id', $tenant->id)->first();
            $customer = Customer::where('tenant_id', $tenant->id)->first();

            if (! $employee || ! $calendar || ! $account || ! $customer) {
                $this->command->warn("Dependências não encontradas para o tenant {$tenant->name}. Execute os seeders anteriores primeiro.");

                continue;
            }

            $schedulings = [
                [
                    'code' => 'SCH-'.strtoupper(Str::random(8)),
                    'employee_id' => $employee->id,
                    'calendar_id' => $calendar->id,
                    'account_id' => $account->id,
                    'customer_id' => $customer->id,
                    'start_time' => now()->addDays(1)->setTime(9, 0),
                    'end_time' => now()->addDays(1)->setTime(10, 0),
                    'status' => 'pending',
                    'color' => '#3B82F6',
                    'duration' => 60.00,
                    'notes' => 'Consulta agendada',
                ],
                [
                    'code' => 'SCH-'.strtoupper(Str::random(8)),
                    'employee_id' => $employee->id,
                    'calendar_id' => $calendar->id,
                    'account_id' => $account->id,
                    'customer_id' => $customer->id,
                    'start_time' => now()->addDays(2)->setTime(14, 0),
                    'end_time' => now()->addDays(2)->setTime(15, 30),
                    'status' => 'confirmed',
                    'color' => '#10B981',
                    'duration' => 90.00,
                    'notes' => 'Consulta confirmada',
                ],
            ];

            foreach ($schedulings as $schedulingData) {
                Scheduling::firstOrCreate(
                    [
                        'code' => $schedulingData['code'],
                        'tenant_id' => $tenant->id,
                    ],
                    array_merge($schedulingData, ['tenant_id' => $tenant->id])
                );
            }

            $this->command->info("Agendamentos criados para o tenant {$tenant->name}");
        }

        $this->command->info('Agendamentos criados com sucesso!');
    }
}
