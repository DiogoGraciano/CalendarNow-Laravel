<?php

namespace Database\Factories;

use App\Models\Accounts;
use App\Models\Calendar;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Scheduling;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Scheduling>
 */
class SchedulingFactory extends Factory
{
    protected $model = Scheduling::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = fake()->dateTimeBetween('now', '+1 month');
        $duration = fake()->numberBetween(30, 180); // minutos
        $endTime = (clone $startTime)->modify("+{$duration} minutes");

        return [
            'code' => 'SCH-'.strtoupper(Str::random(8)),
            'employee_id' => Employee::factory(),
            'calendar_id' => Calendar::factory(),
            'account_id' => Accounts::factory(),
            'customer_id' => Customer::factory(),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => fake()->randomElement(['pending', 'confirmed', 'cancelled']),
            'color' => fake()->hexColor(),
            'duration' => $duration,
            'notes' => fake()->optional()->text(),
        ];
    }

    /**
     * Indicate that the scheduling is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
        ]);
    }

    /**
     * Indicate that the scheduling is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the scheduling is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}
