<?php

namespace Database\Factories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'street' => fake()->streetName(),
            'number' => fake()->buildingNumber(),
            'complement' => fake()->optional()->secondaryAddress(),
            'neighborhood' => fake()->streetName(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'zip' => fake()->postcode(),
            'country' => 'Brasil',
            'type' => fake()->randomElement(['residential', 'commercial', 'delivery', 'billing', 'shipping', 'other']),
            'is_primary' => false,
        ];
    }

    /**
     * Indicate that the address is primary.
     */
    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
        ]);
    }

    /**
     * Indicate that the address is residential.
     */
    public function residential(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'residential',
        ]);
    }

    /**
     * Indicate that the address is commercial.
     */
    public function commercial(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'commercial',
        ]);
    }
}
