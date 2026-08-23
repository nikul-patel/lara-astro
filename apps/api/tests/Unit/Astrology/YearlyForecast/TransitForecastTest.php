<?php

use App\Services\Astrology\YearlyForecast\TransitForecast;
use App\Services\Astrology\ZodiacSigns;
use Tests\Support\YogaChartFixture;

function natalChartFixture(string $ascendantSign, string $moonSign, array $dasha = []): array
{
    return [
        'houses' => YogaChartFixture::houses($ascendantSign),
        'planetary_positions' => [['name' => 'Moon', 'sign' => $moonSign]],
        'dasha' => ['mahadasha' => $dasha],
    ];
}

test('jupiter and saturn transit signs are valid zodiac signs with internally consistent house offsets', function () {
    $natalChart = natalChartFixture('Aries', 'Cancer');

    $forecast = TransitForecast::forYear($natalChart, 2026);

    foreach (['jupiter_transit', 'saturn_transit'] as $key) {
        $transit = $forecast[$key];
        expect(ZodiacSigns::NAMES)->toContain($transit['sign']);

        $expectedFromAscendant = ((array_search($transit['sign'], ZodiacSigns::NAMES, true) - array_search('Aries', ZodiacSigns::NAMES, true) + 12) % 12) + 1;
        $expectedFromMoon = ((array_search($transit['sign'], ZodiacSigns::NAMES, true) - array_search('Cancer', ZodiacSigns::NAMES, true) + 12) % 12) + 1;

        expect($transit['house_from_ascendant'])->toBe($expectedFromAscendant)
            ->and($transit['house_from_moon'])->toBe($expectedFromMoon);
    }
});

test('governing_dasha only includes Antardasha periods overlapping the requested year', function () {
    $dasha = [
        [
            'lord' => 'Venus', 'start' => '2020-01-01', 'end' => '2040-01-01',
            'antardashas' => [
                ['lord' => 'Venus', 'start' => '2020-01-01', 'end' => '2024-01-01'],
                ['lord' => 'Sun', 'start' => '2024-01-01', 'end' => '2025-01-01'],
                ['lord' => 'Moon', 'start' => '2025-01-01', 'end' => '2027-01-01'],
                ['lord' => 'Mars', 'start' => '2027-01-01', 'end' => '2029-01-01'],
            ],
        ],
    ];
    $natalChart = natalChartFixture('Aries', 'Cancer', $dasha);

    $forecast = TransitForecast::forYear($natalChart, 2026);

    expect($forecast['governing_dasha'])->toHaveCount(1)
        ->and($forecast['governing_dasha'][0]['antardasha_lord'])->toBe('Moon')
        ->and($forecast['governing_dasha'][0]['mahadasha_lord'])->toBe('Venus');
});

test('a year straddling an Antardasha boundary includes both overlapping periods', function () {
    $dasha = [
        [
            'lord' => 'Venus', 'start' => '2020-01-01', 'end' => '2040-01-01',
            'antardashas' => [
                ['lord' => 'Sun', 'start' => '2025-06-01', 'end' => '2026-06-01'],
                ['lord' => 'Moon', 'start' => '2026-06-01', 'end' => '2028-06-01'],
            ],
        ],
    ];
    $natalChart = natalChartFixture('Aries', 'Cancer', $dasha);

    $forecast = TransitForecast::forYear($natalChart, 2026);

    expect(array_column($forecast['governing_dasha'], 'antardasha_lord'))->toBe(['Sun', 'Moon']);
});
