<?php

use App\Services\Astrology\Predictions\CareerPredictor;
use Tests\Support\YogaChartFixture;

test('a yoga touching the 10th house takes priority over dignity', function () {
    // Ascendant Aries: 10th house is Capricorn, lord Saturn, placed here
    // in Libra (7th house) -- which would otherwise read as exalted.
    $houses = YogaChartFixture::houses('Aries', ['Saturn' => 7]);
    $yogas = [[
        'key' => 'raj_yoga', 'name' => 'Raj Yoga', 'category' => 'status_and_authority',
        'planets' => ['Saturn', 'Sun'], 'houses' => [10, 5], 'description' => '...',
    ]];

    $prediction = CareerPredictor::predict($houses, $yogas);

    expect($prediction['key'])->toBe('tenth_lord_yoga')
        ->and($prediction['text'])->toContain('Raj Yoga');
});

test('without a relevant yoga, an exalted 10th lord produces the exalted template', function () {
    $houses = YogaChartFixture::houses('Aries', ['Saturn' => 7]); // Libra: Saturn's exaltation sign

    expect(CareerPredictor::predict($houses, [])['key'])->toBe('tenth_lord_exalted');
});

test('a debilitated 10th lord produces the debilitated template even though its house is a kendra', function () {
    // Saturn is debilitated in Aries, which is also the 1st house (a kendra) here.
    $houses = YogaChartFixture::houses('Aries', ['Saturn' => 1]);

    expect(CareerPredictor::predict($houses, [])['key'])->toBe('tenth_lord_debilitated');
});

test('a 10th lord with no strong signal falls back to the default template', function () {
    $houses = YogaChartFixture::houses('Aries', ['Saturn' => 2]); // Taurus, house 2: neutral

    expect(CareerPredictor::predict($houses, [])['key'])->toBe('tenth_lord_default');
});
