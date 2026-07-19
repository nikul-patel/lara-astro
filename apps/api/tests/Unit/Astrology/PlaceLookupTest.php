<?php

use App\Services\Astrology\PlaceLookup;

test('resolves a known Indian city, case-insensitively and with extra text', function () {
    $result = PlaceLookup::resolve('Jaipur, Rajasthan, India');

    expect($result)->toMatchArray([
        'state' => 'Rajasthan',
        'country' => 'India',
        'timezone' => 'Asia/Kolkata',
        'matched' => true,
    ]);
});

test('resolves an aliased city name', function () {
    expect(PlaceLookup::resolve('Bombay')['state'])->toBe('Maharashtra');
});

test('falls back to Delhi for an unrecognized place, flagged as unmatched', function () {
    $result = PlaceLookup::resolve('Nowhereville, Atlantis');

    expect($result)->toMatchArray([
        'state' => 'Delhi',
        'country' => 'India',
        'matched' => false,
    ]);
});
