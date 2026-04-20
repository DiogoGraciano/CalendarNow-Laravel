<?php

namespace Database\Factories;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmployeeDayOff>
 */
class EmployeeDayOffFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('now', '+3 months');
        $endDate = fake()->dateTimeBetween($startDate, Carbon::parse($startDate)->addDays(14));

        return [
            'employee_id' => Employee::factory(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'type' => fake()->randomElement(['day_off', 'vacation', 'medical_leave', 'personal', 'other']),
            'reason' => fake()->optional()->sentence(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
