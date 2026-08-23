<?php

use App\Services\Astrology\Predictions\EducationPredictor;
use Tests\Support\YogaChartFixture;

test('an exalted 5th lord produces the exalted template', function () {
    // Ascendant Aries: 5th house is Leo, lord the Sun, exalted in Aries (house 1 here).
    $houses = YogaChartFixture::houses('Aries', ['Sun' => 1]);

    expect(EducationPredictor::predict($houses)['key'])->toBe('fifth_lord_exalted');
});

test('a debilitated 5th lord produces the debilitated template even though its house is a kendra/trikona', function () {
    // The Sun is debilitated in Libra, which is also the 7th house (a trikona-adjacent kendra) here.
    $houses = YogaChartFixture::houses('Aries', ['Sun' => 7]);

    $prediction = EducationPredictor::predict($houses);

    expect($prediction['key'])->toBe('fourth_or_fifth_debilitated')
        ->and($prediction['text'])->toContain('5th');
});

test('a 5th lord in a kendra/trikona without dignity extremes produces the favorable template', function () {
    $houses = YogaChartFixture::houses('Aries', ['Sun' => 4]); // Cancer: neither exalted nor debilitated for the Sun

    expect(EducationPredictor::predict($houses)['key'])->toBe('fifth_lord_kendra_trikona');
});

test('a debilitated 4th lord is checked once the 5th lord is neutral', function () {
    // Sun (5th lord) neutral in Taurus/house 2. Moon (4th lord) debilitated in Scorpio/house 8.
    $houses = YogaChartFixture::houses('Aries', ['Sun' => 2, 'Moon' => 8]);

    $prediction = EducationPredictor::predict($houses);

    expect($prediction['key'])->toBe('fourth_or_fifth_debilitated')
        ->and($prediction['text'])->toContain('4th');
});

test('a 4th lord in a dusthana is checked once every 5th-lord signal is neutral', function () {
    $houses = YogaChartFixture::houses('Aries', ['Sun' => 2, 'Moon' => 12]); // Moon in the 12th (dusthana)

    $prediction = EducationPredictor::predict($houses);

    expect($prediction['key'])->toBe('fourth_or_fifth_dusthana')
        ->and($prediction['text'])->toContain('4th');
});

test('with no strong signal from either lord, the default template is used', function () {
    $houses = YogaChartFixture::houses('Aries', ['Sun' => 2, 'Moon' => 3]);

    expect(EducationPredictor::predict($houses)['key'])->toBe('default');
});
