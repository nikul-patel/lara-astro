<?php

namespace Database\Factories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quote = fake()->paragraph();

        return [
            'name' => fake()->name(),
            'quote' => ['en' => $quote, 'hi' => $quote, 'gu' => $quote],
            'rating' => fake()->numberBetween(4, 5),
            'is_active' => true,
        ];
    }
}
