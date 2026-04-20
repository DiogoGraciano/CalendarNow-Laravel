<?php

namespace Database\Factories;

use App\Enums\SegmentEnum;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::random(10),
            'plan_id' => Plan::factory(),
            'segment' => fake()->randomElement(SegmentEnum::cases()),
            'name' => fake()->company(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'website' => fake()->optional()->url(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'zip' => fake()->postcode(),
            'country' => 'Brasil',
            'neighborhood' => fake()->streetName(),
            'logo' => fake()->optional()->imageUrl(),
            'favicon' => fake()->optional()->imageUrl(),
        ];
    }
}
