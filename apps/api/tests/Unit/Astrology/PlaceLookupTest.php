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

test('resolves cities added for the frontend\'s place suggestions', function () {
    expect(PlaceLookup::resolve('Vadodara, Gujarat, India'))->toMatchArray(['state' => 'Gujarat', 'matched' => true])
        ->and(PlaceLookup::resolve('Rajkot, Gujarat, India'))->toMatchArray(['state' => 'Gujarat', 'matched' => true]);
});

test('does not match a same-named place in a different country', function () {
    // Neither has an entry in the gazetteer, so both should fall through
    // to the documented "unmatched" default rather than being mistaken for
    // the unrelated same-named Indian/UK cities.
    expect(PlaceLookup::resolve('London, Ontario, Canada')['matched'])->toBeFalse()
        ->and(PlaceLookup::resolve('Delhi, Louisiana, USA')['matched'])->toBeFalse();
});

test('still matches the real London and Delhi without a conflicting qualifier', function () {
    expect(PlaceLookup::resolve('London, United Kingdom'))->toMatchArray(['country' => 'United Kingdom', 'matched' => true])
        ->and(PlaceLookup::resolve('New Delhi, India'))->toMatchArray(['state' => 'Delhi', 'matched' => true]);
});
