<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Service;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class EmployeeServiceSeeder extends Seeder
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
            $serviceIds = Service::where('tenant_id', $tenant->id)->pluck('id')->all();

            if ($employees->isEmpty() || empty($serviceIds)) {
                $this->command->warn("Tenant {$tenant->name}: sem funcionários ou serviços para vincular.");

                continue;
            }

            foreach ($employees as $employee) {
                $employee->services()->sync($serviceIds);
            }

            $this->command->info("Vínculos employee-service criados para o tenant {$tenant->name}");
        }

        $this->command->info('Seeder EmployeeService concluído.');
    }
}
