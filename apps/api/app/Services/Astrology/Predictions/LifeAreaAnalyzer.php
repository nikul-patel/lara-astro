<?php

namespace App\Services\Astrology\Predictions;

use App\Services\Astrology\HouseLords;
use App\Services\Astrology\Predictions\Templates\LifeAreaTemplates;
use App\Services\Astrology\Predictions\Templates\LordInHouseTemplates;
use App\Services\Astrology\Predictions\Templates\PlanetInHouseTemplates;
use App\Services\Astrology\Predictions\Templates\ReadingPhrases;

/**
 * Multi-factor reading of one house (bhava), following the judgment order
 * Phaladeepika ch. 15 lays out for "studying the effects of the Bhavas":
 * the house lord's dignity and placement, the planets occupying the
 * house, the planets aspecting it, the house's natural significator
 * (karaka), and — where the chart has it — the house sign's
 * Sarvashtakavarga bindus (28 being the chart-wide average). Each factor
 * contributes a small signed score and a plain-English reason; the total
 * sorts the house into one of three bands, and every factor is also
 * written up as its own paragraph so the reader sees why.
 *
 * Bands: 5+ strong, 0-4 moderate, negative needs care.
 *
 * Scoring rules (all classical, simplified to integers):
 * - lord dignity: exalted +3 … debilitated -3 (PlanetStrength), combust -1
 * - lord placement from the Lagna: kendra/trikona +2, 2nd/11th +1,
 *   6th/8th/12th -2 — except a dusthana lord in a dusthana, the Viparita
 *   Raja Yoga pattern, which scores +1
 * - occupants (other than the lord, already scored above): natural
 *   benefics +1 except in a dusthana; natural malefics +1 in an upachaya
 *   house (3, 6, 10, 11) and -1 elsewhere
 * - aspects: the house's own lord +1, Jupiter +2, other benefics +1,
 *   Mars/Saturn/Rahu/Ketu -1 outside upachaya houses; the Sun is neutral
 * - karaka dignity: dignified +1, debilitated -1
 * - Sarvashtakavarga: 30+ +2, 28-29 +1, below 25 -1
 */
class LifeAreaAnalyzer
{
    private const KENDRA_TRIKONA = [1, 4, 5, 7, 9, 10];

    private const DUSTHANA = [6, 8, 12];

    private const UPACHAYA = [3, 6, 10, 11];

    private const LIFESPAN_YEARS = 90;

    private const STRENGTH_LABEL = ['strong' => 'Strong', 'moderate' => 'Moderate', 'care' => 'Needs care'];

