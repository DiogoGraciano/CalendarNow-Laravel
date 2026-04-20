<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\TenantEmail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TenantEmail>
 */
class TenantEmailFactory extends Factory
{
    protected $model = TenantEmail::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'email' => fake()->unique()->safeEmail(),
        ];
    }
}
