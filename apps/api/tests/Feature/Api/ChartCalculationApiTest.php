<?php

use App\Models\Setting;

test('calculating a chart returns planetary positions, houses, and a recommendation', function () {
    $response = $this->postJson('/api/v1/chart', [
        'name' => 'Ananya Singh',
        'dob' => '1994-05-12',
        'time' => '14:30',
        'place' => 'Jaipur, India',
    ]);

    $response->assertOk()
        ->assertJsonPath('system', 'vedic')
        ->assertJsonPath('chart_style', 'north_indian')
        ->assertJsonPath('timezone', 'Asia/Kolkata')
        ->assertJsonPath('recommendation.system', 'vedic')
        ->assertJsonPath('recommendation.chart_style', 'north_indian')
        ->assertJsonCount(9, 'planetary_positions')
        ->assertJsonCount(12, 'houses');

    $planetNames = collect($response->json('planetary_positions'))->pluck('name');
    expect($planetNames)->toContain('Sun', 'Moon', 'Rahu', 'Ketu');

    $houseNumbers = collect($response->json('houses'))->pluck('number');
    expect($houseNumbers->all())->toBe(range(1, 12));
});

test('a vedic chart includes the Moon\'s nakshatra and a 9-period Vimshottari Mahadasha timeline', function () {
    $response = $this->postJson('/api/v1/chart', [
        'name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India',
        'system' => 'vedic',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'nakshatra' => ['index', 'name', 'lord', 'pada'],
            'dasha' => ['mahadasha'],
        ])
        ->assertJsonCount(9, 'dasha.mahadasha');

    $mahadasha = $response->json('dasha.mahadasha');
    expect($mahadasha[0])->toHaveKeys(['lord', 'start', 'end', 'antardashas'])
        ->and($mahadasha[0]['antardashas'])->toHaveCount(9);
});

test('a western chart has no nakshatra or dasha (sidereal-only concepts)', function () {
    $response = $this->postJson('/api/v1/chart', [
        'name' => 'Test', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Delhi, India',
        'system' => 'western',
    ]);

    $response->assertOk()
        ->assertJsonPath('nakshatra', null)
        ->assertJsonPath('dasha', null);
});

test('a vedic chart includes a yogas array (possibly empty) and a western chart has none', function () {
    $vedic = $this->postJson('/api/v1/chart', [
        'name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India',
        'system' => 'vedic',
    ]);
    $western = $this->postJson('/api/v1/chart', [
        'name' => 'Test', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Delhi, India',
        'system' => 'western',
    ]);

    expect($vedic->json('yogas'))->toBeArray()
        ->and($western->json('yogas'))->toBeNull();
});

