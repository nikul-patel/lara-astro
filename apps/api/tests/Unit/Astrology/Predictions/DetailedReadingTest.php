<?php

use App\Services\Astrology\Predictions\ChartContext;
use App\Services\Astrology\Predictions\CurrentPeriodPredictor;
use App\Services\Astrology\Predictions\DetailedReadingEngine;
use App\Services\Astrology\Predictions\LifeAreaAnalyzer;
use App\Services\Astrology\Predictions\PlanetStrength;
use App\Services\Astrology\Predictions\Templates\LifeAreaTemplates;
use App\Services\Astrology\Predictions\Templates\LordInHouseTemplates;
use App\Services\Astrology\Predictions\Templates\PlanetInHouseTemplates;
use App\Services\Astrology\Predictions\Templates\ReadingPhrases;
use App\Services\Astrology\ZodiacSigns;
use Carbon\CarbonImmutable;
use Tests\Support\YogaChartFixture;

/**
 * A ChartContext from a whole-sign fixture, each planet placed at 15° of
 * its house's sign unless a degree is given — far enough from the Sun's
 * own position (when the Sun sits in another sign) to avoid accidental
 * combustion.
 *
 * @param  array<string, int>  $planetHouses
 * @param  array<string, float>  $degrees
 */
function readingContext(string $ascendant, array $planetHouses, array $degrees = [], ?array $sav = null, ?array $mahadashas = null): ChartContext
{
    $houses = YogaChartFixture::houses($ascendant, $planetHouses);

    $longitudes = [];
    foreach ($houses as $house) {
        foreach ($house['planets'] as $planet) {
            $longitudes[$planet] = array_search($house['sign'], ZodiacSigns::NAMES, true) * 30 + ($degrees[$planet] ?? 15.0);
        }
    }

    return new ChartContext($houses, $longitudes, $ascendant, $sav, $mahadashas);
}

test('dignity follows the seven-step classical scale', function (string $planet, string $sign, ?float $longitude, string $expected) {
    expect(PlanetStrength::dignity($planet, $sign, $longitude))->toBe($expected);
})->with([
    'exalted' => ['Sun', 'Aries', 10.0, 'exalted'],
    'debilitated' => ['Saturn', 'Aries', 10.0, 'debilitated'],
    'moolatrikona degree band' => ['Sun', 'Leo', 130.0, 'moolatrikona'],
    'own sign past the moolatrikona band' => ['Sun', 'Leo', 145.0, 'own'],
    'friendly sign ruler' => ['Sun', 'Sagittarius', 250.0, 'friendly'],
    'enemy sign ruler' => ['Sun', 'Capricorn', 280.0, 'enemy'],
    'neutral sign ruler' => ['Sun', 'Gemini', 70.0, 'neutral'],
    'node without rulership dignity' => ['Rahu', 'Gemini', 70.0, 'node'],
    'node exaltation convention' => ['Rahu', 'Taurus', 40.0, 'exalted'],
]);

test('a house with an exalted ruler in a kendra and Jupiter\'s aspect reads as strong', function () {
    // Aries Lagna: 10th is Capricorn (ruler Saturn). Saturn is exalted in Libra (7th);
    // Jupiter, exalted in Cancer (4th), aspects the 10th (7th from itself).
    $reading = LifeAreaAnalyzer::analyze(readingContext('Aries', ['Saturn' => 7, 'Jupiter' => 4]), 10);

    expect($reading['strength'])->toBe('strong')
        ->and($reading['lord'])->toBe('Saturn')
        ->and($reading['lord_house'])->toBe(7)
        ->and($reading['lord_dignity'])->toBe('exalted')
        ->and($reading['aspected_by'])->toContain('Jupiter');

    $ruler = collect($reading['sections'])->firstWhere('label', 'The ruler')['text'];
    expect($ruler)->toContain(LordInHouseTemplates::TEMPLATES['en'][10][7])
        ->toContain('exalted in Libra');
});

