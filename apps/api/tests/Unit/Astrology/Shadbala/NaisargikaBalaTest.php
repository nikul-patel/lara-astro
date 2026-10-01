<?php

use App\Services\Astrology\Shadbala\NaisargikaBala;

test('matches the classical fixed-rank table exactly', function () {
    // 60*(8-rank)/7 for rank 1 (Sun) through 7 (Saturn) — the standard
    // published values every Shadbala table lists.
    expect(NaisargikaBala::calculate())->toBe([
        'Sun' => 60.0,
        'Moon' => 51.43,
        'Venus' => 42.86,
        'Jupiter' => 34.29,
        'Mercury' => 25.71,
        'Mars' => 17.14,
        'Saturn' => 8.57,
    ]);
});
