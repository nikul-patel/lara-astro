<?php

namespace Database\Factories;

use App\Models\Astrologer;
use App\Models\GoogleCalendarConnection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GoogleCalendarConnection>
 */
class GoogleCalendarConnectionFactory extends Factory
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
            'google_account_email' => fake()->safeEmail(),
            'access_token' => fake()->sha256(),
            'refresh_token' => fake()->sha256(),
            'token_expires_at' => fake()->dateTimeBetween('+1 hour', '+1 day'),
            'calendar_id' => fake()->safeEmail(),
        ];
    }
}
