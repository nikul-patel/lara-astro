<?php

use App\Models\Astrologer;
use App\Models\AvailabilitySlot;
use App\Models\BirthChart;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Service;
use App\Models\Setting;
use Carbon\CarbonImmutable;

function bookingPayload(Astrologer $astrologer, Service $service, array $overrides = []): array
{
    $slot = $overrides['slot'] ?? now()->addDays(3)->setTime(10, 0)->toIso8601String();

    // The slot must fall inside a published availability window (see
    // BookingController::slotIsWithinAvailability) — create one covering
    // whichever weekday/time this payload's slot lands on.
    AvailabilitySlot::query()->firstOrCreate([
        'astrologer_id' => $astrologer->id,
        'weekday' => CarbonImmutable::parse($slot)->dayOfWeek,
    ], [
        'start_time' => '10:00:00',
        'end_time' => '18:00:00',
        'is_active' => true,
    ]);

    return array_merge([
        'astrologer_id' => $astrologer->id,
        'service_id' => $service->id,
        'slot' => $slot,
        'client' => [
            'name' => 'Riya Mehta',
            'email' => 'riya@example.com',
            'phone' => '+91 90000 00000',
        ],
    ], $overrides);
}

test('a guest can create a booking and receives upi details for confirmation', function () {
    $astrologer = Astrologer::factory()->create(['is_active' => true]);
    $service = Service::factory()->for($astrologer)->create(['is_active' => true, 'duration_minutes' => 30]);
    Setting::current()->update(['upi_id' => 'astro@upi']);

    $response = $this->postJson('/api/v1/bookings', bookingPayload($astrologer, $service));

    $response->assertStatus(201)
        ->assertJsonPath('status', 'pending_payment')
        ->assertJsonPath('upi_id', 'astro@upi')
        ->assertJsonPath('client.email', 'riya@example.com')
        ->assertJsonStructure(['id', 'reference_number', 'guest_token']);

    $booking = Booking::firstOrFail();
    expect($booking->client->email)->toBe('riya@example.com')
        ->and($booking->client->password)->toBeNull();
});

test('a repeat guest booking reuses the same client record', function () {
    $astrologer = Astrologer::factory()->create();
    $service = Service::factory()->for($astrologer)->create(['duration_minutes' => 30]);

    $this->postJson('/api/v1/bookings', bookingPayload($astrologer, $service))->assertStatus(201);
    $this->postJson('/api/v1/bookings', bookingPayload($astrologer, $service, [
        'slot' => now()->addDays(4)->setTime(10, 0)->toIso8601String(),
    ]))->assertStatus(201);

    expect(Client::where('email', 'riya@example.com')->count())->toBe(1)
        ->and(Booking::count())->toBe(2);
});

test('an authenticated client does not need to submit client details', function () {
    $astrologer = Astrologer::factory()->create();
    $service = Service::factory()->for($astrologer)->create(['duration_minutes' => 30]);
    $client = Client::factory()->create();
    $token = $client->createToken('test')->plainTextToken;

    $payload = bookingPayload($astrologer, $service);
    unset($payload['client']);

    $response = $this->postJson('/api/v1/bookings', $payload, ['Authorization' => "Bearer {$token}"]);

    $response->assertStatus(201)->assertJsonPath('client.email', $client->email);
    expect(Booking::firstOrFail()->client_id)->toBe($client->id);
});

test('booking an inactive service is rejected', function () {
    $astrologer = Astrologer::factory()->create();
    $service = Service::factory()->for($astrologer)->create(['is_active' => false]);

    $this->postJson('/api/v1/bookings', bookingPayload($astrologer, $service))
        ->assertJsonValidationErrors('service_id');
});

test('booking a past slot is rejected', function () {
    $astrologer = Astrologer::factory()->create();
    $service = Service::factory()->for($astrologer)->create();

    $this->postJson('/api/v1/bookings', bookingPayload($astrologer, $service, [
        'slot' => now()->subDay()->toIso8601String(),
    ]))->assertJsonValidationErrors('slot');
});

