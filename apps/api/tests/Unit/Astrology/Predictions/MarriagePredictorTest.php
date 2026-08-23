<?php

use App\Services\Astrology\Predictions\MarriagePredictor;
use Tests\Support\YogaChartFixture;

test('an exalted 7th lord produces the exalted template', function () {
    // Ascendant Aries: 7th house is Libra, lord Venus. Venus is exalted in Pisces (12th house here).
    $houses = YogaChartFixture::houses('Aries', ['Venus' => 12]);

    $prediction = MarriagePredictor::predict($houses);

    expect($prediction['key'])->toBe('seventh_lord_exalted')
        ->and($prediction['text'])->toContain('Venus')->toContain('Pisces');
});

test('a debilitated 7th lord produces the debilitated template', function () {
    $houses = YogaChartFixture::houses('Aries', ['Venus' => 6]); // Virgo: Venus' debilitation sign

    expect(MarriagePredictor::predict($houses)['key'])->toBe('seventh_lord_debilitated');
});

test('a 7th lord in a kendra/trikona (without dignity extremes) produces the favorable placement template', function () {
    $houses = YogaChartFixture::houses('Aries', ['Venus' => 1]); // Aries: neither exalted nor debilitated for Venus

    expect(MarriagePredictor::predict($houses)['key'])->toBe('seventh_lord_kendra_trikona');
});

test('a 7th lord in a dusthana produces the obstacles template', function () {
    $houses = YogaChartFixture::houses('Aries', ['Venus' => 8]); // Scorpio

    expect(MarriagePredictor::predict($houses)['key'])->toBe('seventh_lord_dusthana');
});

test('a 7th lord with no strong signal falls back to the default template', function () {
    $houses = YogaChartFixture::houses('Aries', ['Venus' => 2]); // own sign, Taurus, house 2 (neither kendra/trikona nor dusthana)

    expect(MarriagePredictor::predict($houses)['key'])->toBe('seventh_lord_default');
});
