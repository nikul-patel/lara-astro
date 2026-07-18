<?php

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_name' => fake()->company(),
            'supported_languages' => ['en', 'hi', 'gu'],
            'default_currency' => 'INR',
            'currencies' => ['INR', 'USD'],
            'upi_id' => fake()->userName().'@upi',
            'contact' => [
                'email' => fake()->companyEmail(),
                'phone' => fake()->phoneNumber(),
                'address' => fake()->address(),
            ],
            'social_links' => [],
            'legal_links' => [],
        ];
    }
}
