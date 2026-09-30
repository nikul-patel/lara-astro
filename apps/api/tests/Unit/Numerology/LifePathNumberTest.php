<?php

use App\Services\Numerology\LifePathNumber;
use Carbon\CarbonImmutable;

test('a birth date reduces day, month, and year separately before combining', function () {
    // 1990-05-15: day 15 -> 1+5=6; month 5 -> 5; year 1990 -> 1+9+9+0=19 -> 1+9=10 -> 1+0=1.
    // 6 + 5 + 1 = 12 -> 1+2=3.
    expect(LifePathNumber::forDate(CarbonImmutable::parse('1990-05-15')))->toBe(3);
});

test('a simple birth date with no reduction needed at any stage', function () {
    // 2000-01-01: day 1, month 1, year 2000 -> 2+0+0+0=2. 1+1+2=4.
    expect(LifePathNumber::forDate(CarbonImmutable::parse('2000-01-01')))->toBe(4);
});
