<?php

use App\Services\Astrology\Ashtakvarga\AshtakvargaCalculator;

function ashtakvargaFixture(): array
{
    // Every contributor (7 planets) placed in Aries, Ascendant also Aries —
    // a degenerate but easy-to-hand-verify case: every contributor's
    // "house from itself" to Aries is 1, so each subject's Aries bindu
    // count is just how many of the 8 contributors list house 1.
    return array_map(
        fn (string $planet) => ['name' => $planet, 'sign' => 'Aries'],
        ['Sun', 'Moon', 'Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn'],
    );
}

test('Sun\'s Aries bindus match a hand count when every contributor sits in Aries', function () {
    $result = AshtakvargaCalculator::calculate(ashtakvargaFixture(), 'Aries');

    // Sun, Mars, and Saturn are the only contributors whose house-1 list
    // includes 1 in Sun's table (verified by inspection of BinduTables).
    expect($result['bhinnashtakavarga']['Sun']['Aries'])->toBe(3);
});

test('Moon\'s Aries bindus match a hand count when every contributor sits in Aries', function () {
    $result = AshtakvargaCalculator::calculate(ashtakvargaFixture(), 'Aries');

    // Moon, Mercury, and Jupiter are the only contributors whose house-1
    // list includes 1 in Moon's table.
    expect($result['bhinnashtakavarga']['Moon']['Aries'])->toBe(3);
});

test('sarvashtakavarga sums to 337 across all 12 signs regardless of chart', function () {
    $result = AshtakvargaCalculator::calculate(ashtakvargaFixture(), 'Aries');

    expect(array_sum($result['sarvashtakavarga']))->toBe(337);
});

test('sarvashtakavarga is the sum of all 7 planets\' bhinnashtakavarga per sign', function () {
    $result = AshtakvargaCalculator::calculate(ashtakvargaFixture(), 'Aries');

    foreach ($result['sarvashtakavarga'] as $sign => $total) {
        $expected = array_sum(array_column($result['bhinnashtakavarga'], $sign));

        expect($total)->toBe($expected);
    }
});
