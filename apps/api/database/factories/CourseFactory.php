<?php

namespace Database\Factories;

use App\Models\Astrologer;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->randomElement([
            'Foundations of Vedic Astrology', 'Advanced Chart Reading', 'Numerology Essentials',
        ]);
        $priceInr = fake()->randomElement([2999, 4999, 7999]);

        return [
            'astrologer_id' => Astrologer::factory(),
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 100000),
            'title' => $title,
            'description' => fake()->paragraph(),
            'type' => fake()->randomElement(['recorded', 'live']),
            'price_inr' => $priceInr,
            'price_usd' => round($priceInr / 83, 2),
            'is_active' => true,
        ];
    }
}
