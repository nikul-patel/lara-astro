<?php

use App\Services\Astrology\Nakshatra;
use App\Services\Astrology\Predictions\NakshatraPredictor;

test('every one of the 27 nakshatras resolves to real, non-placeholder text', function () {
    foreach (Nakshatra::NAMES as $name) {
        $result = NakshatraPredictor::describe($name);

        expect($result['key'])->toBe($name);
        expect($result['text'])->toBeString()->not->toBeEmpty();
        // A genuine paragraph, not a one-line placeholder.
        expect(strlen($result['text']))->toBeGreaterThan(200);
        expect($result['text'])->not->toContain('{')->not->toContain('}');
    }
});

test('all 27 nakshatra descriptions are genuinely distinct text, not copy-pasted duplicates', function () {
    $texts = array_map(fn (string $name) => NakshatraPredictor::describe($name)['text'], Nakshatra::NAMES);

    expect(array_unique($texts))->toHaveCount(27);
});

test('a spot check of descriptions names their own nakshatra\'s classical ruling deity', function () {
    $deities = [
        'Ashwini' => 'Ashwini Kumaras',
        'Bharani' => 'Yama',
        'Rohini' => 'Brahma',
        'Pushya' => 'Brihaspati',
        'Magha' => 'Pitris',
        'Jyeshtha' => 'Indra',
        'Shravana' => 'Vishnu',
        'Shatabhisha' => 'Varuna',
        'Revati' => 'Pushan',
    ];

    foreach ($deities as $nakshatra => $deity) {
        expect(NakshatraPredictor::describe($nakshatra)['text'])->toContain($deity);
    }
});
