<?php

namespace Database\Factories;

use App\Models\Scheduling;
use App\Models\SchedulingItem;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SchedulingItem>
 */
class SchedulingItemFactory extends Factory
{
    protected $model = SchedulingItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 5);
        $unitAmount = fake()->randomFloat(2, 50, 500);
        $discount = fake()->randomFloat(2, 0, 100);
        $totalAmount = ($unitAmount * $quantity) - $discount;

        $durations = ['00:30:00', '01:00:00', '01:30:00', '02:00:00'];

        return [
            'scheduling_id' => Scheduling::factory(),
            'service_id' => Service::factory(),
            'total_amount' => $totalAmount,
            'unit_amount' => $unitAmount,
            'discount' => $discount,
            'quantity' => $quantity,
            'duration' => fake()->randomElement($durations),
        ];
    }
}
