<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_id' => Service::factory(),
            'astrologer_id' => fn (array $attributes) => Service::find($attributes['service_id'])->astrologer_id,
            'client_id' => Client::factory(),
            'slot' => fake()->dateTimeBetween('+1 day', '+2 weeks'),
            'status' => 'pending_payment',
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => [
            'status' => 'confirmed',
            'upi_reference' => strtoupper(fake()->bothify('UPI-########')),
        ]);
    }
}
