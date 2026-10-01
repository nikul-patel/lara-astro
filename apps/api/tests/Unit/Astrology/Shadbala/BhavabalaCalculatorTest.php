<?php

use App\Services\Astrology\Shadbala\BhavabalaCalculator;

/**
 * @param  array<string, int>  $planetHouses
 * @return list<array{number: int, sign: string, planets: list<string>}>
 */
function bhavabalaHousesFixture(array $planetHouses): array
{
    $signs = ['Aries', 'Taurus', 'Gemini', 'Cancer', 'Leo', 'Virgo', 'Libra', 'Scorpio', 'Sagittarius', 'Capricorn', 'Aquarius', 'Pisces'];
    $houses = [];
    for ($number = 1; $number <= 12; $number++) {
        $houses[] = [
            'number' => $number,
            'sign' => $signs[$number - 1],
            'planets' => array_keys(array_filter($planetHouses, fn (int $h) => $h === $number)),
        ];
    }

    return $houses;
}

test('Bhavadhipati Bala is simply the house lord\'s own Shadbala total', function () {
    // Ascendant Aries: house1=Aries (lord Mars), house2=Taurus (lord Venus).
    $houses = bhavabalaHousesFixture(['Sun' => 5, 'Moon' => 6]);
    $shadbalaTotals = ['Sun' => 100.0, 'Moon' => 200.0, 'Mars' => 300.0, 'Mercury' => 400.0, 'Jupiter' => 500.0, 'Venus' => 600.0, 'Saturn' => 700.0];
    $chartLongitudes = ['Sun' => 0.0, 'Moon' => 50.0, 'Mars' => 0.0, 'Mercury' => 0.0, 'Jupiter' => 0.0, 'Venus' => 0.0, 'Saturn' => 0.0];

    $result = BhavabalaCalculator::calculate($shadbalaTotals, $chartLongitudes, $houses);

    expect($result['bhavadhipati'][1])->toBe(300.0); // House 1 (Aries) -> Mars.
    expect($result['bhavadhipati'][2])->toBe(600.0); // House 2 (Taurus) -> Venus.
});

test('Bhava Drishti Bala isolates Saturn\'s 3rd/7th/10th special aspects onto otherwise-untouched houses', function () {
    // Saturn in house 1 aspects houses 3, 7, and 10 (its classical 3rd/10th
    // specials plus the universal 7th). Every other planet is placed so
    // none of ITS aspects (universal 7th, plus Mars's 4th/8th and
    // Jupiter's 5th/9th specials) land on houses 3, 7, or 10 — verified
    // by hand: Sun/Moon/Mercury/Venus in house 2 only reach house 8; Mars
    // in house 2 reaches 5/8/9; Jupiter in house 5 reaches 1/9/11. So
    // houses 3, 7, and 10 isolate Saturn's contribution alone.
    $houses = bhavabalaHousesFixture([
        'Saturn' => 1, 'Sun' => 2, 'Moon' => 2, 'Mercury' => 2, 'Venus' => 2, 'Mars' => 2, 'Jupiter' => 5,
    ]);
    $chartLongitudes = ['Sun' => 0.0, 'Moon' => 50.0, 'Mars' => 0.0, 'Mercury' => 0.0, 'Jupiter' => 0.0, 'Venus' => 0.0, 'Saturn' => 0.0];
    $shadbalaTotals = array_fill_keys(['Sun', 'Moon', 'Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn'], 0.0);

    $result = BhavabalaCalculator::calculate($shadbalaTotals, $chartLongitudes, $houses);

    // Saturn is malefic: each isolated house nets -60 / 4 = -15.
    expect($result['bhava_drishti'][3])->toBe(-15.0);
    expect($result['bhava_drishti'][7])->toBe(-15.0);
    expect($result['bhava_drishti'][10])->toBe(-15.0);
});

test('total_virupas and total_rupas are the sum and /60 of Bhavadhipati + Bhava Drishti', function () {
    $houses = bhavabalaHousesFixture(['Saturn' => 1, 'Sun' => 2, 'Moon' => 2, 'Mercury' => 2, 'Venus' => 2, 'Mars' => 2, 'Jupiter' => 5]);
    $chartLongitudes = ['Sun' => 0.0, 'Moon' => 50.0, 'Mars' => 0.0, 'Mercury' => 0.0, 'Jupiter' => 0.0, 'Venus' => 0.0, 'Saturn' => 0.0];
    $shadbalaTotals = ['Sun' => 100.0, 'Moon' => 100.0, 'Mars' => 100.0, 'Mercury' => 100.0, 'Jupiter' => 100.0, 'Venus' => 100.0, 'Saturn' => 100.0];

    $result = BhavabalaCalculator::calculate($shadbalaTotals, $chartLongitudes, $houses);

    foreach (range(1, 12) as $houseNumber) {
        $expectedTotal = round($result['bhavadhipati'][$houseNumber] + $result['bhava_drishti'][$houseNumber], 2);
        expect($result['total_virupas'][$houseNumber])->toBe($expectedTotal);
        expect($result['total_rupas'][$houseNumber])->toBe(round($expectedTotal / 60, 2));
    }
});
