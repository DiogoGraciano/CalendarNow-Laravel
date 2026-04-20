<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
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
            // Criar usuário para o cliente se não existir
            $user = User::firstOrCreate(
                [
                    'email' => "cliente@{$tenant->id}.com",
                    'tenant_id' => $tenant->id,
                ],
                [
                    'name' => "Cliente - {$tenant->name}",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'tenant_id' => $tenant->id,
                ]
            );

            $customers = [
                [
                    'user_id' => $user->id,
                    'name' => "Cliente - {$tenant->name}",
                    'email' => "cliente@{$tenant->id}.com",
                    'phone' => '(11) 99999-9999',
                    'address' => 'Rua Exemplo, 123',
                    'city' => 'São Paulo',
                    'state' => 'SP',
                    'zip' => '01234-567',
                    'country' => 'Brasil',
                    'neighborhood' => 'Centro',
                ],
            ];

            foreach ($customers as $customerData) {
                $customer = Customer::firstOrCreate(
                    [
                        'email' => $customerData['email'],
                        'tenant_id' => $tenant->id,
                    ],
                    array_merge($customerData, ['tenant_id' => $tenant->id])
                );

                $this->command->info("Cliente criado: {$customer->email} para o tenant {$tenant->name}");
            }
        }

        $this->command->info('Clientes criados com sucesso!');
    }
}
