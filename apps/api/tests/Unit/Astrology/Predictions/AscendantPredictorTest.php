<?php

use App\Services\Astrology\Predictions\AscendantPredictor;
use App\Services\Astrology\ZodiacSigns;

test('every one of the 12 Ascendant signs resolves to real, non-placeholder text', function () {
    foreach (ZodiacSigns::NAMES as $sign) {
        $result = AscendantPredictor::describe($sign);

        expect($result['key'])->toBe($sign);
        expect($result['text'])->toBeString()->not->toBeEmpty();
        // A genuine paragraph, not a one-line placeholder.
        expect(strlen($result['text']))->toBeGreaterThan(200);
        expect($result['text'])->not->toContain('{')->not->toContain('}');
    }
});

test('each description names its own sign\'s ruling planet, confirming it is not a copy-pasted duplicate', function () {
    $rulers = [
        'Aries' => 'Mars', 'Taurus' => 'Venus', 'Gemini' => 'Mercury', 'Cancer' => 'Moon',
        'Leo' => 'Sun', 'Virgo' => 'Mercury', 'Libra' => 'Venus', 'Scorpio' => 'Mars',
        'Sagittarius' => 'Jupiter', 'Capricorn' => 'Saturn', 'Aquarius' => 'Saturn', 'Pisces' => 'Jupiter',
    ];

    foreach ($rulers as $sign => $ruler) {
        expect(AscendantPredictor::describe($sign)['text'])->toContain($ruler);
    }
});