    /**
     * @return array{house: int, title: string, sign: string, lord: string, lord_house: int|null, lord_dignity: string|null, occupants: list<string>, aspected_by: list<string>, karaka: string, sav_bindus: int|null, score: int, strength: string, strength_label: string, activation_periods: list<array{lord: string, role: string, start: string, end: string}>, sections: list<array{label: string, text: string}>}
     */
    public static function analyze(ChartContext $chart, int $house): array
    {
        $area = LifeAreaTemplates::AREAS['en'][$house];
        $sign = $chart->signOfHouse($house);
        $lord = $chart->lordOf($house);
        $lordHouse = $chart->houseOf($lord);
        $lordDignity = $chart->dignity($lord);
        $occupants = $chart->occupants($house);
        $aspecting = $chart->aspectingPlanets($house);
        $sav = $chart->savFor($house);

        $score = 0;
        $positives = [];
        $negatives = [];

        if ($lordDignity !== null) {
            $dignityScore = PlanetStrength::score($lordDignity);
            $score += $dignityScore;
            if ($dignityScore > 0) {
                $positives[] = "its ruler {$lord} is ".self::dignityWord($lordDignity);
            } elseif ($dignityScore < 0) {
                $negatives[] = "its ruler {$lord} is ".self::dignityWord($lordDignity);
            }
        }

        if ($chart->isCombust($lord)) {
            $score--;
            $negatives[] = "its ruler {$lord} is combust";
        }

        if ($lordHouse !== null) {
            $placementScore = self::placementScore($house, $lordHouse);
            $score += $placementScore;
            $placement = $lordHouse === $house ? 'its ruler occupies its own house' : 'its ruler sits in the '.Ordinal::suffix($lordHouse).' house';
            if ($placementScore > 0) {
                $positives[] = $placement;
            } elseif ($placementScore < 0) {
                $negatives[] = $placement;
            }
        }

        foreach ($occupants as $occupant) {
            if ($occupant === $lord) {
                continue;
            }

            $occupantScore = self::occupantScore($occupant, $house);
            $score += $occupantScore;
            if ($occupantScore > 0) {
                $positives[] = "{$occupant} occupies it";
            } elseif ($occupantScore < 0) {
                $negatives[] = "{$occupant} occupies it";
            }
        }

        foreach ($aspecting as $planet) {
            $aspectScore = $planet === $lord ? 1 : self::aspectScore($planet, $house);
            $score += $aspectScore;
            $reason = $planet === $lord ? "its own ruler {$planet} aspects it" : "{$planet} aspects it";
            if ($aspectScore > 0) {
                $positives[] = $reason;
            } elseif ($aspectScore < 0) {
                $negatives[] = $reason;
            }
        }

        $karaka = $area['karaka'];
        $karakaDignity = $chart->dignity($karaka);
        if ($karakaDignity !== null && PlanetStrength::isDignified($karakaDignity)) {
            $score++;
            $positives[] = "its natural significator {$karaka} is ".self::dignityWord($karakaDignity);
        } elseif ($karakaDignity === 'debilitated') {
            $score--;
            $negatives[] = "its natural significator {$karaka} is debilitated";
        }

        if ($sav !== null) {
            $savScore = match (true) {
                $sav >= 30 => 2,
                $sav >= 28 => 1,
                $sav < 25 => -1,
                default => 0,
            };
            $score += $savScore;
            if ($savScore > 0) {
                $positives[] = "a high Sarvashtakavarga score of {$sav} bindus (the chart average is 28)";
            } elseif ($savScore < 0) {
                $negatives[] = "a low Sarvashtakavarga score of {$sav} bindus (the chart average is 28)";
            }
        }

        $strength = match (true) {
            $score >= 5 => 'strong',
            $score >= 0 => 'moderate',
            default => 'care',
        };

        $activationPeriods = self::activationPeriods($chart, $lord, $occupants);

        $sections = array_values(array_filter([
            ['label' => 'Overview', 'text' => $area['intro'].' In your chart it falls in '.ReadingPhrases::SIGN_QUALITY['en'][$sign].'.'],
            ['label' => 'The ruler', 'text' => self::lordText($chart, $house, $lord, $lordHouse)],
            ['label' => 'Planets here', 'text' => self::occupantsText($chart, $house, $lord, $occupants)],
            $aspecting !== [] ? ['label' => 'Aspects', 'text' => self::aspectsText($house, $aspecting, $occupants)] : null,
            ['label' => 'Natural significator', 'text' => self::karakaText($chart, $area['karaka'], $area['karaka_topic'])],
            ['label' => 'Strength', 'text' => self::strengthText($strength, $positives, $negatives, $sav)],
            $chart->mahadashas !== null ? ['label' => 'Timing', 'text' => self::timingText($lord, $activationPeriods)] : null,
            ['label' => 'Guidance', 'text' => $area['guidance'][$strength]],
        ]));

        return [
            'house' => $house,
            'title' => $area['title'],
            'sign' => $sign,
            'lord' => $lord,
            'lord_house' => $lordHouse,
            'lord_dignity' => $lordDignity,
            'occupants' => $occupants,
            'aspected_by' => $aspecting,
            'karaka' => $karaka,
            'sav_bindus' => $sav,
            'score' => $score,
            'strength' => $strength,
            'strength_label' => self::STRENGTH_LABEL[$strength],
            'activation_periods' => $activationPeriods,
            'sections' => $sections,
        ];
    }

    private static function placementScore(int $house, int $lordHouse): int
    {
        return match (true) {
            in_array($lordHouse, self::KENDRA_TRIKONA, true) => 2,
            in_array($lordHouse, [2, 11], true) => 1,
            in_array($lordHouse, self::DUSTHANA, true) => in_array($house, self::DUSTHANA, true) ? 1 : -2,
            default => 0,
        };
    }

    private static function occupantScore(string $planet, int $house): int
    {
        if (in_array($planet, PlanetStrength::NATURAL_BENEFICS, true)) {
            return in_array($house, self::DUSTHANA, true) ? 0 : 1;
        }

        return in_array($house, self::UPACHAYA, true) ? 1 : -1;
    }

