<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class PublicPageSettingsSeeder extends Seeder
{
    /**
     * Seed das configurações da página pública de cada tenant.
     */
    public function run(): void
    {
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->command->warn('Nenhum tenant encontrado. Execute TenantSeeder primeiro.');

            return;
        }

        $settings = [
            'salon-beleza' => [
                'primary_color' => '#e91e63',
                'secondary_color' => '#f48fb1',
                'hero_title' => 'Salão de Beleza Elegante',
                'hero_subtitle' => 'Realce sua beleza com nossos profissionais especializados. Agende seu horário de forma rápida e prática.',
                'show_employees_section' => true,
            ],
            'clinica-saude' => [
                'primary_color' => '#00897b',
                'secondary_color' => '#4db6ac',
                'hero_title' => 'Clínica Saúde Total',
                'hero_subtitle' => 'Cuidamos da sua saúde com excelência. Agende sua consulta online com nossos especialistas.',
                'show_employees_section' => true,
            ],
            'academia-fitness' => [
                'primary_color' => '#ff6f00',
                'secondary_color' => '#ffb74d',
                'hero_title' => 'Academia Fitness Pro',
                'hero_subtitle' => 'Transforme seu corpo e sua vida. Agende uma aula experimental e comece hoje mesmo!',
                'show_employees_section' => true,
            ],
            'escola-educacao' => [
                'primary_color' => '#1565c0',
                'secondary_color' => '#64b5f6',
                'hero_title' => 'Escola Educação Plus',
                'hero_subtitle' => 'Educação de qualidade para o futuro dos seus filhos. Agende uma visita à nossa escola.',
                'show_employees_section' => false,
            ],
            'consultoria-tech' => [
                'primary_color' => '#6a1b9a',
                'secondary_color' => '#ce93d8',
                'hero_title' => 'Tech Solutions',
                'hero_subtitle' => 'Soluções tecnológicas sob medida para o seu negócio. Agende uma consultoria gratuita.',
                'show_employees_section' => true,
            ],
        ];

        foreach ($tenants as $tenant) {
            $data = $settings[$tenant->id] ?? [
                'primary_color' => '#3b82f6',
                'secondary_color' => '#10b981',
                'hero_title' => $tenant->name,
                'hero_subtitle' => 'Agende seu horário de forma rápida e prática.',
                'show_employees_section' => true,
            ];

            $tenant->update($data);

            $this->command->info("Página pública configurada para: {$tenant->name}");
        }

        $this->command->info('Configurações da página pública criadas com sucesso!');
    }
}
