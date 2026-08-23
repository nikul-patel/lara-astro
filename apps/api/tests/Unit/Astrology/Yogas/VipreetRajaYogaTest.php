<?php

use App\Services\Astrology\Yogas\VipreetRajaYoga;
use Tests\Support\YogaChartFixture;

test('a dusthana lord placed in another dusthana house forms Vipreet Raja Yoga', function () {
    // Ascendant Aries: 6th house (Virgo) lord is Mercury. Placing Mercury
    // in the 8th house (another dusthana) triggers Sarala Yoga.
    $houses = YogaChartFixture::houses('Aries', ['Mercury' => 8]);

    $found = VipreetRajaYoga::detect($houses);

    expect($found)->toHaveCount(1)
        ->and($found[0]['name'])->toContain('Harsha')
        ->and($found[0]['planets'])->toBe(['Mercury']);
});

test('a dusthana lord placed outside the dusthanas does not form Vipreet Raja Yoga', function () {
    $houses = YogaChartFixture::houses('Aries', ['Mercury' => 2]); // 6th lord in the 2nd house

    expect(VipreetRajaYoga::detect($houses))->toBe([]);
});
