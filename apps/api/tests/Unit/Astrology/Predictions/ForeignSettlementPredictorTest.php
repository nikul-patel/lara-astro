<?php

use App\Services\Astrology\Predictions\ForeignSettlementPredictor;
use Tests\Support\YogaChartFixture;

function foreignYoga(string $key, array $planets = [], array $houses = []): array
{
    return ['key' => $key, 'name' => 'Foreign Settlement Yoga', 'category' => 'foreign_settlement', 'planets' => $planets, 'houses' => $houses, 'description' => '...'];
}

test('a Rahu-in-12th yoga takes priority over every other signal', function () {
    $houses = YogaChartFixture::houses('Aries');
    $yogas = [foreignYoga('foreign_settlement_moon_12th'), foreignYoga('foreign_settlement_rahu_12th')];

    expect(ForeignSettlementPredictor::predict($houses, $yogas)['key'])->toBe('rahu_12th');
});

test('a 9th/12th lord link yoga renders both lord names into the template', function () {
    $houses = YogaChartFixture::houses('Aries');
    $yogas = [foreignYoga('foreign_settlement_9th_12th_link', ['Saturn', 'Mars'], [9, 12])];

    $prediction = ForeignSettlementPredictor::predict($houses, $yogas);

    expect($prediction['key'])->toBe('ninth_twelfth_link')
        ->and($prediction['text'])->toContain('Saturn')->toContain('Mars');
});

test('without any foreign-settlement yoga, a strong 12th lord produces the moderate template', function () {
    // Ascendant Aries: 12th house is Pisces, lord Jupiter, own sign here (Sagittarius, house 9).
    $houses = YogaChartFixture::houses('Aries', ['Jupiter' => 9]);

    expect(ForeignSettlementPredictor::predict($houses, [])['key'])->toBe('moderate');
});

test('with no yoga and no strong 12th lord, the default template is used', function () {
    $houses = YogaChartFixture::houses('Aries', ['Jupiter' => 2]); // Taurus: neither exalted nor own sign for Jupiter

    expect(ForeignSettlementPredictor::predict($houses, [])['key'])->toBe('default');
});
