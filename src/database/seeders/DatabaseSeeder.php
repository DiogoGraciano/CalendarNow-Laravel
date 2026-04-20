<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Seeders básicos (sem dependências)
            PlanSeeder::class,
            SegmentSeeder::class,

            // Seeders que dependem dos básicos
            TenantSeeder::class,

            // Seeders que dependem de Tenant
            DreSeeder::class,
            TenantSettingsSeeder::class,
            PublicPageSettingsSeeder::class,
            ServiceSeeder::class,
            UserSeeder::class,
            CalendarSeeder::class,

            // Seeders que dependem de User
            EmployeeSeeder::class,
            CustomerSeeder::class,

            // Seeders que dependem de Employee e Service
            EmployeeServiceSeeder::class,

            // Seeders que dependem de Tenant (feriados)
            HolidaySeeder::class,

            // Seeders que dependem de Employee (folgas)
            EmployeeDayOffSeeder::class,

            // Seeders que dependem de Employee e Calendar
            EmployeeCalendarSeeder::class,

            // Seeders que dependem de User e Employee
            AddressSeeder::class,

            // Seeders que dependem de Dre e Customer
            AccountsSeeder::class,

            // Seeders que dependem de Employee, Calendar, Accounts e Customer
            SchedulingSeeder::class,

            // Seeders que dependem de Scheduling e Service
            SchedulingItemSeeder::class,
        ]);
    }
}
