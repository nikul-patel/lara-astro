<?php

namespace Database\Seeders;

use App\Models\Astrologer;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\LiveSession;
use Illuminate\Database\Seeder;

/**
 * Seeds the fictional "Jyotish Path" courses (PRD §5.1): one recorded course
 * with a module/lesson curriculum and one live course with scheduled live
 * sessions. Course/module/lesson columns are plain (non-translatable) per
 * their migrations, so copy is English; INR + USD pricing is explicit per
 * PRD §11. Idempotent via updateOrCreate keyed on stable slug/order/time.
 */
class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $aarav = Astrologer::where('slug', 'acharya-aarav')->first();
        $meera = Astrologer::where('slug', 'jyotishi-meera')->first();

        $courses = [
            [
                'slug' => 'vedic-astrology-foundations',
                'title' => 'Vedic Astrology Foundations',
                'description' => 'Learn signs, planets, houses and the practical steps for reading a birth chart with confidence.',
                'type' => 'recorded',
                'price_inr' => 4999.00,
                'price_usd' => 79.00,
                'astrologer_id' => $aarav?->id,
                'modules' => [
                    [
                        'title' => 'The astrological alphabet',
                        'lessons' => [
                            ['title' => 'Planets and their roles', 'duration_minutes' => 28, 'video_url' => 'https://videos.jyotishpath.example/foundations/planets'],
                            ['title' => 'Signs, elements and qualities', 'duration_minutes' => 34, 'video_url' => 'https://videos.jyotishpath.example/foundations/signs'],
                        ],
                    ],
                    [
                        'title' => 'Reading the chart',
                        'lessons' => [
                            ['title' => 'The twelve houses', 'duration_minutes' => 42, 'video_url' => 'https://videos.jyotishpath.example/foundations/houses'],
                            ['title' => 'A guided chart interpretation', 'duration_minutes' => 51, 'video_url' => 'https://videos.jyotishpath.example/foundations/interpretation'],
                        ],
                    ],
                ],
                'live_sessions' => [],
            ],
            [
                'slug' => 'live-chart-reading-intensive',
                'title' => 'Live Chart Reading Intensive',
                'description' => 'Practice synthesis and interpretation in a small live cohort with instructor feedback.',
                'type' => 'live',
                'price_inr' => 8999.00,
                'price_usd' => 139.00,
                'astrologer_id' => $meera?->id,
                'modules' => [
                    [
                        'title' => 'Cohort preparation',
                        'lessons' => [
                            ['title' => 'Chart synthesis workbook', 'duration_minutes' => 25, 'video_url' => null],
                        ],
                    ],
                ],
                'live_sessions' => [
                    ['starts_at' => '2026-08-08 10:30:00', 'ends_at' => '2026-08-08 12:00:00', 'meeting_url' => 'https://meet.jyotishpath.example/intensive-session-1'],
                    ['starts_at' => '2026-08-15 10:30:00', 'ends_at' => '2026-08-15 12:00:00', 'meeting_url' => 'https://meet.jyotishpath.example/intensive-session-2'],
                ],
            ],
        ];

        foreach ($courses as $data) {
            $modules = $data['modules'];
            $liveSessions = $data['live_sessions'];
            unset($data['modules'], $data['live_sessions']);

            $course = Course::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'astrologer_id' => $data['astrologer_id'],
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'type' => $data['type'],
                    'price_inr' => $data['price_inr'],
                    'price_usd' => $data['price_usd'],
                    'is_active' => true,
                ],
            );

            foreach ($modules as $moduleIndex => $moduleData) {
                $module = CourseModule::updateOrCreate(
                    ['course_id' => $course->id, 'order' => $moduleIndex],
                    ['title' => $moduleData['title']],
                );

                foreach ($moduleData['lessons'] as $lessonIndex => $lessonData) {
                    CourseLesson::updateOrCreate(
                        ['course_module_id' => $module->id, 'order' => $lessonIndex],
                        [
                            'title' => $lessonData['title'],
                            'duration_minutes' => $lessonData['duration_minutes'],
                            'video_url' => $lessonData['video_url'],
                        ],
                    );
                }
            }

            foreach ($liveSessions as $session) {
                LiveSession::updateOrCreate(
                    ['course_id' => $course->id, 'starts_at' => $session['starts_at']],
                    ['ends_at' => $session['ends_at'], 'meeting_url' => $session['meeting_url']],
                );
            }
        }
    }
}
