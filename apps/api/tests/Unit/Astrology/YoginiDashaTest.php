<?php

use App\Services\Astrology\YoginiDasha;
use Carbon\CarbonImmutable;

test('the lord-years table has the fixed 1-8 durations summing to 36', function () {
    expect(array_values(YoginiDasha::LORD_YEARS))->toBe([1, 2, 3, 4, 5, 6, 7, 8])
        ->and(array_sum(YoginiDasha::LORD_YEARS))->toBe(36);
});

test('Anuradha (nakshatra #17) starts in Bhramari, cross-checked against a real third-party report', function () {
    // A real chart (Moon in Anuradha) from an AstroSage-generated PDF report
    // begins its Yogini Dasha in Bhramari — not asserted against our own
    // implementation's output, but against that external, independently
    // computed value.
    expect(YoginiDasha::startingLord(17))->toBe('Bhramari');
});

test('starting lord formula: (nakshatra number + 3) mod 8, treating 0 as 8', function () {
    expect(YoginiDasha::startingLord(1))->toBe('Bhramari') // (1+3)=4
        ->and(YoginiDasha::startingLord(5))->toBe('Sankata') // (5+3)=8 -> treated as 8
        ->and(YoginiDasha::startingLord(8))->toBe('Dhanya') // (8+3)=11, 11 mod 8=3
        ->and(YoginiDasha::startingLord(27))->toBe('Ulka'); // (27+3)=30, 30 mod 8=6
});

test('the timeline starts at the Anuradha-derived Bhramari lord with the correct balance at birth', function () {
    // Moon at 226.5 sidereal degrees: Anuradha spans 213°20'-226°40'
    // (13°20' wide), so this longitude sits 13.1667° into it ->
    // fractionElapsed = 13.1667/13.3333 = 0.9875, leaving 1.25% of
    // Bhramari's 4-year period, i.e. 4 * 0.0125 * 365.25 ≈ 18.26 days.
    $birth = CarbonImmutable::parse('2000-01-01');
    $timeline = YoginiDasha::timeline(226.5, $birth);

    expect($timeline[0]['lord'])->toBe('Bhramari')
        ->and($timeline[0]['start'])->toBe('2000-01-01');

    $firstPeriodDays = $birth->diffInDays(CarbonImmutable::parse($timeline[0]['end']));
    expect($firstPeriodDays)->toBeGreaterThanOrEqual(17)->toBeLessThanOrEqual(19);
});

test('antardasha sub-periods sum to exactly their parent mahadasha\'s span', function () {
    $timeline = YoginiDasha::timeline(10.0, CarbonImmutable::parse('2000-01-01'));
    $secondMahadasha = $timeline[1]; // first full (non-birth-truncated) period

    $parentDays = CarbonImmutable::parse($secondMahadasha['start'])
        ->diffInDays(CarbonImmutable::parse($secondMahadasha['end']));

    $antardashaDaysSum = 0;
    foreach ($secondMahadasha['antardashas'] as $antardasha) {
        $antardashaDaysSum += CarbonImmutable::parse($antardasha['start'])
            ->diffInDays(CarbonImmutable::parse($antardasha['end']));
    }

    expect($antardashaDaysSum)->toBe($parentDays)
        ->and($secondMahadasha['antardashas'])->toHaveCount(8);
});

test('the timeline repeats the 8-Yogini cycle across the requested span rather than stopping after one pass', function () {
    $timeline = YoginiDasha::timeline(10.0, CarbonImmutable::parse('2000-01-01'), spanYears: 100);

    // One cycle is 36 years (8 periods); a 100-year span must cover at
    // least 2 full cycles (16+ periods), unlike Vimshottari's single
    // 120-year pass which never needs to repeat.
    expect(count($timeline))->toBeGreaterThanOrEqual(16);

    $lastEnd = CarbonImmutable::parse(end($timeline)['end']);
    expect(CarbonImmutable::parse('2000-01-01')->diffInYears($lastEnd))->toBeGreaterThanOrEqual(100);
});
