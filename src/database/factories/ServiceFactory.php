<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $durations = ['00:30:00', '01:00:00', '01:30:00', '02:00:00', '02:30:00'];

        return [
            'name' => fake()->words(3, true).' Service',
            'description' => fake()->optional()->paragraph(),
            'price' => fake()->randomFloat(2, 50, 500),
            'duration' => fake()->randomElement($durations),
            'image' => fake()->optional()->imageUrl(),
            'order' => fake()->numberBetween(0, 100),
        ];
    }
}
