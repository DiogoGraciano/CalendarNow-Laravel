<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'cpf_cnpj' => fake()->unique()->numerify('###########'),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'photo' => fake()->optional()->imageUrl(),
            'status' => fake()->randomElement(['working', 'vacation', 'sick_leave', 'fired', 'resigned']),
            'gender' => fake()->optional()->randomElement(['male', 'female']),
            'birth_date' => fake()->dateTimeBetween('-60 years', '-18 years'),
            'admission_date' => now(),
            'work_start_date' => fake()->optional()->dateTimeBetween('-1 year', 'now'),
            'work_end_time' => fake()->optional()->date(),
            'launch_start_time' => fake()->optional()->date(),
            'launch_end_time' => fake()->optional()->date(),
            'work_days' => fake()->randomElements(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], fake()->numberBetween(1, 7)),
            'work_end_date' => fake()->optional()->date(),
            'fired_date' => fake()->optional()->date(),
            'salary' => fake()->randomFloat(2, 1000, 10000),
            'pay_day' => fake()->numberBetween(1, 28),
            'notes' => fake()->optional()->text(),
        ];
    }

    /**
     * Indicate that the employee is working.
     */
    public function working(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'working',
        ]);
    }

    /**
     * Indicate that the employee is on vacation.
     */
    public function onVacation(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'vacation',
        ]);
    }
}
