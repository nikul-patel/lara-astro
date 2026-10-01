<?php

use App\Services\Astrology\Shadbala\SthanaBala;

function sthanaChartLongitudesFixture(): array
{
    return [
        'Sun' => 10.0, 'Moon' => 100.0, 'Mars' => 65.0, 'Mercury' => 200.0,
        'Jupiter' => 280.0, 'Venus' => 300.0, 'Saturn' => 350.0,
    ];
}

test('Uchcha Bala scores a full 60 at a planet\'s exact deep exaltation point', function () {
    // Sun's deep exaltation is 10 Aries = longitude 10.0.
    $chart = sthanaChartLongitudesFixture();
    $chart['Sun'] = 10.0;

    expect(SthanaBala::uchchaBala($chart)['Sun'])->toBe(60.0);
});

test('Uchcha Bala scores 0 at a planet\'s exact deep debilitation point', function () {
    // Sun's deep debilitation is 10 Libra = longitude 190.0 (180 degrees from exaltation).
    $chart = sthanaChartLongitudesFixture();
    $chart['Sun'] = 190.0;

    expect(SthanaBala::uchchaBala($chart)['Sun'])->toBe(0.0);
});

test('Uchcha Bala scales linearly between the exaltation and debilitation points', function () {
    // Sun's debilitation point is longitude 190.0; 90 degrees from it (280.0) is halfway to exaltation -> 90/3 = 30.
    $chart = sthanaChartLongitudesFixture();
    $chart['Sun'] = 280.0;

    expect(SthanaBala::uchchaBala($chart)['Sun'])->toBe(30.0);
});

test('Ojayugmarasyamsa Bala awards 15 for each of rasi/navamsa landing in the preferred-gender sign', function () {
    // Mars (prefers odd signs): 5 Aries (odd rasi, +15) -> D9 lands in Taurus (even, +0) => 15.
    $chart = sthanaChartLongitudesFixture();
    $chart['Mars'] = 5.0;

    // Jupiter (prefers odd signs): 0 Leo (odd rasi, +15) -> D9 lands in Aries (odd, +15) => 30.
    $chart['Jupiter'] = 120.0;

    // Venus (prefers even signs): 5 Taurus (even rasi, +15) -> D9 lands in Aquarius (odd, +0) => 15.
    $chart['Venus'] = 35.0;

    $result = SthanaBala::ojayugmarasyamsaBala($chart);

    expect($result['Mars'])->toBe(15.0);
    expect($result['Jupiter'])->toBe(30.0);
    expect($result['Venus'])->toBe(15.0);
});

test('Kendradi Bala scores 60 in a Kendra, 30 in a Panaphara, 15 in an Apoklima', function () {
    $signs = ['Aries', 'Taurus', 'Gemini', 'Cancer', 'Leo', 'Virgo', 'Libra', 'Scorpio', 'Sagittarius', 'Capricorn', 'Aquarius', 'Pisces'];
    $planetHouses = ['Sun' => 1, 'Moon' => 2, 'Mars' => 3, 'Mercury' => 4, 'Jupiter' => 5, 'Venus' => 6, 'Saturn' => 9];
    $houses = [];
    for ($number = 1; $number <= 12; $number++) {
        $houses[] = ['number' => $number, 'sign' => $signs[$number - 1], 'planets' => array_keys(array_filter($planetHouses, fn (int $h) => $h === $number))];
    }

    $result = SthanaBala::kendradiBala($houses);

    expect($result['Sun'])->toBe(60.0);   // House 1: Kendra.
    expect($result['Moon'])->toBe(30.0);  // House 2: Panaphara.
    expect($result['Mars'])->toBe(15.0);  // House 3: Apoklima.
    expect($result['Mercury'])->toBe(60.0); // House 4: Kendra.
    expect($result['Jupiter'])->toBe(30.0); // House 5: Panaphara.
    expect($result['Saturn'])->toBe(15.0);  // House 9: Apoklima.
});

