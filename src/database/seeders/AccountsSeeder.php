<?php

namespace Database\Seeders;

use App\Models\Accounts;
use App\Models\Customer;
use App\Models\Dre;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AccountsSeeder extends Seeder
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
            $dre = Dre::where('tenant_id', $tenant->id)
                ->where('type', 'receivable')
                ->first();
            $customer = Customer::where('tenant_id', $tenant->id)->first();

            if (! $dre || ! $customer) {
                $this->command->warn("DRE ou Cliente não encontrado para o tenant {$tenant->name}. Execute os seeders de Dre e Customer primeiro.");

                continue;
            }

            $accounts = [
                [
                    'dre_id' => $dre->id,
                    'customer_id' => $customer->id,
                    'code' => 'ACC-'.strtoupper(Str::random(8)),
                    'name' => 'Conta a Receber #1',
                    'type' => 'receivable',
                    'type_interest' => 'fixed',
                    'interest_rate' => 0.00,
                    'total' => 500.00,
                    'paid' => 0.00,
                    'due_date' => now()->addDays(30),
                    'status' => 'pending',
                ],
                [
                    'dre_id' => $dre->id,
                    'customer_id' => $customer->id,
                    'code' => 'ACC-'.strtoupper(Str::random(8)),
                    'name' => 'Conta a Receber #2',
                    'type' => 'receivable',
                    'type_interest' => 'variable',
                    'interest_rate' => 2.50,
                    'total' => 750.00,
                    'paid' => 250.00,
                    'due_date' => now()->addDays(15),
                    'status' => 'pending',
                ],
            ];

            foreach ($accounts as $accountData) {
                Accounts::firstOrCreate(
                    [
                        'code' => $accountData['code'],
                        'tenant_id' => $tenant->id,
                    ],
                    array_merge($accountData, ['tenant_id' => $tenant->id])
                );
            }

            $this->command->info("Contas criadas para o tenant {$tenant->name}");
        }

        $this->command->info('Contas criadas com sucesso!');
    }
}
