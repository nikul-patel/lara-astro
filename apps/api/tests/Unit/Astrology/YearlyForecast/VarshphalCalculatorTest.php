<?php

use App\Services\Astrology\ChartAssembler;
use App\Services\Astrology\JulianDay;
use App\Services\Astrology\YearlyForecast\VarshphalCalculator;
use App\Services\Astrology\ZodiacSigns;
use Carbon\CarbonImmutable;

/**
 * Builds a natal chart shape directly via ChartAssembler (bypassing
 * BirthChartCalculator, which needs Setting::current() and therefore a
 * database) so this stays a true, DB-free unit test.
 */
function jaipurNatalChart(): array
{
    $julianDay = JulianDay::fromUtc(CarbonImmutable::parse('1994-05-12 14:30:00', 'Asia/Kolkata')->utc());
    $assembled = ChartAssembler::assemble($julianDay, 26.9124, 75.7873, 'vedic');

    return ['houses' => $assembled['houses'], 'planetary_positions' => $assembled['planetary_positions']];
}

test('produces a return chart, Muntha, Varshesh, and 8 Sahams for the requested year', function () {
    $natalChart = jaipurNatalChart();
    $input = ['dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India'];

    $forecast = VarshphalCalculator::forYear($natalChart, $input, 2026);

    expect($forecast['year'])->toBe(2026)
        ->and(CarbonImmutable::parse($forecast['solar_return_moment'])->year)->toBe(2026)
        ->and($forecast['houses'])->toHaveCount(12)
        ->and($forecast['planetary_positions'])->toHaveCount(9)
        ->and(ZodiacSigns::NAMES)->toContain($forecast['muntha']['sign'])
        ->and($forecast['varshesh']['candidates'])->toContain($forecast['varshesh']['lord'])
        ->and($forecast['sahams'])->toHaveCount(8)
        ->and($forecast['is_day_birth'])->toBeBool();
});

test('the Muntha advances by exactly the number of completed years since birth', function () {
    $natalChart = jaipurNatalChart();
    $input = ['dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India'];
    $natalAscendantSign = $natalChart['houses'][0]['sign'];
    $natalIndex = array_search($natalAscendantSign, ZodiacSigns::NAMES, true);

    $forecast = VarshphalCalculator::forYear($natalChart, $input, 2026);

    $completedYears = 2026 - 1994;
    $expectedIndex = ($natalIndex + $completedYears) % 12;

    expect($forecast['muntha']['sign'])->toBe(ZodiacSigns::NAMES[$expectedIndex]);
});

test('two different requested years produce two different solar return moments', function () {
    $natalChart = jaipurNatalChart();
    $input = ['dob' => '1994-05-12', 'time' => '14:30', 'place' => 'Jaipur, India'];

    $forecast2026 = VarshphalCalculator::forYear($natalChart, $input, 2026);
    $forecast2027 = VarshphalCalculator::forYear($natalChart, $input, 2027);

    expect($forecast2026['solar_return_moment'])->not->toBe($forecast2027['solar_return_moment']);
});
