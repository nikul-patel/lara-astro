<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Populates a realistic FICTIONAL astrology-business demo dataset for the
 * "Jyotish Path" demo tenant (PRD §1): site settings, astrologers, services
 * with INR/USD pricing, recorded + live courses with curriculum, blog posts,
 * testimonials and legal/CMS pages — localized to en/hi/gu wherever the
 * schema stores translatable content (Page, Post, Testimonial).
 *
 * Every child seeder is idempotent (updateOrCreate), so this is safe to
 * re-run via `php artisan db:seed --class=DemoContentSeeder`.
 */
class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            AstrologerSeeder::class,
            CourseSeeder::class,
            PostSeeder::class,
            PageSeeder::class,
            TestimonialSeeder::class,
        ]);
    }
}
