<?php

use App\Services\Astrology\AfflictionDetector;
use Tests\Support\YogaChartFixture;

test('a debilitated planet is flagged as afflicted', function () {
    $houses = YogaChartFixture::houses('Aries', ['Saturn' => 1]); // Aries: Saturn's debilitation sign

    expect(AfflictionDetector::afflictions('Saturn', $houses, []))->toContain('debilitated');
});

test('a planet within combustion orb of the Sun is flagged as afflicted', function () {
    $houses = YogaChartFixture::houses('Aries', ['Sun' => 1, 'Mercury' => 1]);
    $longitudes = ['Sun' => 10.0, 'Mercury' => 15.0]; // 5 degrees apart, within Mercury's 14 degree orb

    expect(AfflictionDetector::afflictions('Mercury', $houses, $longitudes))->toContain('combust');
});

test('a planet placed in a natural enemy\'s sign is flagged as afflicted', function () {
    // The Sun's natural enemies are Venus and Saturn; Libra is ruled by Venus.
    $houses = YogaChartFixture::houses('Aries', ['Sun' => 7]); // Libra

    expect(AfflictionDetector::afflictions('Sun', $houses, []))->toContain('enemy_sign');
});

test('a planet aspected by a malefic is flagged as afflicted', function () {
    // Saturn's default 7th-house aspect: Saturn in house 1 aspects house 7.
    $houses = YogaChartFixture::houses('Aries', ['Saturn' => 1, 'Mercury' => 7]);

    expect(AfflictionDetector::afflictions('Mercury', $houses, []))->toContain('aspected_by_malefic');
});

test('a malefic aspecting itself is not counted as self-affliction', function () {
    $houses = YogaChartFixture::houses('Aries', ['Saturn' => 1]);

    expect(AfflictionDetector::afflictions('Saturn', $houses, []))->not->toContain('aspected_by_malefic');
});

test('a well-placed planet with no affliction conditions met is not flagged', function () {
    $houses = YogaChartFixture::houses('Aries', ['Jupiter' => 9]); // Sagittarius: Jupiter's own sign, no malefic aspecting it

    expect(AfflictionDetector::isAfflicted('Jupiter', $houses, []))->toBeFalse();
});
