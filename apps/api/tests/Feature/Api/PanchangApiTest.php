<?php

test('calculating a panchang returns all five limbs plus sunrise/sunset', function () {
    $response = $this->postJson('/api/v1/panchang', [
        'date' => '2024-01-07',
        'place' => 'Jaipur, India',
    ]);

    $response->assertOk()
        ->assertJsonPath('date', '2024-01-07')
        ->assertJsonPath('vaar.name', 'Ravivar')
        ->assertJsonStructure([
            'tithi' => ['number', 'name', 'paksha'],
            'nakshatra' => ['index', 'name', 'lord', 'pada'],
            'yoga' => ['index', 'name'],
            'karana',
            'sunrise',
            'sunset',
        ]);

    // Jaipur is well east of the timezone's reference meridian, so sunrise
    // should land in the morning, not near midnight — a sanity check the
    // longitude correction is applied in the right direction.
    [$hour] = explode(':', $response->json('sunrise'));
    expect((int) $hour)->toBeGreaterThanOrEqual(5)->toBeLessThanOrEqual(8);
});

test('date defaults to today when omitted', function () {
    $response = $this->postJson('/api/v1/panchang', ['place' => 'Delhi, India']);

    $response->assertOk()->assertJsonPath('date', now()->toDateString());
});

test('an unrecognized place still calculates, flagging the fallback', function () {
    $response = $this->postJson('/api/v1/panchang', ['date' => '2024-01-07', 'place' => 'Nowhereville']);

    $response->assertOk()->assertJsonPath('location_matched', false);
});
