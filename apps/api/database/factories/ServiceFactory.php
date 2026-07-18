<?php

namespace Database\Factories;

use App\Models\Astrologer;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement([
            'Birth Chart Reading', 'Career Consultation', 'Match-making', 'Career Consultation Follow-up',
        ]);
        $priceInr = fake()->randomElement([999, 1499, 2499, 4999]);

        return [
            'astrologer_id' => Astrologer::factory(),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 100000),
            'name' => $name,
            'description' => fake()->sentence(),
            'duration_minutes' => fake()->randomElement([30, 45, 60]),
            'price_inr' => $priceInr,
            'price_usd' => round($priceInr / 83, 2),
            'is_active' => true,
        ];
    }
}
