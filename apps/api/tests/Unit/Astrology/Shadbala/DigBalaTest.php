<?php

use App\Services\Astrology\Shadbala\DigBala;

/**
 * One planet per house, in house-number order, so HouseLords::houseContainingPlanet()
 * finds each planet at a known, controllable house.
 *
 * @param  array<string, int>  $planetHouses
 * @return list<array{number: int, sign: string, planets: list<string>}>
 */
function digBalaHousesFixture(array $planetHouses): array
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

test('a planet at its own maximum-strength house scores a full 60', function () {
    $houses = digBalaHousesFixture(['Sun' => 10, 'Mars' => 10, 'Moon' => 4, 'Venus' => 4, 'Jupiter' => 1, 'Mercury' => 1, 'Saturn' => 7]);

    expect(DigBala::calculate($houses))->toBe([
        'Sun' => 60.0, 'Mars' => 60.0, 'Moon' => 60.0, 'Venus' => 60.0,
        'Jupiter' => 60.0, 'Mercury' => 60.0, 'Saturn' => 60.0,
    ]);
});

test('a planet at the house opposite its maximum-strength house scores 0', function () {
    // Sun's max is house 10; house 4 is 6 steps away (the opposite house).
    $houses = digBalaHousesFixture(['Sun' => 4, 'Mars' => 4, 'Moon' => 10, 'Venus' => 10, 'Jupiter' => 7, 'Mercury' => 7, 'Saturn' => 1]);

    expect(DigBala::calculate($houses))->toBe([
        'Sun' => 0.0, 'Mars' => 0.0, 'Moon' => 0.0, 'Venus' => 0.0,
        'Jupiter' => 0.0, 'Mercury' => 0.0, 'Saturn' => 0.0,
    ]);
});

test('scales linearly with house-step distance from the maximum', function () {
    // Sun's max house is 10; house 1 is 3 steps away (min(|1-10|, 12-9)=3) -> 60*(1-3/6)=30.
    $houses = digBalaHousesFixture(['Sun' => 1]);

    expect(DigBala::calculate($houses)['Sun'])->toBe(30.0);
});
