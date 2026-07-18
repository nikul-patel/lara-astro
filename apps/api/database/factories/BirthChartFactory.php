<?php

namespace Database\Factories;

use App\Models\BirthChart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BirthChart>
 */
class BirthChartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => null,
            'name' => fake()->name(),
            'dob' => fake()->date(),
            'time' => fake()->time(),
            'place' => fake()->city().', India',
            'system' => 'vedic',
            'chart_style' => 'north_indian',
            'result' => [
                'timezone' => 'Asia/Kolkata',
                'system' => 'vedic',
                'chart_style' => 'north_indian',
                'recommendation' => ['system' => 'vedic', 'chart_style' => 'north_indian'],
                'planetary_positions' => [],
                'houses' => [],
            ],
        ];
    }
}
