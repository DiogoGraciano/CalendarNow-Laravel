<?php

namespace Database\Factories;

use App\Models\Segment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Segment>
 */
class SegmentFactory extends Factory
{
    protected $model = Segment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $segments = [
            'Beleza e Estética',
            'Saúde e Bem-estar',
            'Educação',
            'Consultoria',
            'Tecnologia',
            'Serviços Gerais',
            'Alimentação',
            'Fitness',
            'Moda',
            'Automotivo',
        ];

        return [
            'name' => fake()->unique()->randomElement($segments),
        ];
    }
}
