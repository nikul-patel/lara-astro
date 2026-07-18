<?php

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\LiveSession;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('Admin');
});

test('an admin can add, edit, and delete a course module', function () {
    $course = Course::factory()->create();

    $this->actingAs($this->admin)->post("/courses/{$course->id}/modules", [
        'module_title' => 'Module One',
        'order' => 0,
    ])->assertRedirect(route('courses.edit', $course));

    $module = CourseModule::firstOrFail();
    expect($module->title)->toBe('Module One');

    $this->actingAs($this->admin)->put("/modules/{$module->id}", [
        'module_title' => 'Module One (Updated)',
        'order' => 1,
    ])->assertRedirect(route('courses.edit', $course));

    expect($module->fresh()->title)->toBe('Module One (Updated)');

    $this->actingAs($this->admin)->delete("/modules/{$module->id}")
        ->assertRedirect(route('courses.edit', $course));

    expect(CourseModule::find($module->id))->toBeNull();
});

test('an admin can add, edit, and delete a lesson within a module', function () {
    $module = CourseModule::factory()->create();

    $this->actingAs($this->admin)->post("/modules/{$module->id}/lessons", [
        'lesson_title' => 'Lesson One',
        'video_url' => 'https://example.com/video.mp4',
        'duration_minutes' => 12,
        'order' => 0,
    ])->assertRedirect(route('courses.edit', $module->course_id));

    $lesson = CourseLesson::firstOrFail();
    expect($lesson->title)->toBe('Lesson One');

    $this->actingAs($this->admin)->put("/lessons/{$lesson->id}", [
        'lesson_title' => 'Lesson One (Updated)',
        'video_url' => 'https://example.com/video2.mp4',
        'duration_minutes' => 15,
        'order' => 1,
    ])->assertRedirect(route('courses.edit', $module->course_id));

    expect($lesson->fresh()->title)->toBe('Lesson One (Updated)');

    $this->actingAs($this->admin)->delete("/lessons/{$lesson->id}")
        ->assertRedirect(route('courses.edit', $module->course_id));

    expect(CourseLesson::find($lesson->id))->toBeNull();
});

test('an admin can add, edit, and delete a live session', function () {
    $course = Course::factory()->create(['type' => 'live']);
    $startsAt = now()->addWeek();

    $this->actingAs($this->admin)->post("/courses/{$course->id}/live-sessions", [
        'starts_at' => $startsAt->format('Y-m-d\TH:i'),
        'ends_at' => $startsAt->copy()->addHour()->format('Y-m-d\TH:i'),
        'meeting_url' => 'https://meet.example.com/session',
    ])->assertRedirect(route('courses.edit', $course));

    $liveSession = LiveSession::firstOrFail();
    expect($liveSession->meeting_url)->toBe('https://meet.example.com/session');

    $newStart = $startsAt->copy()->addDay();
    $this->actingAs($this->admin)->put("/live-sessions/{$liveSession->id}", [
        'starts_at' => $newStart->format('Y-m-d\TH:i'),
        'ends_at' => $newStart->copy()->addHour()->format('Y-m-d\TH:i'),
        'meeting_url' => 'https://meet.example.com/session-2',
    ])->assertRedirect(route('courses.edit', $course));

    expect($liveSession->fresh()->meeting_url)->toBe('https://meet.example.com/session-2');

    $this->actingAs($this->admin)->delete("/live-sessions/{$liveSession->id}")
        ->assertRedirect(route('courses.edit', $course));

    expect(LiveSession::find($liveSession->id))->toBeNull();
});

test('a live session end time must be after its start time', function () {
    $course = Course::factory()->create(['type' => 'live']);
    $startsAt = now()->addWeek();

    $this->actingAs($this->admin)->post("/courses/{$course->id}/live-sessions", [
        'starts_at' => $startsAt->format('Y-m-d\TH:i'),
        'ends_at' => $startsAt->copy()->subHour()->format('Y-m-d\TH:i'),
    ])->assertSessionHasErrors('ends_at');
});

test('a live session cannot be added to a recorded course', function () {
    $course = Course::factory()->create(['type' => 'recorded']);
    $startsAt = now()->addWeek();

    $this->actingAs($this->admin)->post("/courses/{$course->id}/live-sessions", [
        'starts_at' => $startsAt->format('Y-m-d\TH:i'),
    ])->assertRedirect(route('courses.edit', $course));

    expect(LiveSession::count())->toBe(0);
});

test('a live session cannot be edited after its course becomes recorded', function () {
    $liveSession = LiveSession::factory()->create();
    $liveSession->course->update(['type' => 'recorded']);
    $newStart = now()->addWeek();

    $this->actingAs($this->admin)->put("/live-sessions/{$liveSession->id}", [
        'starts_at' => $newStart->format('Y-m-d\TH:i'),
    ])->assertRedirect(route('courses.edit', $liveSession->course_id));

    expect($liveSession->fresh()->starts_at->equalTo($liveSession->starts_at))->toBeTrue();
});
