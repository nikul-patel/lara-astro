<?php

namespace Database\Factories;

use App\Models\BirthChart;
use App\Models\YearWiseForecast;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<YearWiseForecast>
 */
class YearWiseForecastFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'birth_chart_id' => BirthChart::factory(),
            'year' => fake()->numberBetween(2020, 2040),
            'style' => 'simplified',
            'result' => [],
        ];
    }
}
