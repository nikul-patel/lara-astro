<?php

use App\Models\Client;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\LiveSession;

test('a guest can create an enrollment', function () {
    $course = Course::factory()->create(['is_active' => true]);

    $response = $this->postJson('/api/v1/enrollments', [
        'course_id' => $course->id,
        'client' => ['name' => 'Kunal Patel', 'email' => 'kunal@example.com', 'phone' => '9000000000'],
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('status', 'pending_payment')
        ->assertJsonPath('client.email', 'kunal@example.com');

    expect(Client::where('email', 'kunal@example.com')->exists())->toBeTrue();
});

test('enrolling in an inactive course is rejected', function () {
    $course = Course::factory()->create(['is_active' => false]);

    $this->postJson('/api/v1/enrollments', [
        'course_id' => $course->id,
        'client' => ['name' => 'Kunal Patel', 'email' => 'kunal2@example.com', 'phone' => '9000000000'],
    ])->assertJsonValidationErrors('course_id');
});

test('a confirmed enrollment exposes lesson video and live session links, a pending one does not', function () {
    $course = Course::factory()->create(['is_active' => true, 'type' => 'live']);
    $module = CourseModule::factory()->for($course)->create();
    CourseLesson::factory()->for($module, 'module')->create(['video_url' => 'https://videos.example/lesson.mp4']);
    LiveSession::factory()->for($course)->create(['meeting_url' => 'https://meet.example/session']);

    $client = Client::factory()->create();
    $token = $client->createToken('test')->plainTextToken;

    $pending = Enrollment::factory()->create(['course_id' => $course->id, 'client_id' => $client->id, 'status' => 'pending_payment']);
    $confirmed = Enrollment::factory()->confirmed()->create(['course_id' => $course->id, 'client_id' => $client->id]);

    $response = $this->getJson('/api/v1/me/enrollments', ['Authorization' => "Bearer {$token}"]);

    $response->assertOk();
    $pendingEntry = collect($response->json())->firstWhere('id', $pending->id);
    $confirmedEntry = collect($response->json())->firstWhere('id', $confirmed->id);

    expect($pendingEntry['course']['modules'][0]['lessons'][0])->not->toHaveKey('video_url')
        ->and($pendingEntry['course']['live_sessions'][0])->not->toHaveKey('meeting_url')
        ->and($confirmedEntry['course']['modules'][0]['lessons'][0])->toHaveKey('video_url')
        ->and($confirmedEntry['course']['live_sessions'][0])->toHaveKey('meeting_url');
});
