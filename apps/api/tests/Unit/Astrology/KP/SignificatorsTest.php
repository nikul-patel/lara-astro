<?php

use App\Services\Astrology\KP\Significators;

function significatorsPlanetNakshatraLordsFixture(): array
{
    return [
        'Sun' => 'Jupiter', 'Moon' => 'Mars', 'Mars' => 'Saturn', 'Mercury' => 'Venus',
        'Jupiter' => 'Venus', 'Venus' => 'Mercury', 'Saturn' => 'Rahu', 'Rahu' => 'Ketu', 'Ketu' => 'Rahu',
    ];
}

test('forHouses() computes all 4 significator levels and their deduplicated combined union, hand-verified', function () {
    $bhavaMadhya = [
        ['house' => 1, 'sign' => 'Aries', 'planets' => ['Jupiter']],
        ['house' => 2, 'sign' => 'Taurus', 'planets' => ['Mercury']],
    ];

    $result = Significators::forHouses($bhavaMadhya, significatorsPlanetNakshatraLordsFixture());

    // House 1: Aries, owned by Mars, occupied by Jupiter.
    // - Occupant star lords: planets whose nakshatra lord is Jupiter (the occupant) -> Sun.
    // - Owner star lords: planets whose nakshatra lord is Mars (the owner) -> Moon.
    expect($result[1]['occupants'])->toBe(['Jupiter']);
    expect($result[1]['owner'])->toBe('Mars');
    expect($result[1]['occupant_star_lords'])->toBe(['Sun']);
    expect($result[1]['owner_star_lords'])->toBe(['Moon']);
    expect($result[1]['combined'])->toBe(['Sun', 'Jupiter', 'Moon', 'Mars']);

    // House 2: Taurus, owned by Venus, occupied by Mercury.
    // - Occupant star lords: planets whose nakshatra lord is Mercury (the occupant) -> Venus.
    // - Owner star lords: planets whose nakshatra lord is Venus (the owner) -> Mercury, Jupiter.
    // Mercury appears at BOTH the "occupant" level and the "owner star lord"
    // level here — combined must list it only once, at the stronger
    // (earlier-checked) level it first appeared at.
    expect($result[2]['occupants'])->toBe(['Mercury']);
    expect($result[2]['owner'])->toBe('Venus');
    expect($result[2]['occupant_star_lords'])->toBe(['Venus']);
    expect($result[2]['owner_star_lords'])->toBe(['Mercury', 'Jupiter']);
    expect($result[2]['combined'])->toBe(['Venus', 'Mercury', 'Jupiter']);
});

test('planetSignifications() inverts forHouses() into a per-planet list of signified houses', function () {
    $bhavaMadhya = [
        ['house' => 1, 'sign' => 'Aries', 'planets' => ['Jupiter']],
        ['house' => 2, 'sign' => 'Taurus', 'planets' => ['Mercury']],
    ];
    $houseSignificators = Significators::forHouses($bhavaMadhya, significatorsPlanetNakshatraLordsFixture());

    $byPlanet = Significators::planetSignifications($houseSignificators);

    expect($byPlanet['Sun'])->toBe([1]);
    expect($byPlanet['Moon'])->toBe([1]);
    expect($byPlanet['Mars'])->toBe([1]);
    expect($byPlanet['Jupiter'])->toBe([1, 2]);
    expect($byPlanet['Venus'])->toBe([2]);
    expect($byPlanet['Mercury'])->toBe([2]);
    expect($byPlanet)->not->toHaveKey('Saturn');
});

test('an empty house (no occupants) still has owner-based significators', function () {
    $bhavaMadhya = [['house' => 5, 'sign' => 'Leo', 'planets' => []]];

    $result = Significators::forHouses($bhavaMadhya, significatorsPlanetNakshatraLordsFixture());

    expect($result[5]['occupants'])->toBe([]);
    expect($result[5]['owner'])->toBe('Sun'); // Leo's lord.
    expect($result[5]['occupant_star_lords'])->toBe([]);
    // Owner star lords: planets whose nakshatra lord is Sun -> none in this fixture.
    expect($result[5]['owner_star_lords'])->toBe([]);
    expect($result[5]['combined'])->toBe(['Sun']);
});
