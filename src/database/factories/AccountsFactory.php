<?php

namespace Database\Factories;

use App\Models\Accounts;
use App\Models\Customer;
use App\Models\Dre;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Accounts>
 */
class AccountsFactory extends Factory
{
    protected $model = Accounts::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['receivable', 'payable']);
        $status = fake()->randomElement(['pending', 'paid', 'overdue', 'cancelled']);

        return [
            'dre_id' => Dre::factory(),
            'customer_id' => Customer::factory(),
            'code' => 'ACC-'.strtoupper(uniqid()),
            'name' => fake()->words(3, true).' Account',
            'type' => $type,
            'type_interest' => fake()->randomElement(['fixed', 'variable']),
            'interest_rate' => fake()->randomFloat(6, 0, 10),
            'total' => fake()->randomFloat(2, 100, 10000),
            'paid' => fake()->randomFloat(2, 0, 5000),
            'due_date' => fake()->dateTimeBetween('now', '+1 year'),
            'payment_date' => $status === 'paid' ? fake()->dateTimeBetween('-1 year', 'now') : null,
            'notes' => fake()->optional()->text(),
            'status' => $status,
        ];
    }

    /**
     * Indicate that the account is receivable.
     */
    public function receivable(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'receivable',
        ]);
    }

    /**
     * Indicate that the account is payable.
     */
    public function payable(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'payable',
        ]);
    }

    /**
     * Indicate that the account is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid' => $attributes['total'],
            'payment_date' => fake()->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Indicate that the account is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'paid' => 0,
            'payment_date' => null,
        ]);
    }
}
