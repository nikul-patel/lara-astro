<?php

use App\Services\Astrology\Nakshatra;
use App\Services\Astrology\Predictions\DashaNarrativePredictor;
use App\Services\Astrology\Predictions\Ordinal;
use App\Services\Astrology\Predictions\TemplateRenderer;
use App\Services\Astrology\Predictions\Templates\DashaNarrativeTemplates;
use App\Services\Astrology\Predictions\Templates\HouseSignifications;

function dashaNarrativeHousesFixture(): array
{
    // Spreads all 9 Vimshottari lords across 9 different houses, 1-9.
    $lordHouses = ['Ketu' => 1, 'Venus' => 2, 'Sun' => 3, 'Moon' => 4, 'Mars' => 5, 'Rahu' => 6, 'Jupiter' => 7, 'Saturn' => 8, 'Mercury' => 9];
    $signs = ['Aries', 'Taurus', 'Gemini', 'Cancer', 'Leo', 'Virgo', 'Libra', 'Scorpio', 'Sagittarius', 'Capricorn', 'Aquarius', 'Pisces'];

    $houses = [];
    for ($number = 1; $number <= 12; $number++) {
        $houses[] = [
            'number' => $number,
            'sign' => $signs[$number - 1],
            'planets' => array_keys(array_filter($lordHouses, fn (int $h) => $h === $number)),
        ];
    }

    return $houses;
}

test('generate() returns one narrative per Vimshottari lord, with the correct house and signification interpolated', function () {
    $narratives = DashaNarrativePredictor::generate(dashaNarrativeHousesFixture());

    expect($narratives)->toHaveCount(9);
    expect(array_column($narratives, 'lord'))->toEqualCanonicalizing(Nakshatra::LORD_CYCLE);

    $jupiterEntry = collect($narratives)->firstWhere('lord', 'Jupiter');
    expect($jupiterEntry['house'])->toBe(7);
    expect($jupiterEntry['text'])->toContain('7th house')
        ->toContain(HouseSignifications::SIGNIFICATION[7]);
});

test('every lord x house combination (9 x 12 = 108) resolves to real, non-placeholder text', function () {
    foreach (Nakshatra::LORD_CYCLE as $lord) {
        foreach (range(1, 12) as $house) {
            $text = TemplateRenderer::render(DashaNarrativeTemplates::TEMPLATES['en'][$lord], [
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
