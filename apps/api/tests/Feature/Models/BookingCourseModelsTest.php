<?php

use App\Models\Astrologer;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\LiveSession;
use App\Models\Service;

test('a booking gets an auto-generated reference number and guest token', function () {
    $booking = Booking::factory()->create();

    expect($booking->reference_number)->toStartWith('BK-')
        ->and($booking->guest_token)->not->toBeEmpty()
        ->and($booking->status)->toBe('pending_payment');
});

test('a booking belongs to an astrologer, service, and client', function () {
    $astrologer = Astrologer::factory()->create();
    $service = Service::factory()->for($astrologer)->create();
    $client = Client::factory()->create();

    $booking = Booking::factory()->create([
        'astrologer_id' => $astrologer->id,
        'service_id' => $service->id,
        'client_id' => $client->id,
    ]);

    expect($booking->astrologer->is($astrologer))->toBeTrue()
        ->and($booking->service->is($service))->toBeTrue()
        ->and($booking->client->is($client))->toBeTrue()
        ->and($client->bookings)->toHaveCount(1)
        ->and($service->bookings)->toHaveCount(1)
        ->and($astrologer->bookings)->toHaveCount(1);
});

test('booking reference numbers and guest tokens are unique', function () {
    $one = Booking::factory()->create();
    $two = Booking::factory()->create();

    expect($one->reference_number)->not->toBe($two->reference_number)
        ->and($one->guest_token)->not->toBe($two->guest_token);
});

test('confirming a booking records the UPI reference', function () {
    $booking = Booking::factory()->confirmed()->create();

    expect($booking->status)->toBe('confirmed')
        ->and($booking->upi_reference)->not->toBeNull();
});

test('a course has an instructor, modules with ordered lessons, and live sessions', function () {
    $astrologer = Astrologer::factory()->create();
    $course = Course::factory()->for($astrologer, 'instructor')->create(['type' => 'live']);
    $module = CourseModule::factory()->for($course)->create(['order' => 1]);
    CourseLesson::factory()->for($module, 'module')->create(['title' => 'B', 'order' => 2]);
    CourseLesson::factory()->for($module, 'module')->create(['title' => 'A', 'order' => 1]);
    $liveSession = LiveSession::factory()->for($course)->create();

    expect($course->instructor->is($astrologer))->toBeTrue()
        ->and($course->modules)->toHaveCount(1)
        ->and($module->lessons->pluck('title')->all())->toBe(['A', 'B'])
        ->and($course->liveSessions->first()->is($liveSession))->toBeTrue();
});

test('an enrollment gets an auto-generated reference number and belongs to a course and client', function () {
    $course = Course::factory()->create();
    $client = Client::factory()->create();

    $enrollment = Enrollment::factory()->create([
        'course_id' => $course->id,
        'client_id' => $client->id,
    ]);

    expect($enrollment->reference_number)->toStartWith('EN-')
        ->and($enrollment->status)->toBe('pending_payment')
        ->and($enrollment->course->is($course))->toBeTrue()
        ->and($enrollment->client->is($client))->toBeTrue()
        ->and($client->enrollments)->toHaveCount(1)
        ->and($course->enrollments)->toHaveCount(1);
});

test('confirming an enrollment records the UPI reference', function () {
    $enrollment = Enrollment::factory()->confirmed()->create();

    expect($enrollment->status)->toBe('confirmed')
        ->and($enrollment->upi_reference)->not->toBeNull();
});
