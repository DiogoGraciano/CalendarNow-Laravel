<?php

namespace Database\Seeders;

use App\Models\Dre;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class DreSeeder extends Seeder
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

        $dres = [
            [
                'code' => 'REC-001',
                'description' => 'Receitas de Serviços',
                'type' => 'receivable',
            ],
            [
                'code' => 'REC-002',
                'description' => 'Receitas de Produtos',
                'type' => 'receivable',
            ],
            [
                'code' => 'PAY-001',
                'description' => 'Despesas Operacionais',
                'type' => 'payable',
            ],
            [
                'code' => 'PAY-002',
                'description' => 'Despesas Administrativas',
                'type' => 'payable',
            ],
        ];

        foreach ($tenants as $tenant) {
            foreach ($dres as $dreData) {
                Dre::firstOrCreate(
                    [
                        'code' => $dreData['code'],
                        'tenant_id' => $tenant->id,
                    ],
                    array_merge($dreData, ['tenant_id' => $tenant->id])
                );
            }

            $this->command->info("DREs criados para o tenant {$tenant->name}");
        }

        $this->command->info('DREs criados com sucesso!');
    }
}
