<?php

use App\Services\Astrology\KP\SubLord;

test('boundaries() divides a nakshatra into 9 unequal spans matching published KP sub-lord tables', function () {
    $boundaries = SubLord::boundaries('Ketu');

    expect($boundaries)->toHaveCount(9);
    expect(array_column($boundaries, 'lord'))->toBe(['Ketu', 'Venus', 'Sun', 'Moon', 'Mars', 'Rahu', 'Jupiter', 'Saturn', 'Mercury']);

    // Each span in arcminutes = 800' (one nakshatra) x lord-years / 120 —
    // these exact figures (46'40", 2°13'20" etc.) are well-known published
    // KP sub-lord span constants, not just internally-consistent arithmetic.
    $spansInArcminutes = array_map(fn (array $b) => round(($b['to'] - $b['from']) * 60, 2), $boundaries);
    expect($spansInArcminutes)->toBe([46.67, 133.33, 40.0, 66.67, 46.67, 120.0, 106.67, 126.67, 113.33]);

    // The spans must exactly tile the full 13°20' nakshatra, no gaps/overlaps.
    expect($boundaries[0]['from'])->toBe(0.0);
    expect($boundaries[8]['to'])->toBe(360 / 27);
    for ($i = 1; $i < 9; $i++) {
        expect($boundaries[$i]['from'])->toBe($boundaries[$i - 1]['to']);
    }
});

test('boundaries() starts the 9-lord cycle from the nakshatra\'s own lord, not always Ketu', function () {
    // Bharani (nakshatra index 1) is ruled by Venus — its sub-lord cycle
    // must start Venus, Sun, Moon, ... (same relative order, rotated).
    $boundaries = SubLord::boundaries('Venus');

    expect(array_column($boundaries, 'lord'))->toBe(['Venus', 'Sun', 'Moon', 'Mars', 'Rahu', 'Jupiter', 'Saturn', 'Mercury', 'Ketu']);
});

test('forLongitude() identifies the correct sub-lord at hand-picked points within Ashwini (Ketu-ruled)', function () {
    // Ashwini spans 0-13°20'. Sub-lord boundaries from the previous test:
    // Ketu 0-0°46'40" (0-0.7778), Venus 0.7778-3.0, Sun 3.0-3.6667, ...,
    // Mercury 11.4444-13.3333.
    expect(SubLord::forLongitude(0.0)['sub_lord'])->toBe('Ketu');
    expect(SubLord::forLongitude(0.5)['sub_lord'])->toBe('Ketu');
    expect(SubLord::forLongitude(1.0)['sub_lord'])->toBe('Venus');
    expect(SubLord::forLongitude(3.5)['sub_lord'])->toBe('Sun');
    expect(SubLord::forLongitude(13.0)['sub_lord'])->toBe('Mercury');

    $result = SubLord::forLongitude(1.0);
    expect($result['nakshatra'])->toBe('Ashwini');
    expect($result['nakshatra_lord'])->toBe('Ketu');
    expect($result['pada'])->toBe(1); // 1 degree is within the first 3°20' pada.
});

test('forLongitude() correctly crosses into the next nakshatra\'s own sub-lord cycle', function () {
    // 13.5 degrees is just past Ashwini (0-13.3333) into Bharani
    // (13.3333-26.6667), ruled by Venus — the first sub-lord of any
    // nakshatra always matches the nakshatra's own lord.
    $result = SubLord::forLongitude(13.5);

    expect($result['nakshatra'])->toBe('Bharani');
    expect($result['nakshatra_lord'])->toBe('Venus');
    expect($result['sub_lord'])->toBe('Venus');
});