test('Drekkana Bala scores 15 only when a planet sits in its own gender\'s decan of its sign', function () {
    $chart = sthanaChartLongitudesFixture();
    $chart['Sun'] = 5.0;    // Masculine, 1st decan (0-10) -> match.
    $chart['Moon'] = 100.0; // 10 Cancer -> degree-in-sign 10, 2nd decan -> feminine match.
    $chart['Mercury'] = 205.0; // 25 Scorpio -> degree-in-sign 25, 3rd decan -> neuter match.
    $chart['Saturn'] = 352.0; // 22 Pisces -> degree-in-sign 22, 3rd decan -> neuter match.
    $chart['Mars'] = 15.0; // 2nd decan -> mismatch (Mars is masculine, wants 1st decan).

    $result = SthanaBala::drekkanaBala($chart);

    expect($result['Sun'])->toBe(15.0);
    expect($result['Moon'])->toBe(15.0);
    expect($result['Mercury'])->toBe(15.0);
    expect($result['Saturn'])->toBe(15.0);
    expect($result['Mars'])->toBe(0.0);
});

test('Saptavargaja Bala sums dignity across all 7 vargas, hand-verified sign-by-sign', function () {
    // Sun at 25 Leo (own sign, not Moolatrikona which is only 0-20 Leo).
    // Every other classical planet also placed in Leo, so every varga
    // lord's temporal relationship to Sun is offset(Leo,Leo)=1 -> "enemy"
    // (not one of TEMPORAL_FRIEND_OFFSETS) for every single varga below.
    $chart = ['Sun' => 145.0, 'Moon' => 125.0, 'Mars' => 125.0, 'Mercury' => 125.0, 'Jupiter' => 125.0, 'Venus' => 125.0, 'Saturn' => 125.0];
    $rasiSigns = ['Sun' => 'Leo', 'Moon' => 'Leo', 'Mars' => 'Leo', 'Mercury' => 'Leo', 'Jupiter' => 'Leo', 'Venus' => 'Leo', 'Saturn' => 'Leo'];

    // Hand-verified varga signs for Sun at 25 Leo, and each contribution:
    // D1  Leo       -> own sign                                   -> 30
    // D2  Cancer     -> lord Moon,  natural friend,  temporal enemy -> neutral  -> 7.5
    // D3  Aries      -> lord Mars,  natural friend,  temporal enemy -> neutral  -> 7.5
    // D7  Capricorn  -> lord Saturn, natural enemy,  temporal enemy -> great_enemy -> 1.875
    // D9  Scorpio    -> lord Mars,  natural friend,  temporal enemy -> neutral  -> 7.5
    // D12 Gemini     -> lord Mercury, natural neutral, temporal enemy -> enemy -> 3.75
    // D30 Libra      -> lord Venus, natural enemy,   temporal enemy -> great_enemy -> 1.875
    // Total: 30 + 7.5 + 7.5 + 1.875 + 7.5 + 3.75 + 1.875 = 60.0
    expect(SthanaBala::saptavargajaBala($chart, $rasiSigns)['Sun'])->toBe(60.0);
});

test('calculate() sums all five sub-components into a per-planet total', function () {
    $chart = ['Sun' => 10.0, 'Moon' => 100.0, 'Mars' => 65.0, 'Mercury' => 200.0, 'Jupiter' => 280.0, 'Venus' => 300.0, 'Saturn' => 350.0];
    $rasiSigns = ['Sun' => 'Aries', 'Moon' => 'Cancer', 'Mars' => 'Gemini', 'Mercury' => 'Sagittarius', 'Jupiter' => 'Capricorn', 'Venus' => 'Capricorn', 'Saturn' => 'Pisces'];
    $signs = ['Aries', 'Taurus', 'Gemini', 'Cancer', 'Leo', 'Virgo', 'Libra', 'Scorpio', 'Sagittarius', 'Capricorn', 'Aquarius', 'Pisces'];
    $houses = [];
    for ($number = 1; $number <= 12; $number++) {
        $houses[] = ['number' => $number, 'sign' => $signs[$number - 1], 'planets' => []];
    }

    $result = SthanaBala::calculate($chart, $rasiSigns, $houses);

    foreach (['Sun', 'Moon', 'Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn'] as $planet) {
        $expectedTotal = round(
            $result['uchcha'][$planet] + $result['saptavargaja'][$planet] + $result['ojayugmarasyamsa'][$planet]
            + $result['kendradi'][$planet] + $result['drekkana'][$planet],
            2
        );
        expect($result['total'][$planet])->toBe($expectedTotal);
    }
});
