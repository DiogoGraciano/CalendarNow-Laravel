<?php

namespace Database\Seeders;

use App\Models\Dre;
use App\Models\Tenant;
use App\Models\TenantSetting;
use Illuminate\Database\Seeder;

class TenantSettingsSeeder extends Seeder
{
    /**
     * Define as configurações padrão de cada tenant.
     * Pré-seleciona uma DRE do tipo "receivable" (conta a receber) para agendamentos.
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
                ->orderBy('code')
                ->first();

            if (! $dre) {
                $this->command->warn("Nenhuma DRE de receita encontrada para o tenant {$tenant->name}. Execute DreSeeder primeiro.");

                continue;
            }

            TenantSetting::updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'key' => TenantSetting::KEY_SCHEDULING_DEFAULT_DRE_ID,
                ],
                ['value' => (string) $dre->id]
            );

            $this->command->info("Configuração definida para {$tenant->name}: DRE padrão = {$dre->code} - {$dre->description}");
        }

        $this->command->info('Configurações dos tenants criadas com sucesso!');
    }
}