    private static function aspectScore(string $planet, int $house): int
    {
        return match (true) {
            $planet === 'Jupiter' => 2,
            in_array($planet, PlanetStrength::NATURAL_BENEFICS, true) => 1,
            $planet === 'Sun' => 0,
            in_array($house, self::UPACHAYA, true) => 0,
            default => -1,
        };
    }

    private static function lordText(ChartContext $chart, int $house, string $lord, ?int $lordHouse): string
    {
        if ($lordHouse === null) {
            return "{$lord} rules this house.";
        }

        $location = $lordHouse === $house ? 'sits in this very house' : 'is placed in your '.Ordinal::suffix($lordHouse).' house';
        $text = "{$lord}, the ruler of this house, {$location}. ".LordInHouseTemplates::TEMPLATES['en'][$house][$lordHouse];

        return trim($text.' '.self::dignitySentence($chart, $lord));
    }

    /**
     * @param  list<string>  $occupants
     */
    private static function occupantsText(ChartContext $chart, int $house, string $lord, array $occupants): string
    {
        if ($occupants === []) {
            return "No planets occupy this house, so its results flow mainly through its ruler, {$lord}, and the planets aspecting it — an empty house is not a weak house.";
        }

        $sentences = [];
        foreach ($occupants as $planet) {
            $sentences[] = PlanetInHouseTemplates::TEMPLATES['en'][$planet][$house];
            if ($planet !== $lord) {
                $sentences[] = self::dignitySentence($chart, $planet);
            }
        }

        $malefics = array_intersect($occupants, PlanetStrength::NATURAL_MALEFICS);
        if ($malefics !== [] && in_array($house, self::UPACHAYA, true)) {
            $sentences[] = ReadingPhrases::UPACHAYA_MALEFIC['en'];
        }

        return implode(' ', array_filter($sentences));
    }

    /**
     * @param  list<string>  $aspecting
     * @param  list<string>  $occupants
     */
    private static function aspectsText(int $house, array $aspecting, array $occupants): string
    {
        $sentences = ['This house receives the aspect (drishti) of '.self::listJoin($aspecting).'.'];
        foreach ($aspecting as $planet) {
            $sentences[] = ReadingPhrases::ASPECT['en'][$planet];
        }

        $maleficAspects = array_intersect($aspecting, ['Mars', 'Saturn', 'Rahu', 'Ketu']);
        $occupantNoteGiven = array_intersect($occupants, PlanetStrength::NATURAL_MALEFICS) !== [];
        if ($maleficAspects !== [] && ! $occupantNoteGiven && in_array($house, self::UPACHAYA, true)) {
            $sentences[] = ReadingPhrases::UPACHAYA_MALEFIC['en'];
        }

        return implode(' ', array_unique($sentences));
    }

    private static function karakaText(ChartContext $chart, string $karaka, string $topic): string
    {
        $karakaHouse = $chart->houseOf($karaka);
        $dignity = $chart->dignity($karaka);

        if ($karakaHouse === null || $dignity === null) {
            return "{$karaka} is the natural significator of {$topic}.";
        }

        $condition = match (true) {
            PlanetStrength::isDignified($dignity) => 'which adds real support to this area',
            $dignity === 'debilitated' => 'so this area benefits from consciously strengthening '.$karaka,
            in_array($karakaHouse, self::DUSTHANA, true) => 'a hidden placement, so its support here works more quietly',
            default => 'which lends steady background support',
        };

        return "{$karaka}, the natural significator (karaka) of {$topic}, is ".self::dignityWord($dignity).' in your '.Ordinal::suffix($karakaHouse)." house, {$condition}.";
    }

    /**
     * @param  list<string>  $positives
     * @param  list<string>  $negatives
     */
    private static function strengthText(string $strength, array $positives, array $negatives, ?int $sav): string
    {
        $sentences = [];

        if ($positives !== []) {
            $sentences[] = 'Supportive factors: '.self::listJoin($positives).'.';
        }

        if ($negatives !== []) {
            $sentences[] = 'Factors asking for care: '.self::listJoin($negatives).'.';
        }

        $sentences[] = match ($strength) {
            'strong' => 'Weighed together, this area of life is strongly supported in your chart.',
            'moderate' => 'Weighed together, this area of life is moderately supported — results come with steady effort and good timing.',
            default => 'Weighed together, this area of life needs conscious care — awareness, patience and the guidance below make a real difference.',
        };

        if ($sav !== null && $sav >= 25 && $sav < 28) {
            $sentences[] = "Its Sarvashtakavarga score of {$sav} bindus sits in the average band (the chart average is 28).";
        }

        return implode(' ', $sentences);
    }

