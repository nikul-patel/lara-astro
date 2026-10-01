<?php

use App\Services\Astrology\Panchang\Karana;

test('the very first half-tithi is the fixed Kimstughna karana', function () {
    expect(Karana::forLongitudes(sunLongitude: 0.0, moonLongitude: 0.0))->toBe('Kimstughna');
});

test('the movable karanas start with Bava right after Kimstughna', function () {
    expect(Karana::forLongitudes(sunLongitude: 0.0, moonLongitude: 6.0))->toBe('Bava');
});

test('the movable cycle repeats every 7 karanas', function () {
    // Index 1 = Bava, index 8 = Bava again (one full 7-karana cycle later).
    expect(Karana::forLongitudes(sunLongitude: 0.0, moonLongitude: 6.0))
        ->toBe(Karana::forLongitudes(sunLongitude: 0.0, moonLongitude: 48.0));
});

test('the three fixed karanas at the end of the lunar month are Shakuni, Chatushpada, and Naga', function () {
    expect(Karana::forLongitudes(sunLongitude: 0.0, moonLongitude: 342.0))->toBe('Shakuni')
        ->and(Karana::forLongitudes(sunLongitude: 0.0, moonLongitude: 348.0))->toBe('Chatushpada')
        ->and(Karana::forLongitudes(sunLongitude: 0.0, moonLongitude: 354.0))->toBe('Naga');
});
