<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence();

        return [
            'slug' => Str::slug($title),
            'title' => ['en' => $title, 'hi' => $title, 'gu' => $title],
            'excerpt' => [
                'en' => fake()->sentence(),
                'hi' => fake()->sentence(),
                'gu' => fake()->sentence(),
            ],
            'content' => [
                'en' => fake()->paragraphs(5, true),
                'hi' => fake()->paragraphs(5, true),
                'gu' => fake()->paragraphs(5, true),
            ],
            'published_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
