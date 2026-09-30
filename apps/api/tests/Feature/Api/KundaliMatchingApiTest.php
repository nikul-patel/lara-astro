<?php

test('matching two charts returns all eight kootas and a total score', function () {
    $response = $this->postJson('/api/v1/kundali-matching', [
        'bride' => ['name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India'],
        'groom' => ['name' => 'Rohan Sharma', 'dob' => '1991-08-20', 'time' => '09:15', 'place' => 'Delhi, India'],
    ]);

    $response->assertOk()->assertJsonStructure([
        'total_points', 'max_points', 'minimum_recommended', 'is_recommended', 'has_nadi_dosha', 'has_bhakoot_dosha',
        'kootas' => [['name', 'points', 'max_points', 'description']],
        'bride' => ['nakshatra', 'rashi'],
        'groom' => ['nakshatra', 'rashi'],
    ]);

    expect($response->json('max_points'))->toBe(36)
        ->and($response->json('kootas'))->toHaveCount(8)
        ->and($response->json('total_points'))->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(36);

    // toEqual (value), not toBe (strict type): a whole-number float total
    // (e.g. 15.0) round-trips through JSON as an int (json_encode drops the
    // trailing ".0"), so the two sides can differ in PHP type even though
    // they're numerically identical.
    $sum = collect($response->json('kootas'))->sum('points');
    expect($sum)->toEqual($response->json('total_points'));
});

test('validation requires both bride and groom details', function () {
    $this->postJson('/api/v1/kundali-matching', [
        'bride' => ['name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India'],
    ])->assertJsonValidationErrors(['groom.name', 'groom.dob', 'groom.time', 'groom.place']);
});
