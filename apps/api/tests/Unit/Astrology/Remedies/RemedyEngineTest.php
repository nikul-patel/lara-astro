<?php

use App\Services\Astrology\Remedies\RemedyEngine;
use Tests\Support\YogaChartFixture;

test('a debilitated planet gets a remedy entry with its planet-specific suggestions', function () {
    $houses = YogaChartFixture::houses('Aries', ['Mars' => 4]); // Cancer: Mars' debilitation sign

    $remedies = RemedyEngine::generate($houses, []);
    $marsRemedy = collect($remedies)->firstWhere('planet', 'Mars');

    expect($marsRemedy)->not->toBeNull()
        ->and($marsRemedy['afflictions'])->toContain('debilitated')
        ->and($marsRemedy['gemstone'])->toBe('Red Coral')
        ->and($marsRemedy['fasting_day'])->toBe('Tuesday')
        ->and($marsRemedy['caution_note'])->toBe(RemedyEngine::CAUTION_NOTE);
});

test('a well-dignified chart with no afflicted planets produces no remedy entries', function () {
    // Every placed planet is in its own sign (so never debilitated or in an
    // enemy sign), and no malefic is placed anywhere in the chart at all
    // (so nothing can be flagged as aspected by one).
    $houses = YogaChartFixture::houses('Aries', [
        'Sun' => 5, 'Moon' => 4, 'Mercury' => 3, 'Jupiter' => 9, 'Venus' => 2,
    ]);

    expect(RemedyEngine::generate($houses, []))->toBe([]);
});

test('every remedy entry carries the same standard caution note', function () {
    $houses = YogaChartFixture::houses('Aries', ['Saturn' => 1]); // debilitated in Aries

    $remedies = RemedyEngine::generate($houses, []);

    foreach ($remedies as $remedy) {
        expect($remedy['caution_note'])->toBe(RemedyEngine::CAUTION_NOTE);
    }
});
