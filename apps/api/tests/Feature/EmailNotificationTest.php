<?php

use App\Mail\BookingConfirmedMail;
use App\Mail\BookingReceivedMail;
use App\Mail\BookingReminderMail;
use App\Mail\EnrollmentConfirmedMail;
use App\Mail\EnrollmentReceivedMail;
use App\Mail\NewBookingAdminMail;
use App\Mail\NewEnrollmentAdminMail;
use App\Models\Astrologer;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\RoleSeeder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

function notificationBookingPayload(Astrologer $astrologer, Service $service): array
{
    $slot = now()->addDays(3)->setTime(10, 0)->toIso8601String();

    AvailabilitySlot::query()->firstOrCreate([
        'astrologer_id' => $astrologer->id,
        'weekday' => CarbonImmutable::parse($slot)->dayOfWeek,
    ], [
        'start_time' => '10:00:00',
        'end_time' => '18:00:00',
        'is_active' => true,
    ]);

    return [
        'astrologer_id' => $astrologer->id,
        'service_id' => $service->id,
        'slot' => $slot,
        'client' => [
            'name' => 'Riya Mehta',
            'email' => 'riya@example.com',
            'phone' => '+91 90000 00000',
        ],
    ];
}

test('all notification mailables are queued (ShouldQueue)', function () {
    $classes = [
        BookingReceivedMail::class,
        BookingConfirmedMail::class,
        NewBookingAdminMail::class,
        BookingReminderMail::class,
        EnrollmentReceivedMail::class,
        EnrollmentConfirmedMail::class,
        NewEnrollmentAdminMail::class,
    ];

    foreach ($classes as $class) {
        expect(is_subclass_of($class, ShouldQueue::class))->toBeTrue();
    }
});

test('creating a booking queues a client confirmation and an admin alert', function () {
    Mail::fake();
    Setting::current()->update(['upi_id' => 'astro@upi', 'contact' => ['email' => 'admin@studio.test']]);

    $astrologer = Astrologer::factory()->create(['is_active' => true]);
    $service = Service::factory()->for($astrologer)->create(['is_active' => true, 'duration_minutes' => 30]);

    $this->postJson('/api/v1/bookings', notificationBookingPayload($astrologer, $service))
        ->assertStatus(201);

    Mail::assertQueued(BookingReceivedMail::class, fn ($mail) => $mail->hasTo('riya@example.com'));
    Mail::assertQueued(NewBookingAdminMail::class, fn ($mail) => $mail->hasTo('admin@studio.test'));
});

test('the admin alert recipient falls back to config when Settings has no contact email', function () {
    Mail::fake();
    config(['mail.admin_address' => 'fallback@studio.test']);
    Setting::current()->update(['contact' => null]);

    $astrologer = Astrologer::factory()->create(['is_active' => true]);
    $service = Service::factory()->for($astrologer)->create(['is_active' => true, 'duration_minutes' => 30]);

    $this->postJson('/api/v1/bookings', notificationBookingPayload($astrologer, $service))
        ->assertStatus(201);

    Mail::assertQueued(NewBookingAdminMail::class, fn ($mail) => $mail->hasTo('fallback@studio.test'));
});

test('creating an enrollment queues a client confirmation and an admin alert', function () {
    Mail::fake();
    Setting::current()->update(['contact' => ['email' => 'admin@studio.test']]);
    $course = Course::factory()->create(['is_active' => true]);

    $this->postJson('/api/v1/enrollments', [
        'course_id' => $course->id,
        'client' => ['name' => 'Kunal Patel', 'email' => 'kunal@example.com', 'phone' => '9000000000'],
    ])->assertStatus(201);

    Mail::assertQueued(EnrollmentReceivedMail::class, fn ($mail) => $mail->hasTo('kunal@example.com'));
    Mail::assertQueued(NewEnrollmentAdminMail::class, fn ($mail) => $mail->hasTo('admin@studio.test'));
});

test('confirming a booking payment queues the confirmation email once', function () {
    Mail::fake();
    $this->seed(RoleSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $booking = Booking::factory()->create(['status' => 'pending_payment']);

    $this->actingAs($admin)->put("/bookings/{$booking->id}", [
        'status' => 'confirmed',
        'slot' => $booking->slot->format('Y-m-d\TH:i'),
        'upi_reference' => 'UPI-TEST1234',
    ])->assertRedirect('/bookings');

    Mail::assertQueued(BookingConfirmedMail::class, fn ($mail) => $mail->hasTo($booking->client->email));

    // Re-saving an already-confirmed booking must not resend the email.
    $this->actingAs($admin)->put("/bookings/{$booking->id}", [
        'status' => 'confirmed',
        'slot' => $booking->slot->format('Y-m-d\TH:i'),
        'upi_reference' => 'UPI-TEST1234',
    ])->assertRedirect('/bookings');

    Mail::assertQueued(BookingConfirmedMail::class, 1);
});

test('confirming an enrollment payment queues the confirmation email', function () {
    Mail::fake();
    $this->seed(RoleSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $enrollment = Enrollment::factory()->create(['status' => 'pending_payment']);

    $this->actingAs($admin)->put("/enrollments/{$enrollment->id}", [
        'status' => 'confirmed',
        'upi_reference' => 'UPI-TEST1234',
    ])->assertRedirect('/enrollments');

    Mail::assertQueued(EnrollmentConfirmedMail::class, fn ($mail) => $mail->hasTo($enrollment->client->email));
});

test('the reminder command queues reminders for in-window confirmed bookings and is idempotent', function () {
    Mail::fake();

    $due = Booking::factory()->confirmed()->create(['slot' => now()->addHours(12)]);
    $tooFar = Booking::factory()->confirmed()->create(['slot' => now()->addDays(3)]);
    $pending = Booking::factory()->create(['status' => 'pending_payment', 'slot' => now()->addHours(6)]);

    $this->artisan('bookings:send-reminders')->assertSuccessful();

    Mail::assertQueued(BookingReminderMail::class, 1);
    Mail::assertQueued(BookingReminderMail::class, fn ($mail) => $mail->hasTo($due->client->email));
    expect($due->fresh()->reminder_sent_at)->not->toBeNull()
        ->and($tooFar->fresh()->reminder_sent_at)->toBeNull()
        ->and($pending->fresh()->reminder_sent_at)->toBeNull();

    // Running again must not re-send.
    $this->artisan('bookings:send-reminders')->assertSuccessful();
    Mail::assertQueued(BookingReminderMail::class, 1);
});
