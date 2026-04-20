<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Holiday>
 */
class HolidayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Natal', 'Ano Novo', 'Carnaval', 'Tiradentes', 'Dia do Trabalho', 'Independência', 'Finados', 'Proclamação da República']),
            'date' => fake()->dateTimeBetween('now', '+1 year'),
            'recurring' => fake()->boolean(70),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
