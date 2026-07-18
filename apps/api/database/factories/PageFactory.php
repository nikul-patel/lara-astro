<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'slug' => Str::slug($title),
            'title' => ['en' => $title, 'hi' => $title, 'gu' => $title],
            'content' => [
                'en' => fake()->paragraphs(3, true),
                'hi' => fake()->paragraphs(3, true),
                'gu' => fake()->paragraphs(3, true),
            ],
        ];
    }
}
