<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Plano Gratuito',
                'description' => 'Plano gratuito para testes',
                'price' => 0.00,
                'is_active' => true,
                'is_default' => true,
                'max_users' => '1',
                'max_calendars' => '1',
            ],
            [
                'name' => 'Plano Básico',
                'description' => 'Plano ideal para pequenas empresas',
                'price' => 99.00,
                'is_active' => true,
                'is_default' => true,
                'max_users' => '5',
                'max_calendars' => '2',
            ],
            [
                'name' => 'Plano Profissional',
                'description' => 'Plano para empresas em crescimento',
                'price' => 199.00,
                'is_active' => true,
                'is_default' => false,
                'max_users' => '15',
                'max_calendars' => '5',
            ],
            [
                'name' => 'Plano Enterprise',
                'description' => 'Plano completo para grandes empresas',
                'price' => 499.00,
                'is_active' => true,
                'is_default' => false,
                'max_users' => 'unlimited',
                'max_calendars' => 'unlimited',
            ],
        ];

        foreach ($plans as $plan) {
            Plan::firstOrCreate(
                ['name' => $plan['name']],
                $plan
            );
        }
    }
}
