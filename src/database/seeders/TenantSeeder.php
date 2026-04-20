<?php

namespace Database\Seeders;

use App\Enums\SegmentEnum;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plan = Plan::where('is_default', true)->first() ?? Plan::first();

        if (! $plan) {
            $this->command->warn('Plano não encontrado. Execute PlanSeeder primeiro.');

            return;
        }

        $tenants = [
            [
                'id' => 'salon-beleza',
                'plan_id' => $plan->id,
                'segment' => SegmentEnum::BeautyAesthetics,
                'name' => 'Salão de Beleza Elegante',
                'email' => 'contato@salaoelegante.com.br',
                'phone' => '(11) 3456-7890',
                'website' => 'https://salaoelegante.com.br',
                'address' => 'Av. Paulista, 1000',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip' => '01310-100',
                'country' => 'Brasil',
                'neighborhood' => 'Bela Vista',
            ],
            [
                'id' => 'clinica-saude',
                'plan_id' => Plan::where('name', 'Plano Profissional')->first()?->id ?? $plan->id,
                'segment' => SegmentEnum::HealthWellness,
                'name' => 'Clínica Saúde Total',
                'email' => 'contato@clinicasaudetotal.com.br',
                'phone' => '(11) 3456-7891',
                'website' => 'https://clinicasaudetotal.com.br',
                'address' => 'Rua das Flores, 500',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip' => '01234-567',
                'country' => 'Brasil',
                'neighborhood' => 'Centro',
            ],
            [
                'id' => 'academia-fitness',
                'plan_id' => $plan->id,
                'segment' => SegmentEnum::Fitness,
                'name' => 'Academia Fitness Pro',
                'email' => 'contato@academiafitnesspro.com.br',
                'phone' => '(11) 3456-7892',
                'website' => 'https://academiafitnesspro.com.br',
                'address' => 'Rua dos Atletas, 200',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip' => '01234-890',
                'country' => 'Brasil',
                'neighborhood' => 'Vila Nova',
            ],
            [
                'id' => 'escola-educacao',
                'plan_id' => Plan::where('name', 'Plano Enterprise')->first()?->id ?? $plan->id,
                'segment' => SegmentEnum::Education,
                'name' => 'Escola Educação Plus',
                'email' => 'contato@escolaeducacaoplus.com.br',
                'phone' => '(11) 3456-7893',
                'website' => 'https://escolaeducacaoplus.com.br',
                'address' => 'Av. Educação, 300',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip' => '01234-123',
                'country' => 'Brasil',
                'neighborhood' => 'Jardim Educação',
            ],
            [
                'id' => 'consultoria-tech',
                'plan_id' => Plan::where('name', 'Plano Profissional')->first()?->id ?? $plan->id,
                'segment' => SegmentEnum::Technology,
                'name' => 'Consultoria Tech Solutions',
                'email' => 'contato@techsolutions.com.br',
                'phone' => '(11) 3456-7894',
                'website' => 'https://techsolutions.com.br',
                'address' => 'Rua da Tecnologia, 400',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip' => '01234-456',
                'country' => 'Brasil',
                'neighborhood' => 'Vila Tech',
            ],
        ];

        foreach ($tenants as $tenantData) {
            $tenant = Tenant::firstOrCreate(
                ['id' => $tenantData['id']],
                $tenantData
            );

            // Criar domínio para o tenant se não existir
            if (! $tenant->domains()->exists()) {
                $domain = $tenantData['id'].'.localhost';
                $tenant->domains()->firstOrCreate([
                    'domain' => $domain,
                ]);

                $this->command->info("Tenant criado: {$tenant->name} (ID: {$tenant->id}, Domínio: {$domain})");
            } else {
                $this->command->info("Tenant já existe: {$tenant->name} (ID: {$tenant->id})");
            }
        }

        $this->command->info('Tenants criados com sucesso!');
    }
}
