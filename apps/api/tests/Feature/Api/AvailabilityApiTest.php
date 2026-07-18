<?php

use App\Models\Astrologer;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Service;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

afterEach(function () {
    Carbon::setTestNow();
});

test('it requires astrologer_id, service_id, from, and to', function () {
    $this->getJson('/api/v1/availability')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['astrologer_id', 'service_id', 'from', 'to']);
});

test('it generates open slots from weekly availability windows', function () {
    $astrologer = Astrologer::factory()->create();
    $service = Service::factory()->for($astrologer)->create(['duration_minutes' => 30]);
    $day = now()->addDays(3)->startOfDay();

    AvailabilitySlot::factory()->for($astrologer)->create([
        'weekday' => $day->dayOfWeek,
        'start_time' => '09:00:00',
        'end_time' => '10:00:00',
        'is_active' => true,
    ]);

    $response = $this->getJson('/api/v1/availability?'.http_build_query([
        'astrologer_id' => $astrologer->id,
        'service_id' => $service->id,
        'from' => $day->toDateString(),
        'to' => $day->toDateString(),
    ]));

    $response->assertOk();
    expect($response->json())->toHaveCount(2)
        ->and($response->json('0.available'))->toBeTrue();

    $starts = collect($response->json())->pluck('start')->map(fn ($s) => CarbonImmutable::parse($s)->format('H:i'));
    expect($starts->all())->toBe(['09:00', '09:30']);
});

test('an existing blocking booking removes only its own slot', function () {
    $astrologer = Astrologer::factory()->create();
    $service = Service::factory()->for($astrologer)->create(['duration_minutes' => 30]);
    $day = now()->addDays(3)->startOfDay();

    AvailabilitySlot::factory()->for($astrologer)->create([
        'weekday' => $day->dayOfWeek,
        'start_time' => '09:00:00',
        'end_time' => '10:00:00',
    ]);

    Booking::factory()->create([
        'astrologer_id' => $astrologer->id,
        'service_id' => $service->id,
        'client_id' => Client::factory(),
        'slot' => $day->copy()->setTime(9, 0),
        'status' => 'confirmed',
    ]);

    $response = $this->getJson('/api/v1/availability?'.http_build_query([
        'astrologer_id' => $astrologer->id,
        'service_id' => $service->id,
        'from' => $day->toDateString(),
        'to' => $day->toDateString(),
    ]));

    $response->assertOk();
    expect($response->json())->toHaveCount(1);
    $start = CarbonImmutable::parse($response->json('0.start'));
    expect($start->format('H:i'))->toBe('09:30');
});

test('a cancelled booking does not block its slot', function () {
    $astrologer = Astrologer::factory()->create();
    $service = Service::factory()->for($astrologer)->create(['duration_minutes' => 30]);
    $day = now()->addDays(3)->startOfDay();

    AvailabilitySlot::factory()->for($astrologer)->create([
        'weekday' => $day->dayOfWeek,
        'start_time' => '09:00:00',
        'end_time' => '10:00:00',
    ]);

    Booking::factory()->create([
        'astrologer_id' => $astrologer->id,
        'service_id' => $service->id,
        'client_id' => Client::factory(),
        'slot' => $day->copy()->setTime(9, 0),
        'status' => 'cancelled',
    ]);

    $response = $this->getJson('/api/v1/availability?'.http_build_query([
        'astrologer_id' => $astrologer->id,
        'service_id' => $service->id,
        'from' => $day->toDateString(),
        'to' => $day->toDateString(),
    ]));

    $response->assertOk();
    expect($response->json())->toHaveCount(2);
});

test('slots that have already passed today are excluded', function () {
    $astrologer = Astrologer::factory()->create();
    $service = Service::factory()->for($astrologer)->create(['duration_minutes' => 60]);

    Carbon::setTestNow(Carbon::parse('2026-07-20 12:00:00'));
    $today = Carbon::now()->startOfDay();

    AvailabilitySlot::factory()->for($astrologer)->create([
        'weekday' => $today->dayOfWeek,
        'start_time' => '09:00:00',
        'end_time' => '14:00:00',
    ]);

    $response = $this->getJson('/api/v1/availability?'.http_build_query([
        'astrologer_id' => $astrologer->id,
        'service_id' => $service->id,
        'from' => $today->toDateString(),
        'to' => $today->toDateString(),
    ]));

    $response->assertOk();
    expect($response->json())->toHaveCount(1);
    $start = CarbonImmutable::parse($response->json('0.start'));
    expect($start->format('H:i'))->toBe('13:00');
});

test('the service must belong to the requested astrologer', function () {
    $astrologer = Astrologer::factory()->create();
    $otherAstrologer = Astrologer::factory()->create();
    $service = Service::factory()->for($otherAstrologer)->create();

    $this->getJson('/api/v1/availability?'.http_build_query([
        'astrologer_id' => $astrologer->id,
        'service_id' => $service->id,
        'from' => now()->toDateString(),
        'to' => now()->addDay()->toDateString(),
    ]))->assertNotFound();
});
