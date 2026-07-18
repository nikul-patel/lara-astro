<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enrollment>
 */
class EnrollmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'client_id' => Client::factory(),
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
