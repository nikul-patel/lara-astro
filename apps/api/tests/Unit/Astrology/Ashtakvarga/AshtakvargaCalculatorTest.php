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

test('Sun\'s Prastharashtakvarga lists exactly 3 contributors (Sun, Mars, Saturn) granting an Aries bindu', function () {
    // Same hand-verified fact as the bhinnashtakavarga test above, but
    // checking the per-contributor detail Prastharashtakvarga exists to
    // expose rather than just the summed total.
    $result = AshtakvargaCalculator::calculate(ashtakvargaFixture(), 'Aries');

    $ariesBindus = array_map(fn (array $bindus) => $bindus['Aries'], $result['prastharashtakvarga']['Sun']);

    expect(array_filter($ariesBindus))->toHaveCount(3);
    expect($ariesBindus['Sun'])->toBe(1);
    expect($ariesBindus['Mars'])->toBe(1);
    expect($ariesBindus['Saturn'])->toBe(1);
    expect($ariesBindus['Moon'])->toBe(0);
});

test('prastharashtakvarga\'s per-contributor bindus sum to exactly bhinnashtakavarga\'s per-sign totals, for every subject and sign', function () {
    $result = AshtakvargaCalculator::calculate(ashtakvargaFixture(), 'Aries');

    foreach ($result['prastharashtakvarga'] as $subject => $contributorBindus) {
        foreach (array_keys($result['bhinnashtakavarga'][$subject]) as $sign) {
            $summed = array_sum(array_column($contributorBindus, $sign));

            expect($summed)->toBe($result['bhinnashtakavarga'][$subject][$sign]);
        }
    }
});

test('prastharashtakvarga only ever contains 0/1 bindus and exactly the 8 classical contributors per subject', function () {
    $result = AshtakvargaCalculator::calculate(ashtakvargaFixture(), 'Aries');

    foreach ($result['prastharashtakvarga'] as $contributorBindus) {
        expect($contributorBindus)->toHaveCount(8);

        foreach ($contributorBindus as $bindus) {
            foreach ($bindus as $value) {
                expect($value)->toBeIn([0, 1]);
            }
        }
    }
});
