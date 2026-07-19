<?php

namespace Database\Seeders;

use App\Models\Astrologer;
use App\Models\Service;
use Illuminate\Database\Seeder;

/**
 * Seeds the fictional "Jyotish Path" astrologers and their consultation
 * services (PRD §5.1). Astrologer/Service columns are plain (non-translatable)
 * per their migrations, so copy is English; each service carries explicit
 * INR + USD pricing per PRD §11. Idempotent via updateOrCreate on slug.
 */
class AstrologerSeeder extends Seeder
{
    public function run(): void
    {
        $astrologers = [
            [
                'slug' => 'acharya-aarav',
                'name' => 'Acharya Aarav Sharma',
                'bio' => 'A Vedic astrology practitioner known for calm, practical readings rooted in classical Jyotish. Aarav focuses on career direction, life timing and dasha analysis, and explains the chart in plain language you can act on.',
                'specialties' => ['Career', 'Life direction', 'Dashas'],
                'languages' => ['Hindi', 'English'],
                'services' => [
                    [
                        'slug' => 'birth-chart-consultation',
                        'name' => 'Birth Chart Consultation',
                        'description' => 'A focused Vedic reading of your chart, current cycles and the questions that matter now.',
                        'duration_minutes' => 45,
                        'price_inr' => 2100.00,
                        'price_usd' => 35.00,
                    ],
                    [
                        'slug' => 'career-guidance',
                        'name' => 'Career & Business Guidance',
                        'description' => 'Practical timing and direction for work, leadership, transitions and business decisions.',
                        'duration_minutes' => 60,
                        'price_inr' => 3100.00,
                        'price_usd' => 49.00,
                    ],
                ],
            ],
            [
                'slug' => 'jyotishi-meera',
                'name' => 'Jyotishi Meera Joshi',
                'bio' => 'A compassionate consultant focused on relationships, family patterns and personal clarity. Meera offers thoughtful compatibility reviews and wellbeing guidance in a warm, respectful style.',
                'specialties' => ['Relationships', 'Compatibility', 'Wellbeing'],
                'languages' => ['Gujarati', 'Hindi', 'English'],
                'services' => [
                    [
                        'slug' => 'relationship-compatibility',
                        'name' => 'Relationship Compatibility',
                        'description' => 'A thoughtful compatibility review that highlights strengths, patterns and shared growth.',
                        'duration_minutes' => 60,
                        'price_inr' => 3500.00,
                        'price_usd' => 55.00,
                    ],
                ],
            ],
        ];

        foreach ($astrologers as $data) {
            $services = $data['services'];
            unset($data['services']);

            $astrologer = Astrologer::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'bio' => $data['bio'],
                    'specialties' => $data['specialties'],
                    'languages' => $data['languages'],
                    'availability_mode' => 'manual',
                    'is_active' => true,
                ],
            );

            foreach ($services as $service) {
                Service::updateOrCreate(
                    ['slug' => $service['slug']],
                    [
                        'astrologer_id' => $astrologer->id,
                        'name' => $service['name'],
                        'description' => $service['description'],
                        'duration_minutes' => $service['duration_minutes'],
                        'price_inr' => $service['price_inr'],
                        'price_usd' => $service['price_usd'],
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}
