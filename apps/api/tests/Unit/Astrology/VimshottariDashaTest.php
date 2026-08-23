<?php

use App\Services\Astrology\Nakshatra;
use App\Services\Astrology\VimshottariDasha;
use Carbon\CarbonImmutable;

test('a Moon at the exact start of a nakshatra gives its lord a full, untruncated first Mahadasha', function () {
    $birth = CarbonImmutable::parse('2000-01-01 00:00:00', 'UTC');

    // 0° sidereal is the start of Ashwini, ruled by Ketu (7 years).
    $timeline = VimshottariDasha::timeline(0.0, $birth);

    expect($timeline[0]['lord'])->toBe('Ketu')
        ->and($timeline[0]['start'])->toBe('2000-01-01')
        ->and($timeline[0]['end'])->toBe($birth->addRealSeconds(7 * 365.25 * 86400)->toDateString());
});

test('the 9 Mahadashas of one full pass sum to exactly 120 years when nothing is elapsed at birth', function () {
    $birth = CarbonImmutable::parse('2000-01-01 00:00:00', 'UTC');

    $timeline = VimshottariDasha::timeline(0.0, $birth);

    expect($timeline)->toHaveCount(9);
    $lastEnd = CarbonImmutable::parse(end($timeline)['end']);
    expect($birth->diffInDays($lastEnd))->toBeBetween(120 * 365.25 - 1, 120 * 365.25 + 1);
});

test('Mahadasha lords follow the fixed 9-lord cycle starting from the Moon nakshatra lord', function () {
    $birth = CarbonImmutable::parse('2000-01-01 00:00:00', 'UTC');

    // 20° sidereal is within Bharani (ruled by Venus).
    $timeline = VimshottariDasha::timeline(20.0, $birth);

    $lords = array_column($timeline, 'lord');
    $startIndex = array_search('Venus', Nakshatra::LORD_CYCLE, true);
    $expected = [];
    for ($i = 0; $i < 9; $i++) {
        $expected[] = Nakshatra::LORD_CYCLE[($startIndex + $i) % 9];
    }

    expect($lords)->toBe($expected);
});

test('a birth Moon partway through its nakshatra truncates only the first Mahadasha to its remaining balance', function () {
    $birth = CarbonImmutable::parse('2000-01-01 00:00:00', 'UTC');

    // Halfway through Ashwini (Ketu, 7 years) -> 3.5 years remaining.
    $halfwayThroughAshwini = (360 / 27) / 2;
    $timeline = VimshottariDasha::timeline($halfwayThroughAshwini, $birth);

    $firstEnd = CarbonImmutable::parse($timeline[0]['end']);
    expect($birth->diffInDays($firstEnd))->toBeBetween(3.5 * 365.25 - 1, 3.5 * 365.25 + 1);

    // The second Mahadasha (Venus, 20 years) is full-length, unaffected by
    // the first period's truncation.
    $secondEnd = CarbonImmutable::parse($timeline[1]['end']);
    expect($firstEnd->diffInDays($secondEnd))->toBeBetween(20 * 365.25 - 1, 20 * 365.25 + 1);
});

test('each Mahadasha has 9 Antardashas whose lords cycle starting from the Mahadasha\'s own lord', function () {
    $birth = CarbonImmutable::parse('2000-01-01 00:00:00', 'UTC');

    $timeline = VimshottariDasha::timeline(20.0, $birth); // second Mahadasha lord: Sun

    $secondMahadasha = $timeline[1];
    expect($secondMahadasha['lord'])->toBe('Sun')
        ->and($secondMahadasha['antardashas'])->toHaveCount(9)
        ->and($secondMahadasha['antardashas'][0]['lord'])->toBe('Sun');
});

test('Antardasha spans sum back to exactly their parent Mahadasha\'s span', function () {
    $birth = CarbonImmutable::parse('2000-01-01 00:00:00', 'UTC');

    $timeline = VimshottariDasha::timeline(0.0, $birth);
    $mahadasha = $timeline[2]; // a full, untruncated Mahadasha

    $mahadashaStart = CarbonImmutable::parse($mahadasha['start']);
    $mahadashaEnd = CarbonImmutable::parse($mahadasha['end']);

    $antardashaStart = CarbonImmutable::parse($mahadasha['antardashas'][0]['start']);
    $antardashaEnd = CarbonImmutable::parse(end($mahadasha['antardashas'])['end']);

    expect($antardashaStart->toDateString())->toBe($mahadashaStart->toDateString())
        ->and($antardashaEnd->toDateString())->toBe($mahadashaEnd->toDateString());
});

test('pratyantardashas() divides an Antardasha into 9 sub-periods cycling from its own lord', function () {
    $start = CarbonImmutable::parse('2010-01-01 00:00:00', 'UTC');
    $end = $start->addRealSeconds(2 * 365.25 * 86400);

    $pratyantardashas = VimshottariDasha::pratyantardashas('Mars', $start, $end);

    expect($pratyantardashas)->toHaveCount(9)
        ->and($pratyantardashas[0]['lord'])->toBe('Mars')
        ->and($pratyantardashas[0]['start'])->toBe($start->toDateString())
        ->and(end($pratyantardashas)['end'])->toBe($end->toDateString());
});
