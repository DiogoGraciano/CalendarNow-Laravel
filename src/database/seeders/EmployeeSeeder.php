<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
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
            // Criar usuário para o funcionário se não existir
            $user = User::firstOrCreate(
                [
                    'email' => "funcionario@{$tenant->id}.com",
                    'tenant_id' => $tenant->id,
                ],
                [
                    'name' => "Funcionário - {$tenant->name}",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'tenant_id' => $tenant->id,
                ]
            );

            $employees = [
                [
                    'user_id' => $user->id,
                    'cpf_cnpj' => '123.456.789-00',
                    'email' => "funcionario@{$tenant->id}.com",
                    'phone' => '(11) 88888-8888',
                    'status' => 'working',
                    'gender' => 'male',
                    'birth_date' => '1990-01-15',
                    'admission_date' => now(),
                    'work_start_date' => now(),
                    'salary' => 5000.00,
                    'pay_day' => 5,
                    'work_days' => [1, 2, 3, 4, 5],
                ],
            ];

            foreach ($employees as $employeeData) {
                $employee = Employee::firstOrCreate(
                    [
                        'cpf_cnpj' => $employeeData['cpf_cnpj'],
                        'tenant_id' => $tenant->id,
                    ],
                    array_merge($employeeData, ['tenant_id' => $tenant->id])
                );

                $this->command->info("Funcionário criado: {$employee->email} para o tenant {$tenant->name}");
            }
        }

        $this->command->info('Funcionários criados com sucesso!');
    }
}
