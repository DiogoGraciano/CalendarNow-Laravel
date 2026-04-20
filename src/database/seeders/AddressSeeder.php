<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Employee;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
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

        $addresses = [
            [
                'street' => 'Rua das Flores',
                'number' => '123',
                'complement' => 'Apto 45',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip' => '01234-567',
                'country' => 'Brasil',
                'type' => 'residential',
                'is_primary' => true,
            ],
            [
                'street' => 'Avenida Paulista',
                'number' => '1000',
                'complement' => 'Sala 10',
                'neighborhood' => 'Bela Vista',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip' => '01310-100',
                'country' => 'Brasil',
                'type' => 'commercial',
                'is_primary' => false,
            ],
            [
                'street' => 'Rua do Comércio',
                'number' => '456',
                'complement' => null,
                'neighborhood' => 'Vila Nova',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip' => '01234-890',
                'country' => 'Brasil',
                'type' => 'delivery',
                'is_primary' => false,
            ],
        ];

        foreach ($tenants as $tenant) {
            $users = User::where('tenant_id', $tenant->id)->get();
            $employees = Employee::where('tenant_id', $tenant->id)->get();

            foreach ($addresses as $addressData) {
                $address = Address::firstOrCreate(
                    [
                        'street' => $addressData['street'],
                        'number' => $addressData['number'],
                        'zip' => $addressData['zip'],
                        'tenant_id' => $tenant->id,
                    ],
                    array_merge($addressData, ['tenant_id' => $tenant->id])
                );

                // Vincular endereço primário ao usuário principal do tenant
                if ($addressData['is_primary'] && $users->isNotEmpty()) {
                    $mainUser = $users->first();
                    $mainUser->addresses()->syncWithoutDetaching([$address->id]);
                    $this->command->info("Endereço primário vinculado ao usuário {$mainUser->email}");
                }

                // Vincular endereço residencial ao primeiro funcionário do tenant
                if ($addressData['type'] === 'residential' && $employees->isNotEmpty()) {
                    $employee = $employees->first();
                    $employee->addresses()->syncWithoutDetaching([$address->id]);
                    $this->command->info("Endereço residencial vinculado ao funcionário {$employee->email}");
                }

                // Vincular endereço comercial ao usuário principal
                if ($addressData['type'] === 'commercial' && $users->isNotEmpty()) {
                    $mainUser = $users->first();
                    $mainUser->addresses()->syncWithoutDetaching([$address->id]);
                    $this->command->info("Endereço comercial vinculado ao usuário {$mainUser->email}");
                }
            }

            $this->command->info("Endereços criados e vinculados para o tenant {$tenant->name}");
        }

        $this->command->info('Endereços criados com sucesso!');
    }
}
