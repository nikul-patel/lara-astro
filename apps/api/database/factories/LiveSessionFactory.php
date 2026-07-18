<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\LiveSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LiveSession>
 */
class LiveSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('+1 day', '+1 month');

        return [
            'course_id' => Course::factory(),
            'starts_at' => $startsAt,
            'ends_at' => (clone $startsAt)->modify('+1 hour'),
            'meeting_url' => fake()->url(),
        ];
    }
}