test('a vedic chart includes predictions for all 4 life areas by default', function () {
    $response = $this->postJson('/api/v1/chart', [
        'name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India',
        'system' => 'vedic',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['predictions' => ['marriage', 'career', 'education', 'foreign_settlement']]);
    expect($response->json('remedies'))->toBeArray();
});

test('a deployment can disable predictions and remedies content', function () {
    Setting::current()->update(['astrology_predictions_enabled' => false]);

    $response = $this->postJson('/api/v1/chart', [
        'name' => 'Test', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Delhi, India',
        'system' => 'vedic',
    ]);

    $response->assertOk()
        ->assertJsonPath('predictions', null)
        ->assertJsonPath('remedies', null);
});

test('region recommendation matches the PRD table for south and east Indian birth places', function () {
    $south = $this->postJson('/api/v1/chart', [
        'name' => 'Test', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Chennai, Tamil Nadu',
    ]);
    $east = $this->postJson('/api/v1/chart', [
        'name' => 'Test', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Kolkata, West Bengal',
    ]);
    $international = $this->postJson('/api/v1/chart', [
        'name' => 'Test', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'London, UK',
    ]);

    expect($south->json('recommendation.chart_style'))->toBe('south_indian')
        ->and($east->json('recommendation.chart_style'))->toBe('east_indian')
        ->and($international->json('recommendation'))->toMatchArray(['system' => 'vedic', 'chart_style' => 'north_indian']);
});

test('an explicit system/chart_style override is respected over the recommendation', function () {
    $response = $this->postJson('/api/v1/chart', [
        'name' => 'Test', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Chennai, India',
        'system' => 'western',
    ]);

    $response->assertOk()
        ->assertJsonPath('system', 'western')
        ->assertJsonPath('chart_style', null)
        ->assertJsonPath('recommendation.chart_style', 'south_indian');
});

test('a deployment can disable the western system override', function () {
    Setting::current()->update(['astrology_western_enabled' => false]);

    $this->postJson('/api/v1/chart', [
        'name' => 'Test', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Delhi, India',
        'system' => 'western',
    ])->assertJsonValidationErrors('system');
});

test('a deployment can force a specific chart style regardless of region', function () {
    Setting::current()->update(['astrology_forced_chart_style' => 'south_indian']);

    $response = $this->postJson('/api/v1/chart', [
        'name' => 'Test', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Delhi, India',
    ]);

    $response->assertJsonPath('chart_style', 'south_indian')
        ->assertJsonPath('recommendation.chart_style', 'south_indian');
});

test('a forced chart style overrides an explicit request chart_style, not just the recommendation', function () {
    Setting::current()->update(['astrology_forced_chart_style' => 'south_indian']);

    $response = $this->postJson('/api/v1/chart', [
        'name' => 'Test', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Chennai, India',
        'chart_style' => 'north_indian',
    ]);

    $response->assertJsonPath('chart_style', 'south_indian');
});

test('an invalid time returns a validation error instead of a server error', function () {
    $this->postJson('/api/v1/chart', [
        'name' => 'Test', 'dob' => '1994-05-12', 'time' => 'not-a-time', 'place' => 'Delhi, India',
    ])->assertJsonValidationErrors('time');
});

test('vedic and western produce different (ayanamsa-shifted) planetary longitudes for the same birth details', function () {
    $payload = ['name' => 'Test', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Delhi, India'];

    $vedic = $this->postJson('/api/v1/chart', [...$payload, 'system' => 'vedic'])->json();
    $western = $this->postJson('/api/v1/chart', [...$payload, 'system' => 'western'])->json();

    $vedicSun = collect($vedic['planetary_positions'])->firstWhere('name', 'Sun')['longitude'];
    $westernSun = collect($western['planetary_positions'])->firstWhere('name', 'Sun')['longitude'];

    // The two should differ by roughly the Lahiri ayanamsa (~24° in the
    // 1990s), not be identical and not be wildly different.
    $diff = abs($westernSun - $vedicSun);
    if ($diff > 180) {
        $diff = 360 - $diff;
    }

    expect($diff)->toBeGreaterThan(20)->toBeLessThan(28);
});

test('validation rejects a missing required field', function () {
    $this->postJson('/api/v1/chart', [
        'name' => 'Test', 'time' => '14:30', 'place' => 'Delhi, India',
    ])->assertJsonValidationErrors('dob');
});

test('a vedic chart includes Ashtakvarga and a western chart has none', function () {
    $vedic = $this->postJson('/api/v1/chart', [
        'name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India',
        'system' => 'vedic',
    ]);
    $western = $this->postJson('/api/v1/chart', [
        'name' => 'Test', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Delhi, India',
        'system' => 'western',
    ]);

    $vedic->assertOk()->assertJsonStructure([
        'ashtakvarga' => ['bhinnashtakavarga', 'sarvashtakavarga'],
    ]);
    expect($western->json('ashtakvarga'))->toBeNull();

    $sarvashtakavarga = $vedic->json('ashtakvarga.sarvashtakavarga');
    expect($sarvashtakavarga)->toHaveCount(12)
        ->and(array_sum($sarvashtakavarga))->toBe(337);

    $bhinnashtakavarga = $vedic->json('ashtakvarga.bhinnashtakavarga');
    expect(array_keys($bhinnashtakavarga))->toEqualCanonicalizing(
        ['Sun', 'Moon', 'Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn'],
    );
});

test('an unrecognized place still calculates, falling back to Delhi and flagging the fallback', function () {
    $response = $this->postJson('/api/v1/chart', [
        'name' => 'Test', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Nowhereville',
    ]);

    $response->assertOk()->assertJsonPath('location_matched', false);
});
