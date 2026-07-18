<?php

use App\Models\Astrologer;
use App\Models\Service;

test('it lists only active astrologers', function () {
    Astrologer::factory()->create(['name' => 'Active One', 'is_active' => true]);
    Astrologer::factory()->create(['name' => 'Inactive One', 'is_active' => false]);

    $response = $this->getJson('/api/v1/astrologers');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.name'))->toBe('Active One');
});

test('it shows an astrologer with active services only', function () {
    $astrologer = Astrologer::factory()->create(['slug' => 'meera-rao', 'is_active' => true]);
    Service::factory()->for($astrologer)->create(['name' => 'Active Service', 'is_active' => true]);
    Service::factory()->for($astrologer)->create(['name' => 'Inactive Service', 'is_active' => false]);

    $response = $this->getJson('/api/v1/astrologers/meera-rao');

    $response->assertOk();
    expect($response->json('services'))->toHaveCount(1)
        ->and($response->json('services.0.name'))->toBe('Active Service');
});

test('an inactive astrologer 404s on the public detail endpoint', function () {
    $astrologer = Astrologer::factory()->create(['slug' => 'hidden', 'is_active' => false]);

    $this->getJson("/api/v1/astrologers/{$astrologer->slug}")->assertNotFound();
});

test('it resolves per-locale astrologer content via the locale query param', function () {
    $astrologer = Astrologer::factory()->create(['is_active' => true]);

    $this->getJson('/api/v1/astrologers?locale=hi')->assertOk();
    $this->getJson('/api/v1/astrologers?locale=xx')->assertOk(); // unsupported -> falls back to en, doesn't error
});

test('it lists only active services and can filter by astrologer', function () {
    $astrologer = Astrologer::factory()->create();
    $otherAstrologer = Astrologer::factory()->create();
    Service::factory()->for($astrologer)->create(['is_active' => true]);
    Service::factory()->for($astrologer)->create(['is_active' => false]);
    Service::factory()->for($otherAstrologer)->create(['is_active' => true]);

    $response = $this->getJson("/api/v1/services?astrologer_id={$astrologer->id}");

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
});

test('service prices are numeric floats, not decimal strings', function () {
    $astrologer = Astrologer::factory()->create();
    Service::factory()->for($astrologer)->create(['price_inr' => 1999.50, 'price_usd' => 24.25]);

    $response = $this->getJson("/api/v1/services?astrologer_id={$astrologer->id}");

    expect($response->json('data.0.price_inr'))->toBeFloat()->toBe(1999.5)
        ->and($response->json('data.0.price_usd'))->toBeFloat()->toBe(24.25);
});
