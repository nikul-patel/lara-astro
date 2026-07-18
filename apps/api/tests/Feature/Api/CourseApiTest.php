<?php

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\LiveSession;

test('it lists only active courses and can filter by type', function () {
    Course::factory()->create(['type' => 'recorded', 'is_active' => true]);
    Course::factory()->create(['type' => 'live', 'is_active' => true]);
    Course::factory()->create(['type' => 'recorded', 'is_active' => false]);

    $response = $this->getJson('/api/v1/courses?type=recorded');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.type'))->toBe('recorded');
});

test('an inactive course 404s on the public detail endpoint', function () {
    $course = Course::factory()->create(['slug' => 'hidden-course', 'is_active' => false]);

    $this->getJson("/api/v1/courses/{$course->slug}")->assertNotFound();
});

test('course detail includes curriculum outline without gated video/meeting links', function () {
    $course = Course::factory()->create(['slug' => 'foundations', 'is_active' => true, 'type' => 'live']);
    $module = CourseModule::factory()->for($course)->create(['title' => 'Module One']);
    CourseLesson::factory()->for($module, 'module')->create([
        'title' => 'Lesson One',
        'video_url' => 'https://example.com/secret-lesson.mp4',
    ]);
    LiveSession::factory()->for($course)->create(['meeting_url' => 'https://meet.example.com/secret']);

    $response = $this->getJson('/api/v1/courses/foundations');

    $response->assertOk();
    expect($response->json('modules.0.title'))->toBe('Module One')
        ->and($response->json('modules.0.lessons.0.title'))->toBe('Lesson One')
        ->and($response->json('modules.0.lessons.0'))->not->toHaveKey('video_url')
        ->and($response->json('live_sessions.0'))->not->toHaveKey('meeting_url')
        ->and($response->json('live_sessions.0'))->toHaveKey('starts_at');

    $body = $response->getContent();
    expect($body)->not->toContain('secret-lesson.mp4')
        ->and($body)->not->toContain('meet.example.com/secret');
});

test('course detail returns a null instructor when no astrologer is assigned', function () {
    $course = Course::factory()->create(['slug' => 'no-instructor', 'is_active' => true, 'astrologer_id' => null]);

    $response = $this->getJson('/api/v1/courses/no-instructor');

    $response->assertOk()->assertJsonPath('instructor', null);
});
