<?php

use App\Services\Astrology\Panchang\Tithi;

test('the first Shukla tithi is Pratipada', function () {
    expect(Tithi::forLongitudes(sunLongitude: 0.0, moonLongitude: 6.0))
        ->toMatchArray(['number' => 1, 'name' => 'Pratipada', 'paksha' => 'Shukla']);
});

test('the 15th tithi (Moon 174 deg ahead) is Purnima, still within Shukla', function () {
    expect(Tithi::forLongitudes(sunLongitude: 0.0, moonLongitude: 174.0))
        ->toMatchArray(['number' => 15, 'name' => 'Purnima', 'paksha' => 'Shukla']);
});

test('the 16th tithi (just past full moon) is Pratipada again, now Krishna paksha', function () {
    expect(Tithi::forLongitudes(sunLongitude: 0.0, moonLongitude: 186.0))
        ->toMatchArray(['number' => 16, 'name' => 'Pratipada', 'paksha' => 'Krishna']);
});

test('the 30th tithi (Moon 354 deg ahead) is Amavasya', function () {
    expect(Tithi::forLongitudes(sunLongitude: 0.0, moonLongitude: 354.0))
        ->toMatchArray(['number' => 30, 'name' => 'Amavasya', 'paksha' => 'Krishna']);
});
