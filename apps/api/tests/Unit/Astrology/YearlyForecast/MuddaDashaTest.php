<?php

use App\Services\Astrology\YearlyForecast\MuddaDasha;
use Carbon\CarbonImmutable;

function muddaDashaHousesFixture(): array
{
    $signs = ['Aries', 'Taurus', 'Gemini', 'Cancer', 'Leo', 'Virgo', 'Libra', 'Scorpio', 'Sagittarius', 'Capricorn', 'Aquarius', 'Pisces'];
    $houses = [];
    for ($number = 1; $number <= 12; $number++) {
        $houses[] = ['number' => $number, 'sign' => $signs[$number - 1], 'planets' => []];
    }

    return $houses;
}

test('a full 9-period cycle sums to exactly 365.25 days, each period scaled by the lord\'s classical 120-year share', function () {
    // Moon at longitude 0 -> exactly the start of Ashwini (Ketu-ruled), no balance truncation.
    $returnMoment = CarbonImmutable::parse('2025-05-12 00:00:00', 'UTC');
    $timeline = MuddaDasha::timeline(0.0, $returnMoment, muddaDashaHousesFixture());

    expect($timeline)->toHaveCount(9);
    expect(array_column($timeline, 'lord'))->toBe(['Ketu', 'Venus', 'Sun', 'Moon', 'Mars', 'Rahu', 'Jupiter', 'Saturn', 'Mercury']);

    // 365.25 * lord-years / 120, hand-computed: Ketu 21.30625, Venus
    // 60.875, Sun 18.2625, Moon 30.4375, Mars 21.30625, Rahu 54.7875,
    // Jupiter 48.7, Saturn 57.83125, Mercury 51.74375 — these sum to
    // exactly 365.25 (the full cycle length).
    $expectedDays = [21.30625, 60.875, 18.2625, 30.4375, 21.30625, 54.7875, 48.7, 57.83125, 51.74375];
    foreach ($timeline as $i => $period) {
        $start = CarbonImmutable::parse($period['start']);
        $end = CarbonImmutable::parse($period['end']);
        $actualDays = $start->diffInSeconds($end) / 86400;

        expect(round($actualDays, 2))->toBe(round($expectedDays[$i], 2));
    }

    $cycleStart = CarbonImmutable::parse($timeline[0]['start']);
    $cycleEnd = CarbonImmutable::parse($timeline[8]['end']);
    expect(round($cycleStart->diffInSeconds($cycleEnd) / 86400, 2))->toBe(365.25);
});

test('periods chain with no gaps, and a mid-nakshatra Moon truncates only the first period', function () {
    // Moon at longitude 6.666666 is exactly halfway through Ashwini
    // (0-13.3333, Ketu-ruled) -> the first (Ketu) period should run at
    // half its full length; every later period runs full length.
    $returnMoment = CarbonImmutable::parse('2025-05-12 00:00:00', 'UTC');
    $timeline = MuddaDasha::timeline(6.666666, $returnMoment, muddaDashaHousesFixture());

    $ketuDays = CarbonImmutable::parse($timeline[0]['start'])->diffInSeconds(CarbonImmutable::parse($timeline[0]['end'])) / 86400;
    expect(round($ketuDays, 2))->toBe(round(21.30625 / 2, 2));

    for ($i = 1; $i < 9; $i++) {
        expect($timeline[$i]['start'])->toBe($timeline[$i - 1]['end']);
    }
});

test('each period records the lord\'s actual house placement in the return chart', function () {
    $houses = muddaDashaHousesFixture();
    $houses[6]['planets'] = ['Jupiter']; // House 7.

    $timeline = MuddaDasha::timeline(0.0, CarbonImmutable::parse('2025-05-12'), $houses);

    $jupiterPeriod = collect($timeline)->firstWhere('lord', 'Jupiter');
    expect($jupiterPeriod['house'])->toBe(7);
});
