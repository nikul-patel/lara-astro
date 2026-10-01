<?php

use App\Services\Astrology\JulianDay;
use App\Services\Astrology\Shadbala\ChestaBala;
use Carbon\CarbonImmutable;

test('a planet in a well-documented historical retrograde window scores the maximum 60', function () {
    // Mars was retrograde from 2022-10-30 to 2023-01-12 (a well-known,
    // independently verifiable historical fact, not derived from this
    // engine) — mid-way through that window, apparent speed must be
    // negative and Chesta Bala must read the maximum.
    $julianDay = JulianDay::fromUtc(CarbonImmutable::parse('2022-12-01 00:00:00', 'UTC'));

    expect(ChestaBala::calculate($julianDay)['Mars'])->toBe(60.0);
});

test('a planet in direct motion scores strictly less than the retrograde maximum', function () {
    // Mars was direct (not near any station) on 2023-06-01, well outside
    // its 2022-2023 retrograde window.
    $julianDay = JulianDay::fromUtc(CarbonImmutable::parse('2023-06-01 00:00:00', 'UTC'));

    $marsBala = ChestaBala::calculate($julianDay)['Mars'];

    expect($marsBala)->toBeGreaterThanOrEqual(0.0);
    expect($marsBala)->toBeLessThan(60.0);
});

test('only the 5 star planets are scored — Sun and Moon never appear', function () {
    $julianDay = JulianDay::fromUtc(CarbonImmutable::parse('2024-01-01 00:00:00', 'UTC'));

    $result = ChestaBala::calculate($julianDay);

    expect($result)->not->toHaveKey('Sun');
    expect($result)->not->toHaveKey('Moon');
    expect(array_keys($result))->toEqualCanonicalizing(['Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn']);
});
