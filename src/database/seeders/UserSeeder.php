<?php

namespace Database\Seeders;

use App\Models\Calendar;
use App\Models\Tenant;
use App\Models\TenantEmail;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = Tenant::first();

        if (! $tenant) {
            $this->command->warn('Tenant não encontrado. Execute TenantSeeder primeiro.');

            return;
        }

        // Criar usuários para cada tenant
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            // Criar usuário principal com o mesmo email do tenant
            // O email do tenant é usado para encontrar o tenant pelo usuário
            $mainUser = User::firstOrCreate(
                [
                    'email' => $tenant->email,
                    'tenant_id' => $tenant->id,
                ],
                [
                    'name' => $tenant->name,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            $this->command->info("Usuário principal criado: {$mainUser->email} para o tenant {$tenant->name}");

            // Vincular usuário principal aos calendários do tenant
            $calendars = Calendar::where('tenant_id', $tenant->id)->get();
            if ($calendars->isNotEmpty()) {
                $mainUser->calendars()->syncWithoutDetaching($calendars->pluck('id')->toArray());
                $this->command->info("Usuário principal vinculado a {$calendars->count()} calendário(s)");
            }

            // Criar emails adicionais para o tenant
            $emails = [
                "suporte@{$tenant->id}.com",
                "vendas@{$tenant->id}.com",
                "contato@{$tenant->id}.com",
            ];

            foreach ($emails as $email) {
                $tenantEmail = TenantEmail::firstOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'email' => $email,
                    ]
                );

                $this->command->info("Email criado: {$email} para o tenant {$tenant->name}");
            }

            // Criar alguns usuários adicionais
            $users = [
                [
                    'name' => "Usuário Teste 1 - {$tenant->name}",
                    'email' => "usuario1@{$tenant->id}.com",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
                [
                    'name' => "Usuário Teste 2 - {$tenant->name}",
                    'email' => "usuario2@{$tenant->id}.com",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
            ];

            foreach ($users as $userData) {
                $user = User::firstOrCreate(
                    [
                        'email' => $userData['email'],
                        'tenant_id' => $tenant->id,
                    ],
                    array_merge($userData, ['tenant_id' => $tenant->id])
                );

                // Vincular usuário ao primeiro calendário do tenant
                $firstCalendar = Calendar::where('tenant_id', $tenant->id)->first();
                if ($firstCalendar) {
                    $user->calendars()->syncWithoutDetaching([$firstCalendar->id]);
                }

                $this->command->info("Usuário criado: {$user->email} para o tenant {$tenant->name}");
            }
        }

        $this->command->info('Usuários e emails criados com sucesso!');
    }
}
