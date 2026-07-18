<?php

namespace Database\Factories;

use App\Models\Astrologer;
use App\Models\AvailabilitySlot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AvailabilitySlot>
 */
class AvailabilitySlotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'astrologer_id' => Astrologer::factory(),
            'weekday' => fake()->numberBetween(1, 5),
            'start_time' => '10:00:00',
            'end_time' => '18:00:00',
            'is_active' => true,
        ];
    }
}
