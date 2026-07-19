<?php

use App\Services\Astrology\Houses;
use App\Services\Astrology\JulianDay;
use App\Services\Astrology\ZodiacSigns;
use Carbon\CarbonImmutable;

test('the ascendant advances through all 12 signs over 24 hours', function () {
    $signsSeen = [];

    // Sampled every 15 minutes: signs don't rise at a perfectly uniform
    // rate (oblique ascension — some signs take noticeably longer to rise
    // than others at a given latitude), so a coarser sampling interval
    // could alias past a short-duration sign. 15 minutes is well under the
    // shortest a sign's rising duration gets at this latitude.
    for ($minutes = 0; $minutes < 24 * 60; $minutes += 15) {
        $jd = JulianDay::fromUtc(CarbonImmutable::parse('1994-05-12 00:00:00', 'UTC')->addMinutes($minutes));
        $ascendant = Houses::ascendant($jd, latitude: 26.9124, longitude: 75.7873);
        $signsSeen[] = ZodiacSigns::forLongitude($ascendant);
    }

    // A full day rotates the ascendant through the whole zodiac exactly
    // once.
    expect(array_unique($signsSeen))->toHaveCount(12);
});

test('whole-sign houses assign each house the next sign after the ascendant\'s', function () {
    $houses = Houses::wholeSignHouses(ascendantLongitude: 135.0, planetLongitudes: []); // 135° = Leo (Leo starts at 120°)

    expect($houses[0])->toMatchArray(['number' => 1, 'sign' => 'Leo'])
        ->and($houses[1])->toMatchArray(['number' => 2, 'sign' => 'Virgo'])
        ->and($houses[11])->toMatchArray(['number' => 12, 'sign' => 'Cancer']);
});

test('whole-sign houses place each planet in the house matching its sign', function () {
    $houses = Houses::wholeSignHouses(ascendantLongitude: 0.0, planetLongitudes: [
        'Sun' => 15.0,    // Aries -> house 1
        'Moon' => 215.0,  // Scorpio -> house 8
    ]);

    expect($houses[0]['planets'])->toBe(['Sun'])
        ->and($houses[7]['planets'])->toBe(['Moon']);
});
