<?php

use App\Services\Astrology\Transits\SadeSati;
use Carbon\CarbonImmutable;

function sadeSatiChart(string $moonSign): array
{
    return ['planetary_positions' => [['name' => 'Moon', 'sign' => $moonSign]]];
}

test('regression for #66: cycle_start no longer shifts across different reference_date values within the same window', function () {
    $chart = sadeSatiChart('Taurus');
    $birth = CarbonImmutable::parse('1994-05-12 14:30:00', 'Asia/Kolkata');

    $resultA = SadeSati::forChart($chart, $birth, CarbonImmutable::parse('2010-01-01'));
    $resultB = SadeSati::forChart($chart, $birth, CarbonImmutable::parse('2012-01-01'));
    $resultC = SadeSati::forChart($chart, $birth, CarbonImmutable::parse('2015-06-01'));

    // All three reference dates are not inside a Sade Sati phase and refer
    // to the same underlying lifetime timeline, computed once from birth —
    // the whole point of the fix is that the summary for "the relevant
    // cycle" no longer depends on which of these we ask from.
    expect($resultA['cycle_start'])->toBe($resultB['cycle_start'])
        ->and($resultB['cycle_start'])->toBe($resultC['cycle_start'])
        ->and($resultA['cycle_end'])->toBe($resultC['cycle_end']);
});

test('the lifetime timeline is identical regardless of reference_date, since it no longer drives the search', function () {
    $chart = sadeSatiChart('Cancer');
    $birth = CarbonImmutable::parse('2000-01-01');

    $resultA = SadeSati::forChart($chart, $birth, CarbonImmutable::parse('2005-01-01'));
    $resultB = SadeSati::forChart($chart, $birth, CarbonImmutable::parse('2050-01-01'));

    expect($resultA['lifetime'])->toBe($resultB['lifetime']);
});

test('Panoti signs for a Scorpio Moon are Aquarius (4th) and Gemini (8th), cross-checked against a real third-party report', function () {
    // The same AstroSage PDF report used to validate Yogini Dasha's
    // starting lord (a Scorpio-Moon, Anuradha-nakshatra chart) lists its
    // "Small Panoti" periods under Gemini and Aquarius — not Sun/Moon
    // signs, Saturn transits. This checks our Panoti sign assignment
    // against that same real, independently computed report.
    $chart = sadeSatiChart('Scorpio');
    $birth = CarbonImmutable::parse('1996-06-01 07:15:00', 'Asia/Kolkata');

    $result = SadeSati::forChart($chart, $birth, CarbonImmutable::parse('2003-01-01'));

    $panotiSigns = collect($result['lifetime']['panoti'])->pluck('sign')->unique()->sort()->values();
    expect($panotiSigns->all())->toBe(['Aquarius', 'Gemini']);

    $fourthFromMoon = collect($result['lifetime']['panoti'])->firstWhere('type', 'fourth_from_moon');
    $eighthFromMoon = collect($result['lifetime']['panoti'])->firstWhere('type', 'eighth_from_moon');
    expect($fourthFromMoon['sign'])->toBe('Aquarius')
        ->and($eighthFromMoon['sign'])->toBe('Gemini');
});

test('every sade_sati interval sign matches its phase\'s expected position from the Moon', function () {
    $chart = sadeSatiChart('Leo');
    $birth = CarbonImmutable::parse('1990-01-01');

    $result = SadeSati::forChart($chart, $birth, CarbonImmutable::parse('1990-01-01'));

    foreach ($result['lifetime']['sade_sati'] as $interval) {
        $expectedSign = match ($interval['phase']) {
            'rising' => 'Cancer',   // 12th from Leo
            'peak' => 'Leo',
            'setting' => 'Virgo',   // 2nd from Leo
        };

        expect($interval['sign'])->toBe($expectedSign);
    }

    expect($result['lifetime']['sade_sati'])->not->toBeEmpty();
});

test('a reference_date inside an active phase reports is_active true with that phase', function () {
    $chart = sadeSatiChart('Leo');
    $birth = CarbonImmutable::parse('1990-01-01');

    $result = SadeSati::forChart($chart, $birth, CarbonImmutable::parse('1990-01-01'));
    $firstInterval = $result['lifetime']['sade_sati'][0];

    // Query exactly inside the first interval's span.
    $midpoint = CarbonImmutable::parse($firstInterval['start'])->addDays(10);
    $active = SadeSati::forChart($chart, $birth, $midpoint);

    expect($active['is_active'])->toBeTrue()
        ->and($active['phase'])->toBe($firstInterval['phase']);
});

test('cycle_start, peak_phase_start, setting_phase_start, and cycle_end are chronologically ordered within a cycle', function () {
    $chart = sadeSatiChart('Pisces');
    $birth = CarbonImmutable::parse('1985-03-15');

    $result = SadeSati::forChart($chart, $birth, CarbonImmutable::parse('1985-03-15'));

    expect($result['cycle_start'])->not->toBeNull();
    $dates = collect([$result['cycle_start'], $result['peak_phase_start'], $result['setting_phase_start'], $result['cycle_end']])
        ->map(fn ($date) => CarbonImmutable::parse($date));

    expect($dates->values()->all())->toEqual($dates->sort()->values()->all());
});
