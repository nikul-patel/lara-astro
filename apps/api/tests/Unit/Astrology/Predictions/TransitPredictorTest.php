<?php

use App\Services\Astrology\Predictions\TransitPredictor;

test('generate() renders real, non-placeholder text for all 9 grahas with their house-from-Moon interpolated in', function () {
    $transits = [
        'Sun' => ['sign' => 'Leo', 'house_from_moon' => 3],
        'Moon' => ['sign' => 'Cancer', 'house_from_moon' => 2],
        'Mercury' => ['sign' => 'Virgo', 'house_from_moon' => 4],
        'Venus' => ['sign' => 'Libra', 'house_from_moon' => 5],
        'Mars' => ['sign' => 'Scorpio', 'house_from_moon' => 6],
        'Jupiter' => ['sign' => 'Sagittarius', 'house_from_moon' => 7],
        'Saturn' => ['sign' => 'Capricorn', 'house_from_moon' => 8],
        'Rahu' => ['sign' => 'Aquarius', 'house_from_moon' => 9],
        'Ketu' => ['sign' => 'Leo', 'house_from_moon' => 3],
    ];

    $result = TransitPredictor::generate($transits);

    expect(array_keys($result))->toBe(array_keys($transits));
    foreach ($transits as $planet => $transit) {
        expect($result[$planet]['sign'])->toBe($transit['sign']);
        expect($result[$planet]['house_from_moon'])->toBe($transit['house_from_moon']);
        expect(strlen($result[$planet]['text']))->toBeGreaterThan(150);
        expect($result[$planet]['text'])->not->toContain('{')->not->toContain('}');
        // The interpolated house number must actually appear in the rendered text.
        expect($result[$planet]['text'])->toContain((string) $transit['house_from_moon']);
    }
});

test('Saturn\'s transit text references Sade Sati, confirming the right template rendered for the right planet', function () {
    $result = TransitPredictor::generate(['Saturn' => ['sign' => 'Pisces', 'house_from_moon' => 1]]);

    expect($result['Saturn']['text'])->toContain('Sade Sati');
});
