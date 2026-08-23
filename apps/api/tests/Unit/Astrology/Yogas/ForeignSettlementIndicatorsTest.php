<?php

use App\Services\Astrology\Yogas\ForeignSettlementIndicators;
use Tests\Support\YogaChartFixture;

test('Rahu in the 12th house is flagged as a foreign settlement indicator', function () {
    $houses = YogaChartFixture::houses('Aries', ['Rahu' => 12]);

    $keys = array_column(ForeignSettlementIndicators::detect($houses), 'key');

    expect($keys)->toContain('foreign_settlement_rahu_12th');
});

test('the Moon in the 12th house is flagged as a foreign settlement indicator', function () {
    $houses = YogaChartFixture::houses('Aries', ['Moon' => 12]);

    $keys = array_column(ForeignSettlementIndicators::detect($houses), 'key');

    expect($keys)->toContain('foreign_settlement_moon_12th');
});

test('a 12th lord placed in a kendra/trikona house strengthens foreign settlement prospects', function () {
    // Ascendant Aries: 12th house (Pisces) lord is Jupiter. Placing it in
    // the 9th house (a trikona) is a "strong" placement.
    $houses = YogaChartFixture::houses('Aries', ['Jupiter' => 9]);

    $found = ForeignSettlementIndicators::detect($houses);

    expect(array_column($found, 'key'))->toContain('foreign_settlement_12th_lord_strong');
});

test('a conjunct 9th and 12th lord (with different rulers) is flagged as a strong foreign combination', function () {
    // Ascendant Taurus: 9th house (Capricorn) lord Saturn, 12th house
    // (Aries) lord Mars — different rulers, placed together.
    $houses = YogaChartFixture::houses('Taurus', ['Saturn' => 3, 'Mars' => 3]);

    $found = ForeignSettlementIndicators::detect($houses);
    $link = collect($found)->firstWhere('key', 'foreign_settlement_9th_12th_link');

    expect($link)->not->toBeNull()
        ->and($link['description'])->toContain('conjunct');
});

test('a 9th/12th lord exchange (parivartana) is flagged as a strong foreign combination', function () {
    // Same Taurus ascendant, but Saturn and Mars swap into each other's houses.
    $houses = YogaChartFixture::houses('Taurus', ['Saturn' => 12, 'Mars' => 9]);

    $found = ForeignSettlementIndicators::detect($houses);
    $link = collect($found)->firstWhere('key', 'foreign_settlement_9th_12th_link');

    expect($link)->not->toBeNull()
        ->and($link['description'])->toContain('mutual exchange');
});

test('an unremarkable chart with no relevant placements produces no indicators', function () {
    $houses = YogaChartFixture::houses('Aries');

    expect(ForeignSettlementIndicators::detect($houses))->toBe([]);
});