test('a house whose ruler is debilitated in a dusthana, with a malefic occupant, needs care', function () {
    // Aries Lagna: 2nd is Taurus (ruler Venus). Venus is debilitated in Virgo (6th); Saturn sits in the 2nd.
    $reading = LifeAreaAnalyzer::analyze(readingContext('Aries', ['Venus' => 6, 'Saturn' => 2]), 2);

    expect($reading['strength'])->toBe('care')
        ->and($reading['score'])->toBeLessThan(0);

    $strength = collect($reading['sections'])->firstWhere('label', 'Strength')['text'];
    expect($strength)->toContain('its ruler Venus is debilitated')->toContain('Saturn occupies it');
});

test('a dusthana lord placed in another dusthana counts as support (the Viparita Raja Yoga pattern)', function () {
    // Aries Lagna: 6th is Virgo (ruler Mercury), placed in the 8th.
    $reading = LifeAreaAnalyzer::analyze(readingContext('Aries', ['Mercury' => 8]), 6);

    $strength = collect($reading['sections'])->firstWhere('label', 'Strength')['text'];
    expect($strength)->toContain('Supportive factors: its ruler sits in the 8th house');
});

test('an empty house is explained as flowing through its ruler rather than read as weak', function () {
    $reading = LifeAreaAnalyzer::analyze(readingContext('Aries', ['Venus' => 2]), 5);

    expect($reading['occupants'])->toBe([])
        ->and(collect($reading['sections'])->firstWhere('label', 'Planets here')['text'])->toContain('an empty house is not a weak house');
});

test('Sarvashtakavarga bindus raise or lower the score against the 28-point average', function () {
    $sav = array_fill_keys(ZodiacSigns::NAMES, 28);
    $sav['Leo'] = 32; // Aries Lagna: 5th house
    $sav['Virgo'] = 20; // 6th house

    $context = readingContext('Aries', ['Sun' => 5], [], $sav);

    expect(LifeAreaAnalyzer::analyze($context, 5)['sav_bindus'])->toBe(32)
        ->and(collect(LifeAreaAnalyzer::analyze($context, 5)['sections'])->firstWhere('label', 'Strength')['text'])->toContain('a high Sarvashtakavarga score of 32 bindus')
        ->and(collect(LifeAreaAnalyzer::analyze($context, 6)['sections'])->firstWhere('label', 'Strength')['text'])->toContain('a low Sarvashtakavarga score of 20 bindus');
});

test('timing lists the ruler\'s and occupants\' Mahadashas, leaving out periods beyond a realistic lifespan', function () {
    $mahadashas = [
        ['lord' => 'Venus', 'start' => '2000-01-01', 'end' => '2020-01-01', 'antardashas' => []],
        ['lord' => 'Saturn', 'start' => '2095-01-01', 'end' => '2114-01-01', 'antardashas' => []],
    ];
    // Aries Lagna: 10th is Capricorn (ruler Saturn); Venus occupies the 10th.
    $reading = LifeAreaAnalyzer::analyze(readingContext('Aries', ['Saturn' => 7, 'Venus' => 10], [], null, $mahadashas), 10);

    expect($reading['activation_periods'])->toHaveCount(1)
        ->and($reading['activation_periods'][0])->toMatchArray(['lord' => 'Venus', 'role' => 'occupant']);

    $timing = collect($reading['sections'])->firstWhere('label', 'Timing')['text'];
    expect($timing)->toContain('Venus Mahadasha (2000–2020) as a planet placed here')
        ->toContain('Saturn’s own Mahadasha falls late');
});

test('the full reading covers all twelve houses with an overview naming the strongest areas', function () {
    $reading = DetailedReadingEngine::generate(readingContext('Aries', [
        'Sun' => 1, 'Moon' => 4, 'Mars' => 10, 'Mercury' => 12, 'Jupiter' => 4,
        'Venus' => 2, 'Saturn' => 7, 'Rahu' => 3, 'Ketu' => 9,
    ]));

    expect($reading['life_areas'])->toHaveCount(12)
        ->and(collect($reading['life_areas'])->pluck('house')->all())->toBe(range(1, 12))
        ->and($reading['overview']['strongest_areas'])->toHaveCount(3)
        ->and($reading['overview']['paragraphs'][0])->toContain('Your Ascendant (Lagna) is Aries, ruled by Mars');

    foreach ($reading['life_areas'] as $area) {
        expect($area['strength'])->toBeIn(['strong', 'moderate', 'care'])
            ->and(collect($area['sections'])->pluck('label'))->toContain('Overview', 'The ruler', 'Strength', 'Guidance');
    }
});

