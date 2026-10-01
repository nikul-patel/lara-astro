<?php

use App\Services\Astrology\Nakshatra;
use App\Services\Astrology\Predictions\Ordinal;
use App\Services\Astrology\Predictions\TemplateRenderer;
use App\Services\Astrology\Predictions\Templates\HouseSignifications;
use App\Services\Astrology\Predictions\Templates\VarshaphalNarrativeTemplates;
use App\Services\Astrology\Predictions\VarshaphalNarrativePredictor;

test('annotate() attaches a non-empty, non-placeholder narrative to every period while preserving the original fields', function () {
    $timeline = [
        ['lord' => 'Jupiter', 'start' => '2025-05-12T00:00:00+00:00', 'end' => '2025-06-30T00:00:00+00:00', 'house' => 7],
        ['lord' => 'Saturn', 'start' => '2025-06-30T00:00:00+00:00', 'end' => '2025-08-27T00:00:00+00:00', 'house' => 11],
    ];

    $annotated = VarshaphalNarrativePredictor::annotate($timeline);

    expect($annotated[0])->toHaveKeys(['lord', 'start', 'end', 'house', 'text']);
    expect($annotated[0]['lord'])->toBe('Jupiter');
    expect($annotated[0]['start'])->toBe('2025-05-12T00:00:00+00:00'); // Original fields untouched.
    expect($annotated[0]['text'])->toContain('7th house')->toContain(HouseSignifications::SIGNIFICATION[7]);
    expect($annotated[1]['text'])->toContain('11th house')->toContain(HouseSignifications::SIGNIFICATION[11]);
});

test('every lord x house combination (9 x 12 = 108) resolves to real, non-placeholder text', function () {
    foreach (Nakshatra::LORD_CYCLE as $lord) {
        foreach (range(1, 12) as $house) {
            $text = TemplateRenderer::render(VarshaphalNarrativeTemplates::TEMPLATES['en'][$lord], [
                'house' => Ordinal::suffix($house),
                'signification' => HouseSignifications::SIGNIFICATION[$house],
            ]);

            expect($text)->toBeString()->not->toBeEmpty();
            expect(strlen($text))->toBeGreaterThan(150);
            expect($text)->not->toContain('{')->not->toContain('}');
            expect($text)->toContain(Ordinal::suffix($house).' house');
        }
    }
});
