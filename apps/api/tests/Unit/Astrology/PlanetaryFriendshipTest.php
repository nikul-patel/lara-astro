<?php

use App\Services\Astrology\PlanetaryFriendship;

test('natural relationship is not always symmetric', function () {
    // Moon counts Mercury a friend, but Mercury counts Moon an enemy.
    expect(PlanetaryFriendship::relationship('Moon', 'Mercury'))->toBe('friend')
        ->and(PlanetaryFriendship::relationship('Mercury', 'Moon'))->toBe('enemy');
});

test('temporal friendship: 2nd/3rd/4th/10th/11th/12th from a planet are friends, everything else including its own sign is an enemy', function () {
    expect(PlanetaryFriendship::temporalRelationship('Aries', 'Gemini'))->toBe('friend') // offset 3
        ->and(PlanetaryFriendship::temporalRelationship('Aries', 'Leo'))->toBe('enemy')   // offset 5
        ->and(PlanetaryFriendship::temporalRelationship('Aries', 'Aries'))->toBe('enemy'); // offset 1, same sign
});

test('Panchadha Maitri combines natural and temporal into the standard 5 levels', function () {
    expect(PlanetaryFriendship::combined('friend', 'friend'))->toBe('great_friend')
        ->and(PlanetaryFriendship::combined('friend', 'enemy'))->toBe('neutral')
        ->and(PlanetaryFriendship::combined('neutral', 'friend'))->toBe('friend')
        ->and(PlanetaryFriendship::combined('neutral', 'enemy'))->toBe('enemy')
        ->and(PlanetaryFriendship::combined('enemy', 'friend'))->toBe('neutral')
        ->and(PlanetaryFriendship::combined('enemy', 'enemy'))->toBe('great_enemy');
});

test('table() produces all 42 ordered pairs with a hand-verified combined value', function () {
    $planetSigns = [
        'Sun' => 'Aries', 'Moon' => 'Gemini', 'Mars' => 'Taurus', 'Mercury' => 'Cancer',
        'Jupiter' => 'Leo', 'Venus' => 'Virgo', 'Saturn' => 'Leo',
    ];

    $table = PlanetaryFriendship::table($planetSigns);

    expect($table)->toHaveCount(42); // 7 planets x 6 others, no self-pairs

    // Sun (Aries) -> Moon (Gemini): natural friend (Sun's FRIENDS includes
    // Moon), temporal offset Aries->Gemini = 3 -> friend -> great_friend.
    $sunToMoon = collect($table)->first(fn ($row) => $row['from'] === 'Sun' && $row['to'] === 'Moon');
    expect($sunToMoon['natural'])->toBe('friend')
        ->and($sunToMoon['temporal'])->toBe('friend')
        ->and($sunToMoon['combined'])->toBe('great_friend');

    // Sun (Aries) -> Saturn (Leo): natural enemy (Sun's ENEMIES includes
    // Saturn), temporal offset Aries->Leo = 5 -> enemy -> great_enemy.
    $sunToSaturn = collect($table)->first(fn ($row) => $row['from'] === 'Sun' && $row['to'] === 'Saturn');
    expect($sunToSaturn['natural'])->toBe('enemy')
        ->and($sunToSaturn['temporal'])->toBe('enemy')
        ->and($sunToSaturn['combined'])->toBe('great_enemy');
});
