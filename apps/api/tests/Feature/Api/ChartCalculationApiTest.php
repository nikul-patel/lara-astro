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

test('a vedic chart includes predictions for all 4 life areas, an Ascendant description, and per-dasha-lord narratives by default', function () {
    $response = $this->postJson('/api/v1/chart', [
        'name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India',
        'system' => 'vedic',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'predictions' => [
                'marriage', 'career', 'education', 'foreign_settlement',
                'ascendant' => ['key', 'text'],
                'dasha_narrative',
            ],
        ]);
    expect($response->json('remedies'))->toBeArray();

    $dashaNarrative = $response->json('predictions.dasha_narrative');
    expect($dashaNarrative)->toHaveCount(9);
    foreach ($dashaNarrative as $period) {
        expect($period)->toHaveKeys(['lord', 'house', 'text']);
        expect(strlen($period['text']))->toBeGreaterThan(150);
    }

    expect(strlen($response->json('predictions.ascendant.text')))->toBeGreaterThan(200);
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

test('a vedic chart includes a Yogini Dasha timeline and a western chart has none', function () {
    $vedic = $this->postJson('/api/v1/chart', [
        'name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India',
        'system' => 'vedic',
    ]);
    $western = $this->postJson('/api/v1/chart', [
        'name' => 'Test', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Delhi, India',
        'system' => 'western',
    ]);

    $vedic->assertOk();
    $yoginiTimeline = $vedic->json('dasha.yogini');
    expect($yoginiTimeline)->toBeArray()->not->toBeEmpty();
    expect($yoginiTimeline[0])->toHaveKeys(['lord', 'start', 'end', 'antardashas'])
        ->and($yoginiTimeline[0]['antardashas'])->toHaveCount(8);

    expect($western->json('dasha'))->toBeNull();
});

test('a vedic chart includes Jaimini Atmakaraka/Karakamsa/Swamsa/Char Dasha and a western chart has none', function () {
    $vedic = $this->postJson('/api/v1/chart', [
        'name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India',
        'system' => 'vedic',
    ]);
    $western = $this->postJson('/api/v1/chart', [
        'name' => 'Test', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Delhi, India',
        'system' => 'western',
    ]);

    $vedic->assertOk()->assertJsonStructure([
        'jaimini' => ['atmakaraka', 'karakamsa', 'swamsa', 'char_dasha'],
    ]);
    expect($vedic->json('jaimini.char_dasha'))->toHaveCount(12);
    expect($western->json('jaimini'))->toBeNull();
});

test('both vedic and western charts include a Western-style aspects list', function () {
    $vedic = $this->postJson('/api/v1/chart', [
        'name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India',
        'system' => 'vedic',
    ]);
    $western = $this->postJson('/api/v1/chart', [
        'name' => 'Test', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Delhi, India',
        'system' => 'western',
    ]);

    $vedic->assertOk();
    $western->assertOk();
    expect($vedic->json('aspects'))->toBeArray()
        ->and($western->json('aspects'))->toBeArray();

    if ($vedic->json('aspects') !== []) {
        expect($vedic->json('aspects.0'))->toHaveKeys(['from', 'to', 'aspect', 'angle', 'orb']);
    }
});

test('a vedic chart includes a 42-row planetary friendship table and a western chart has none', function () {
    $vedic = $this->postJson('/api/v1/chart', [
        'name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India',
        'system' => 'vedic',
    ]);
    $western = $this->postJson('/api/v1/chart', [
        'name' => 'Test', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Delhi, India',
        'system' => 'western',
    ]);

    $vedic->assertOk()->assertJsonCount(42, 'friendship_table');
    $row = $vedic->json('friendship_table.0');
    expect($row)->toHaveKeys(['from', 'to', 'natural', 'temporal', 'combined']);

    expect($western->json('friendship_table'))->toBeNull();
});

test('a vedic chart includes an Avkahada Chakra panel and a western chart has none', function () {
    $vedic = $this->postJson('/api/v1/chart', [
        'name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India',
        'system' => 'vedic',
    ]);
    $western = $this->postJson('/api/v1/chart', [
        'name' => 'Test', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Delhi, India',
        'system' => 'western',
    ]);

    $vedic->assertOk()->assertJsonStructure([
        'avkahada' => ['varna', 'yoni', 'gana', 'vashya', 'nadi', 'good_planets', 'friendly_signs', 'lucky_stone', 'lucky_day'],
    ]);
    expect($western->json('avkahada'))->toBeNull();
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

test('a vedic chart includes Shadbala and Bhavabala and a western chart has none', function () {
    $vedic = $this->postJson('/api/v1/chart', [
        'name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India',
        'system' => 'vedic',
    ]);
    $western = $this->postJson('/api/v1/chart', [
        'name' => 'Test', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Delhi, India',
        'system' => 'western',
    ]);

    $vedic->assertOk()->assertJsonStructure([
        'shadbala' => [
            'sthana' => ['uchcha', 'saptavargaja', 'ojayugmarasyamsa', 'kendradi', 'drekkana', 'total'],
            'dig', 'kala' => ['nathonnatha', 'paksha', 'tribhaga', 'vara', 'hora', 'ayana', 'yuddha', 'total'],
            'chesta', 'naisargika', 'drik', 'total_virupas', 'total_rupas', 'minimum_required_rupas', 'is_strong',
        ],
        'bhavabala' => ['bhavadhipati', 'bhava_drishti', 'total_virupas', 'total_rupas'],
    ]);
    expect($western->json('shadbala'))->toBeNull();
    expect($western->json('bhavabala'))->toBeNull();

    $planets = ['Sun', 'Moon', 'Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn'];
    expect(array_keys($vedic->json('shadbala.total_rupas')))->toEqualCanonicalizing($planets);
    foreach ($planets as $planet) {
        // Naisargika Bala alone (fixed, chart-independent) guarantees every total is positive.
        expect($vedic->json("shadbala.total_virupas.{$planet}"))->toBeGreaterThan(0);
    }

    expect($vedic->json('bhavabala.total_rupas.1'))->not->toBeNull();
});

test('a vedic chart includes KP sub-lords for every planet and the ascendant, and a western chart has none', function () {
    $vedic = $this->postJson('/api/v1/chart', [
        'name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India',
        'system' => 'vedic',
    ]);
    $western = $this->postJson('/api/v1/chart', [
        'name' => 'Test', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Delhi, India',
        'system' => 'western',
    ]);

    $vedic->assertOk()->assertJsonStructure([
        'kp' => [
            'sub_lords' => [
                'Sun' => ['nakshatra', 'nakshatra_lord', 'pada', 'sub_lord'],
            ],
            'ascendant' => ['nakshatra', 'nakshatra_lord', 'pada', 'sub_lord'],
        ],
    ]);
    expect(array_keys($vedic->json('kp.sub_lords')))->toEqualCanonicalizing(
        ['Sun', 'Moon', 'Mercury', 'Venus', 'Mars', 'Jupiter', 'Saturn', 'Rahu', 'Ketu'],
    );

    $validSubLords = ['Ketu', 'Venus', 'Sun', 'Moon', 'Mars', 'Rahu', 'Jupiter', 'Saturn', 'Mercury'];
    expect($vedic->json('kp.sub_lords.Sun.sub_lord'))->toBeIn($validSubLords);
    expect($vedic->json('kp.ascendant.sub_lord'))->toBeIn($validSubLords);

    expect($western->json('kp'))->toBeNull();
});

test('a vedic chart includes a Lal Kitab fixed-house chart and Pucca Ghar, and a western chart has none', function () {
    $vedic = $this->postJson('/api/v1/chart', [
        'name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India',
        'system' => 'vedic',
    ]);
    $western = $this->postJson('/api/v1/chart', [
        'name' => 'Test', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Delhi, India',
        'system' => 'western',
    ]);

    $vedic->assertOk()->assertJsonStructure([
        'lal_kitab' => ['houses', 'ascendant_house', 'empty_houses', 'pucca_ghar'],
    ]);
    expect($western->json('lal_kitab'))->toBeNull();

    $houses = $vedic->json('lal_kitab.houses');
    expect($houses)->toHaveCount(12);
    expect($houses[0])->toBe(['number' => 1, 'sign' => 'Aries', 'planets' => $houses[0]['planets']]);
    expect($houses[11]['sign'])->toBe('Pisces');

    expect(array_keys($vedic->json('lal_kitab.pucca_ghar')))->toEqualCanonicalizing(
        ['Sun', 'Moon', 'Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn'],
    );
});

test('both vedic and western charts include 12 real Placidus Bhava Madhya cusps, 180 degrees apart from their opposite', function () {
    $vedic = $this->postJson('/api/v1/chart', [
        'name' => 'Ananya Singh', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India',
        'system' => 'vedic',
    ]);
    $western = $this->postJson('/api/v1/chart', [
        'name' => 'Test', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Delhi, India',
        'system' => 'western',
    ]);

    foreach ([$vedic, $western] as $response) {
        $response->assertOk()->assertJsonStructure([
            'bhava_madhya' => [['house', 'sign', 'degree', 'longitude']],
        ]);

        $bhavaMadhya = $response->json('bhava_madhya');
        expect($bhavaMadhya)->toHaveCount(12);
        expect(array_column($bhavaMadhya, 'house'))->toBe(range(1, 12));

        $byHouse = collect($bhavaMadhya)->keyBy('house');
        foreach ([[1, 7], [2, 8], [3, 9], [4, 10], [5, 11], [6, 12]] as [$a, $b]) {
            $delta = fmod($byHouse[$b]['longitude'] - $byHouse[$a]['longitude'], 360);
            $delta = $delta < 0 ? $delta + 360 : $delta;
            expect(abs($delta - 180))->toBeLessThan(0.01);
        }
    }
});

test('an unrecognized place still calculates, falling back to Delhi and flagging the fallback', function () {
    $response = $this->postJson('/api/v1/chart', [
        'name' => 'Test', 'dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Nowhereville',
    ]);

    $response->assertOk()->assertJsonPath('location_matched', false);
});
