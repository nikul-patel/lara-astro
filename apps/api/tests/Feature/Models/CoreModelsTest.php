<?php

use App\Models\Astrologer;
use App\Models\AvailabilitySlot;
use App\Models\BirthChart;
use App\Models\Client;
use App\Models\GoogleCalendarConnection;
use App\Models\Service;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

test('an astrologer has services, availability slots, and an optional google calendar connection', function () {
    $astrologer = Astrologer::factory()->create();
    $service = Service::factory()->for($astrologer)->create();
    $slot = AvailabilitySlot::factory()->for($astrologer)->create();
    $connection = GoogleCalendarConnection::factory()->for($astrologer)->create();

    expect($astrologer->services)->toHaveCount(1)
        ->and($astrologer->services->first()->is($service))->toBeTrue()
        ->and($astrologer->availabilitySlots)->toHaveCount(1)
        ->and($astrologer->availabilitySlots->first()->is($slot))->toBeTrue()
        ->and($astrologer->googleCalendarConnection->is($connection))->toBeTrue();
});

test('services store explicit INR and USD pricing and belong to an astrologer', function () {
    $astrologer = Astrologer::factory()->create();
    $service = Service::factory()->for($astrologer)->create(['price_inr' => 1999, 'price_usd' => 24.99]);

    expect($service->astrologer->is($astrologer))->toBeTrue()
        ->and((float) $service->price_inr)->toBe(1999.0)
        ->and((float) $service->price_usd)->toBe(24.99);
});

test('astrologer slugs and service slugs must be unique', function () {
    Astrologer::factory()->create(['slug' => 'jane-doe']);

    expect(fn () => Astrologer::factory()->create(['slug' => 'jane-doe']))
        ->toThrow(QueryException::class);
});

test('a birth chart can exist without a client (guest usage)', function () {
    $chart = BirthChart::factory()->create(['client_id' => null]);

    expect($chart->client)->toBeNull()
        ->and($chart->result)->toBeArray();
});

test('a birth chart can be attached to a client and saved to their account', function () {
    $client = Client::factory()->create();
    $chart = BirthChart::factory()->for($client)->create();

    expect($chart->client->is($client))->toBeTrue()
        ->and($client->birthCharts)->toHaveCount(1);
});

test('guest clients have no password, registered clients do', function () {
    $guest = Client::factory()->guest()->create();
    $registered = Client::factory()->create();

    expect($guest->password)->toBeNull()
        ->and($registered->password)->not->toBeNull();
});

test('client emails must be unique', function () {
    Client::factory()->create(['email' => 'same@example.com']);

    expect(fn () => Client::factory()->create(['email' => 'same@example.com']))
        ->toThrow(QueryException::class);
});

test('google calendar tokens are encrypted at rest', function () {
    $connection = GoogleCalendarConnection::factory()->create(['access_token' => 'plain-text-token']);

    $raw = DB::table('google_calendar_connections')
        ->where('id', $connection->id)
        ->value('access_token');

    expect($raw)->not->toBe('plain-text-token')
        ->and($connection->fresh()->access_token)->toBe('plain-text-token');
});
