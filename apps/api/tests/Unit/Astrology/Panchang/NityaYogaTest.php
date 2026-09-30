<?php

use App\Services\Astrology\Panchang\NityaYoga;

test('a Sun+Moon sum of 0 deg gives the first Nitya Yoga, Vishkambha', function () {
    expect(NityaYoga::forLongitudes(sunLongitude: 0.0, moonLongitude: 0.0))
        ->toMatchArray(['index' => 0, 'name' => 'Vishkambha']);
});

test('a Sun+Moon sum of 15 deg falls in the second segment, Priti', function () {
    expect(NityaYoga::forLongitudes(sunLongitude: 10.0, moonLongitude: 5.0))
        ->toMatchArray(['index' => 1, 'name' => 'Priti']);
});

test('the 27th (last) Nitya Yoga is Vaidhriti', function () {
    // 26 * 13.333.. = 346.67 deg, safely inside the 27th segment.
    expect(NityaYoga::forLongitudes(sunLongitude: 350.0, moonLongitude: 0.0))
        ->toMatchArray(['index' => 26, 'name' => 'Vaidhriti']);
});
