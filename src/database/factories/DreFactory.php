<?php

namespace Database\Factories;

use App\Models\Dre;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Dre>
 */
class DreFactory extends Factory
{
    protected $model = Dre::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['receivable', 'payable']);
        $prefix = $type === 'receivable' ? 'REC' : 'PAY';

        return [
            'code' => $prefix.'-'.str_pad((string) fake()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'description' => fake()->sentence(),
            'type' => $type,
        ];
    }

    /**
     * Indicate that the DRE is receivable.
     */
    public function receivable(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'receivable',
            'code' => 'REC-'.str_pad((string) fake()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
        ]);
    }

    /**
     * Indicate that the DRE is payable.
     */
    public function payable(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'payable',
            'code' => 'PAY-'.str_pad((string) fake()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
        ]);
    }
}
