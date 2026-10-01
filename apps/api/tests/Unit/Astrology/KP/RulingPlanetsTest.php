<?php

use App\Services\Astrology\KP\RulingPlanets;

test('compute() composes the 7 ruling planets from sign lords plus already-supplied star/sub lords', function () {
    $ascendantSubLord = ['nakshatra' => 'Ashwini', 'nakshatra_lord' => 'Ketu', 'pada' => 1, 'sub_lord' => 'Venus'];
    $moonSubLord = ['nakshatra' => 'Rohini', 'nakshatra_lord' => 'Moon', 'pada' => 2, 'sub_lord' => 'Mars'];

    $result = RulingPlanets::compute('Jupiter', 'Aries', $ascendantSubLord, 'Taurus', $moonSubLord);

    expect($result)->toBe([
        'day_lord' => 'Jupiter',
        'ascendant_sign_lord' => 'Mars',   // Aries' lord.
        'ascendant_star_lord' => 'Ketu',
        'ascendant_sub_lord' => 'Venus',
        'moon_sign_lord' => 'Venus',       // Taurus' lord.
        'moon_star_lord' => 'Moon',
        'moon_sub_lord' => 'Mars',
    ]);
});
