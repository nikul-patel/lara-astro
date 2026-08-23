<?php

use App\Services\Astrology\HouseLords;

test('lordOfHouse returns the ruler of the sign occupying that house', function () {
    $houses = [
        ['number' => 1, 'sign' => 'Leo', 'planets' => []],
        ['number' => 2, 'sign' => 'Virgo', 'planets' => ['Mercury']],
    ];

    expect(HouseLords::lordOfHouse(1, $houses))->toBe('Sun')
        ->and(HouseLords::lordOfHouse(2, $houses))->toBe('Mercury');
});

test('houseContainingPlanet finds the house listing a planet, or null if absent', function () {
    $houses = [
        ['number' => 1, 'sign' => 'Leo', 'planets' => []],
        ['number' => 2, 'sign' => 'Virgo', 'planets' => ['Mercury', 'Venus']],
    ];

    expect(HouseLords::houseContainingPlanet('Venus', $houses))->toBe(2)
        ->and(HouseLords::houseContainingPlanet('Mars', $houses))->toBeNull();
});

test('signOfPlanet returns the sign of the house a planet is placed in', function () {
    $houses = [
        ['number' => 1, 'sign' => 'Leo', 'planets' => []],
        ['number' => 2, 'sign' => 'Virgo', 'planets' => ['Mercury']],
    ];

    expect(HouseLords::signOfPlanet('Mercury', $houses))->toBe('Virgo')
        ->and(HouseLords::signOfPlanet('Mars', $houses))->toBeNull();
});

test('every sign has exactly one ruler and every ruler owns 1 or 2 signs', function () {
    expect(HouseLords::SIGN_RULERS)->toHaveCount(12);

    $ownedCounts = array_count_values(HouseLords::SIGN_RULERS);
    foreach ($ownedCounts as $planet => $count) {
        expect(in_array($count, [1, 2], true))->toBeTrue();
    }
});