    /**
     * Mahadashas of the house's ruler and occupants that begin within a
     * realistic lifespan — the stored timeline is one full 120-year pass
     * from birth, so its last periods would otherwise read as "2096–2112".
     *
     * @param  list<string>  $occupants
     * @return list<array{lord: string, role: string, start: string, end: string}>
     */
    private static function activationPeriods(ChartContext $chart, string $lord, array $occupants): array
    {
        $birth = $chart->mahadashas[0]['start'] ?? null;
        if ($birth === null) {
            return [];
        }
        $cutoff = (string) ((int) substr($birth, 0, 4) + self::LIFESPAN_YEARS).substr($birth, 4);

        $periods = [];
        foreach (array_unique([$lord, ...$occupants]) as $planet) {
            $mahadasha = $chart->mahadashaOf($planet);
            if ($mahadasha !== null && $mahadasha['start'] < $cutoff) {
                $periods[] = [
                    'lord' => $planet,
                    'role' => $planet === $lord ? 'ruler' : 'occupant',
                    'start' => $mahadasha['start'],
                    'end' => $mahadasha['end'],
                ];
            }
        }

        usort($periods, fn (array $a, array $b) => strcmp($a['start'], $b['start']));

        return $periods;
    }

    /**
     * @param  list<array{lord: string, role: string, start: string, end: string}>  $periods
     */
    private static function timingText(string $lord, array $periods): ?string
    {
        $rulerIncluded = collect($periods)->contains('role', 'ruler');
        $sentences = [];

        if ($periods !== []) {
            $phrases = array_map(
                fn (array $period) => "the {$period['lord']} Mahadasha (".substr($period['start'], 0, 4).'–'.substr($period['end'], 0, 4).') '.($period['role'] === 'ruler' ? 'as its ruler' : 'as a planet placed here'),
                $periods,
            );
            $sentences[] = 'This area is most strongly activated during '.self::listJoin($phrases).'.';
        }

        if (! $rulerIncluded) {
            $sentences[] = "{$lord}’s own Mahadasha falls late in the 120-year Vimshottari cycle for your chart, so this area is brought forward mainly by {$lord}’s Antardashas (sub-periods) within other Mahadashas.";
        } else {
            $sentences[] = 'Antardashas (sub-periods) of these planets within other Mahadashas also bring its themes forward.';
        }

        return implode(' ', $sentences);
    }

    public static function dignitySentence(ChartContext $chart, string $planet): string
    {
        $sign = $chart->signOf($planet);
        $dignity = $chart->dignity($planet);
        if ($sign === null || $dignity === null) {
            return '';
        }

        $sentence = TemplateRenderer::render(ReadingPhrases::DIGNITY['en'][$dignity], [
            'planet' => $planet,
            'sign' => $sign,
            'dispositor' => HouseLords::SIGN_RULERS[$sign],
        ]);

        if ($chart->isCombust($planet)) {
            $sentence .= ' '.ReadingPhrases::DIGNITY['en']['combust'];
        }

        return $sentence;
    }

    public static function dignityWord(string $dignity): string
    {
        return match ($dignity) {
            'exalted' => 'exalted',
            'moolatrikona' => 'in its moolatrikona sign',
            'own' => 'in its own sign',
            'friendly' => 'in a friendly sign',
            'neutral' => 'in a neutral sign',
            'enemy' => 'in an unfriendly sign',
            'debilitated' => 'debilitated',
            default => 'placed',
        };
    }

    /**
     * @param  list<string>  $items
     */
    public static function listJoin(array $items): string
    {
        $items = array_values($items);

        return match (count($items)) {
            0 => '',
            1 => $items[0],
            2 => "{$items[0]} and {$items[1]}",
            default => implode(', ', array_slice($items, 0, -1)).' and '.end($items),
        };
    }
}
