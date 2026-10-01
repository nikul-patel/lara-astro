<?php

use App\Services\Astrology\Jaimini\CharDasha;
use Carbon\CarbonImmutable;

function charDashaFixture(): array
{
    // Chosen so every sign's lord sits somewhere other than its own sign,
    // avoiding the degenerate (and classically special-cased) "lord in
    // own sign" case for every period in this test.
    return [
        'Sun' => 'Taurus', 'Moon' => 'Pisces', 'Mars' => 'Gemini', 'Mercury' => 'Scorpio',
        'Jupiter' => 'Cancer', 'Venus' => 'Capricorn', 'Saturn' => 'Aries',
    ];
}

test('an odd Lagna runs the dasha forward through the zodiac with hand-verified durations', function () {
    $birth = CarbonImmutable::parse('2000-01-01');
    $timeline = CharDasha::timeline('Aries', charDashaFixture(), $birth);

    $expectedSigns = ['Aries', 'Taurus', 'Gemini', 'Cancer', 'Leo', 'Virgo', 'Libra', 'Scorpio', 'Sagittarius', 'Capricorn', 'Aquarius', 'Pisces'];
    expect(array_column($timeline, 'sign'))->toBe($expectedSigns);

    // Aries (odd, lord Mars in Gemini): forward count Aries->Gemini = 3.
    // Taurus (even, lord Venus in Capricorn): backward count = 5.
    // Leo (odd, lord Sun in Taurus): forward count Leo->Taurus = 10.
    $expectedYears = ['Aries' => 3, 'Taurus' => 5, 'Gemini' => 6, 'Cancer' => 5, 'Leo' => 10, 'Virgo' => 11, 'Libra' => 4, 'Scorpio' => 6, 'Sagittarius' => 8, 'Capricorn' => 10, 'Aquarius' => 3, 'Pisces' => 9];
    foreach ($timeline as $period) {
        expect($period['years'])->toBe($expectedYears[$period['sign']]);
    }
});

test('an even Lagna runs the dasha backward through the zodiac', function () {
    $timeline = CharDasha::timeline('Taurus', charDashaFixture(), CarbonImmutable::parse('2000-01-01'));

    $expectedSigns = ['Taurus', 'Aries', 'Pisces', 'Aquarius', 'Capricorn', 'Sagittarius', 'Scorpio', 'Libra', 'Virgo', 'Leo', 'Cancer', 'Gemini'];
    expect(array_column($timeline, 'sign'))->toBe($expectedSigns);
});

test('periods chain with no gaps or overlaps', function () {
    $timeline = CharDasha::timeline('Leo', charDashaFixture(), CarbonImmutable::parse('1995-06-15'));

    expect($timeline)->toHaveCount(12);
    for ($i = 1; $i < 12; $i++) {
        expect($timeline[$i]['start'])->toBe($timeline[$i - 1]['end']);
    }
});
