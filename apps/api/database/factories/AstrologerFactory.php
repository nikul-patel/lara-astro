<?php

namespace Database\Factories;

use App\Models\Astrologer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Astrologer>
 */
class AstrologerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name();

        return [
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 100000),
            'name' => $name,
            'bio' => fake()->paragraph(),
            'photo_path' => null,
            'specialties' => fake()->randomElements(
                ['Vedic Astrology', 'Numerology', 'Tarot', 'Vastu', 'Palmistry', 'Match-making'],
                2
            ),
            'languages' => fake()->randomElements(['English', 'Hindi', 'Gujarati'], 2),
            'availability_mode' => 'manual',
            'is_active' => true,
        ];
    }
}
