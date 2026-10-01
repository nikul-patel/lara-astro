<?php

use App\Services\Astrology\Panchang\Vaar;
use Carbon\CarbonImmutable;

test('a known Sunday maps to Ravivar, ruled by the Sun', function () {
    // 2024-01-07 is a Sunday.
    expect(Vaar::forDate(CarbonImmutable::parse('2024-01-07')))
        ->toMatchArray(['name' => 'Ravivar', 'lord' => 'Sun']);
});

test('a known Saturday maps to Shanivar, ruled by Saturn', function () {
    // 2024-01-06 is a Saturday.
    expect(Vaar::forDate(CarbonImmutable::parse('2024-01-06')))
        ->toMatchArray(['name' => 'Shanivar', 'lord' => 'Saturn']);
});