test('the current period reading finds the running Mahadasha/Antardasha and the next four sub-periods across the Mahadasha boundary', function () {
    $mahadashas = [
        ['lord' => 'Jupiter', 'start' => '2020-01-01', 'end' => '2036-01-01', 'antardashas' => [
            ['lord' => 'Jupiter', 'start' => '2020-01-01', 'end' => '2022-03-01'],
            ['lord' => 'Saturn', 'start' => '2022-03-01', 'end' => '2024-09-01'],
            ['lord' => 'Mercury', 'start' => '2024-09-01', 'end' => '2036-01-01'],
        ]],
        ['lord' => 'Saturn', 'start' => '2036-01-01', 'end' => '2055-01-01', 'antardashas' => [
            ['lord' => 'Saturn', 'start' => '2036-01-01', 'end' => '2039-01-01'],
            ['lord' => 'Mercury', 'start' => '2039-01-01', 'end' => '2042-01-01'],
            ['lord' => 'Ketu', 'start' => '2042-01-01', 'end' => '2055-01-01'],
        ]],
    ];
    // Jupiter in Cancer (4th), Saturn in Libra (7th): Libra is 4th from Cancer, a kendra relationship.
    $context = readingContext('Aries', ['Jupiter' => 4, 'Saturn' => 7, 'Mercury' => 1, 'Ketu' => 9], [], null, $mahadashas);

    $period = CurrentPeriodPredictor::generate($context, CarbonImmutable::parse('2023-06-01'));

    expect($period['mahadasha']['lord'])->toBe('Jupiter')
        ->and($period['antardasha']['lord'])->toBe('Saturn')
        ->and($period['paragraphs'][0])->toContain('Jupiter Mahadasha')->toContain('rules your 9th house')
        ->and($period['paragraphs'][2])->toBe(ReadingPhrases::PERIOD_RELATIONSHIP['en']['kendra'])
        ->and(collect($period['upcoming'])->map(fn (array $p) => $p['mahadasha_lord'].'/'.$p['lord'])->all())
        ->toBe(['Jupiter/Mercury', 'Saturn/Saturn', 'Saturn/Mercury', 'Saturn/Ketu']);

    expect(CurrentPeriodPredictor::generate($context, CarbonImmutable::parse('1990-01-01')))->toBeNull();
});

test('every narrative library is complete for every planet, house and sign', function () {
    $planets = ['Sun', 'Moon', 'Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn', 'Rahu', 'Ketu'];

    foreach (range(1, 12) as $lordOf) {
        foreach (range(1, 12) as $placedIn) {
            expect(LordInHouseTemplates::TEMPLATES['en'][$lordOf][$placedIn] ?? '')->not->toBeEmpty();
        }

        $area = LifeAreaTemplates::AREAS['en'][$lordOf];
        expect($area['title'])->not->toBeEmpty()
            ->and(array_keys($area['guidance']))->toBe(['strong', 'moderate', 'care']);
    }

    foreach ($planets as $planet) {
        foreach (range(1, 12) as $house) {
            expect(PlanetInHouseTemplates::TEMPLATES['en'][$planet][$house] ?? '')->not->toBeEmpty();
        }
        expect(ReadingPhrases::ASPECT['en'][$planet])->not->toBeEmpty()
            ->and(ReadingPhrases::PERIOD_THEME['en'][$planet])->not->toBeEmpty();
    }

    expect(array_keys(ReadingPhrases::SIGN_QUALITY['en']))->toBe(ZodiacSigns::NAMES);
});
