<?php

test('calculating a transit report returns today\'s Gochar for all 9 grahas, each with a house-from-Moon and narrative text', function () {
    $response = $this->postJson('/api/v1/transits', [
        'name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India',
        'reference_date' => '2026-08-22',
    ]);

    $response->assertOk()->assertJsonStructure([
        'reference_date', 'natal_moon_sign',
        'transits' => [
            'Sun' => ['sign', 'house_from_moon', 'text'],
            'Moon' => ['sign', 'house_from_moon', 'text'],
            'Mercury' => ['sign', 'house_from_moon', 'text'],
            'Venus' => ['sign', 'house_from_moon', 'text'],
            'Mars' => ['sign', 'house_from_moon', 'text'],
            'Jupiter' => ['sign', 'house_from_moon', 'text'],
            'Saturn' => ['sign', 'house_from_moon', 'text'],
            'Rahu' => ['sign', 'house_from_moon', 'text'],
            'Ketu' => ['sign', 'house_from_moon', 'text'],
        ],
    ]);

    expect($response->json('reference_date'))->toBe('2026-08-22');

    foreach ($response->json('transits') as $transit) {
        expect($transit['house_from_moon'])->toBeGreaterThanOrEqual(1)->toBeLessThanOrEqual(12);
        expect(strlen($transit['text']))->toBeGreaterThan(150);
    }

    // Rahu and Ketu's house-from-Moon must always be exactly 6 apart (they're always opposite signs).
    $rahuHouse = $response->json('transits.Rahu.house_from_moon');
    $ketuHouse = $response->json('transits.Ketu.house_from_moon');
    expect((($ketuHouse - $rahuHouse) + 12) % 12)->toBe(6);
});

test('reference_date defaults to today when omitted', function () {
    $this->postJson('/api/v1/transits', [
        'name' => 'Test', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Delhi, India',
    ])->assertOk()->assertJson(['reference_date' => now()->toDateString()]);
});

test('the same inputs on the same reference_date return the exact same transit signs, deterministically', function () {
    $input = ['name' => 'Test', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Delhi, India', 'reference_date' => '2026-08-22'];

    $first = $this->postJson('/api/v1/transits', $input);
    $second = $this->postJson('/api/v1/transits', $input);

    expect($first->json('transits'))->toBe($second->json('transits'));
});