test('booking an already-taken slot is rejected', function () {
    $astrologer = Astrologer::factory()->create();
    $service = Service::factory()->for($astrologer)->create(['duration_minutes' => 30]);
    $slot = now()->addDays(3)->setTime(10, 0);

    Booking::factory()->create([
        'astrologer_id' => $astrologer->id,
        'service_id' => $service->id,
        'client_id' => Client::factory(),
        'slot' => $slot,
        'status' => 'confirmed',
    ]);

    $this->postJson('/api/v1/bookings', bookingPayload($astrologer, $service, [
        'slot' => $slot->toIso8601String(),
    ]))->assertJsonValidationErrors('slot');
});

test('booking a slot with no published availability is rejected', function () {
    $astrologer = Astrologer::factory()->create();
    $service = Service::factory()->for($astrologer)->create(['duration_minutes' => 30]);

    $this->postJson('/api/v1/bookings', [
        'astrologer_id' => $astrologer->id,
        'service_id' => $service->id,
        'slot' => now()->addDays(3)->setTime(10, 0)->toIso8601String(),
        'client' => ['name' => 'Riya Mehta', 'email' => 'riya@example.com', 'phone' => '+91 90000 00000'],
    ])->assertJsonValidationErrors('slot');
});

test('booking a slot outside the published window\'s hours is rejected', function () {
    $astrologer = Astrologer::factory()->create();
    $service = Service::factory()->for($astrologer)->create(['duration_minutes' => 30]);
    $slot = now()->addDays(3)->setTime(10, 0);

    AvailabilitySlot::factory()->for($astrologer)->create([
        'weekday' => $slot->dayOfWeek,
        'start_time' => '10:00:00',
        'end_time' => '18:00:00',
    ]);

    $this->postJson('/api/v1/bookings', [
        'astrologer_id' => $astrologer->id,
        'service_id' => $service->id,
        'slot' => $slot->setTime(20, 0)->toIso8601String(),
        'client' => ['name' => 'Riya Mehta', 'email' => 'riya@example.com', 'phone' => '+91 90000 00000'],
    ])->assertJsonValidationErrors('slot');
});

test('a birth chart belonging to someone else cannot be attached', function () {
    $astrologer = Astrologer::factory()->create();
    $service = Service::factory()->for($astrologer)->create();
    $otherClient = Client::factory()->create();
    $chart = BirthChart::factory()->create(['client_id' => $otherClient->id]);

    $this->postJson('/api/v1/bookings', bookingPayload($astrologer, $service, [
        'birth_chart_id' => $chart->id,
    ]))->assertJsonValidationErrors('birth_chart_id');
});

test('a guest can look up their booking with the guest token', function () {
    $astrologer = Astrologer::factory()->create();
    $service = Service::factory()->for($astrologer)->create();
    $created = $this->postJson('/api/v1/bookings', bookingPayload($astrologer, $service))->json();

    $this->getJson("/api/v1/bookings/{$created['id']}?token={$created['guest_token']}")
        ->assertOk()
        ->assertJsonPath('id', $created['id']);

    $this->getJson("/api/v1/bookings/{$created['id']}?token=wrong-token")->assertNotFound();
    $this->getJson("/api/v1/bookings/{$created['id']}")->assertNotFound();
});

test('an authenticated client can list their own bookings but not others\'', function () {
    $client = Client::factory()->create();
    $otherClient = Client::factory()->create();
    Booking::factory()->create(['client_id' => $client->id]);
    Booking::factory()->create(['client_id' => $otherClient->id]);
    $token = $client->createToken('test')->plainTextToken;

    $response = $this->getJson('/api/v1/me/bookings', ['Authorization' => "Bearer {$token}"]);

    $response->assertOk();
    expect($response->json())->toHaveCount(1)
        ->and($response->json('0.client.id'))->toBe($client->id);
});
