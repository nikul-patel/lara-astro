<?php

use App\Services\Astrology\AvkahadaChakra;

test('exposes the already-verified Varna/Yoni/Gana/Vashya/Nadi attributes standalone', function () {
    $nakshatra = ['index' => 6, 'name' => 'Punarvasu'];

    $result = AvkahadaChakra::forChart($nakshatra, 'Cancer', 'Gemini');

    expect($result['varna'])->toBe('Brahmin')     // Cancer is a Brahmin-varna sign
        ->and($result['yoni'])->toBe('Cat')        // Punarvasu's yoni is Cat
        ->and($result['gana'])->toBe('Deva')       // Punarvasu is a Deva-gana nakshatra
        ->and($result['vashya'])->toBe('Jalachar') // Cancer is a Jalachar-vashya sign
        ->and($result['nadi'])->toBe('Aadi');      // Punarvasu is an Aadi-nadi nakshatra
});

test('good planets and friendly signs derive from the Lagna lord\'s natural friendship', function () {
    $nakshatra = ['index' => 6, 'name' => 'Punarvasu'];

    // Gemini ascendant -> Lagna lord Mercury -> Mercury's natural friends are Sun and Venus.
    $result = AvkahadaChakra::forChart($nakshatra, 'Cancer', 'Gemini');

    expect($result['good_planets'])->toBe(['Sun', 'Venus']);

    // Sun rules Leo; Venus rules Taurus and Libra.
    expect($result['friendly_signs'])->toBe(['Leo', 'Libra', 'Taurus']);
});

test('lucky stone and day come from the Lagna lord\'s own remedy table', function () {
    $nakshatra = ['index' => 6, 'name' => 'Punarvasu'];

    $result = AvkahadaChakra::forChart($nakshatra, 'Cancer', 'Gemini');

    expect($result['lucky_stone'])->toBe('Emerald')   // Mercury's gemstone
        ->and($result['lucky_day'])->toBe('Wednesday'); // Mercury's day
});
