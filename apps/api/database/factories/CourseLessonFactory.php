<?php

namespace Database\Factories;

use App\Models\CourseLesson;
use App\Models\CourseModule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseLesson>
 */
class CourseLessonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_module_id' => CourseModule::factory(),
            'title' => 'Lesson: '.fake()->words(4, true),
            'duration_minutes' => fake()->numberBetween(5, 30),
            'video_url' => fake()->url(),
            'order' => fake()->numberBetween(0, 10),
        ];
    }
}
