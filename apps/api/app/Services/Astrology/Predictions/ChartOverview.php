<?php

namespace App\Services\Astrology\Predictions;

use App\Services\Astrology\PlanetaryFriendship;
use App\Services\Astrology\Predictions\Templates\LordInHouseTemplates;
use App\Services\Astrology\Predictions\Templates\ReadingPhrases;

/**
 * The executive summary that opens the detailed reading: the Lagna lord's
 * placement, the chart's elemental and modal balance (counted over the
 * seven classical planets plus the Ascendant — the nodes have no physical
 * body and are conventionally left out of the balance), standout
 * dignified/debilitated planets, the strongest planet by Shadbala, and the
 * life areas LifeAreaAnalyzer scored highest and lowest.
 */
class ChartOverview
{
    /**
     * @param  list<array{house: int, title: string, score: int, strength: string}>  $areas
     * @return array{dominant_element: string, dominant_modality: string, strongest_planet: string|null, strongest_areas: list<int>, areas_needing_care: list<int>, paragraphs: list<string>}
     */
    public static function generate(ChartContext $chart, array $areas): array
    {
        $paragraphs = [];

        $lagnaLord = $chart->lordOf(1);
        $lagnaLordHouse = $chart->houseOf($lagnaLord);
        $lagnaParagraph = "Your Ascendant (Lagna) is {$chart->ascendantSign}, ruled by {$lagnaLord}.";
        if ($lagnaLordHouse !== null) {
            $lagnaParagraph .= " {$lagnaLord} sits in your ".Ordinal::suffix($lagnaLordHouse).' house. '.LordInHouseTemplates::TEMPLATES['en'][1][$lagnaLordHouse];
        }
        $paragraphs[] = $lagnaParagraph;

        $elements = ['fire' => 0, 'earth' => 0, 'air' => 0, 'water' => 0];
        $modalities = ['cardinal' => 0, 'fixed' => 0, 'mutable' => 0];
        $signs = [$chart->ascendantSign];
        foreach (PlanetaryFriendship::CLASSICAL_PLANETS as $planet) {
            if (($sign = $chart->signOf($planet)) !== null) {
                $signs[] = $sign;
            }
        }
        foreach ($signs as $sign) {
            $elements[$chart->elementOf($sign)]++;
            $modalities[$chart->modalityOf($sign)]++;
        }
        arsort($elements);
        arsort($modalities);
        $dominantElement = array_key_first($elements);
        $dominantModality = array_key_first($modalities);
        $paragraphs[] = ReadingPhrases::ELEMENT['en'][$dominantElement].' '.ReadingPhrases::MODALITY['en'][$dominantModality];

        $dignified = [];
        $debilitated = [];
        foreach ([...PlanetaryFriendship::CLASSICAL_PLANETS, 'Rahu', 'Ketu'] as $planet) {
            $dignity = $chart->dignity($planet);
            if ($dignity !== null && PlanetStrength::isDignified($dignity)) {
                $dignified[] = $planet.' ('.LifeAreaAnalyzer::dignityWord($dignity).' in '.$chart->signOf($planet).')';
            } elseif ($dignity === 'debilitated') {
                $debilitated[] = "{$planet} (in ".$chart->signOf($planet).')';
            }
        }
        if ($dignified !== []) {
            $paragraphs[] = 'Planets of special strength: '.LifeAreaAnalyzer::listJoin($dignified).'. These planets deliver their significations reliably and are pillars of your chart.';
        }
        if ($debilitated !== []) {
            $paragraphs[] = 'Planets needing support: '.LifeAreaAnalyzer::listJoin($debilitated).'. A debilitated planet is not a curse — its results tend to mature later and respond well to conscious effort and the remedies in this report.';
        }

        $strongestPlanet = self::strongestPlanet($chart);
        if ($strongestPlanet !== null) {
            $paragraphs[] = "By Shadbala (six-fold strength), {$strongestPlanet} is your strongest planet, gifting you ".ReadingPhrases::PLANET_GIFT['en'][$strongestPlanet].'. Its periods and the houses it rules tend to be where you shine.';
        }

        $ranked = $areas;
        usort($ranked, fn (array $a, array $b) => [$b['score'], $a['house']] <=> [$a['score'], $b['house']]);
        $strongest = array_slice($ranked, 0, 3);
        $needingCare = array_values(array_filter(array_reverse($ranked), fn (array $area) => $area['strength'] === 'care'));
        $needingCare = array_slice($needingCare, 0, 3);

        $paragraphs[] = 'Your most strongly supported life areas are '.LifeAreaAnalyzer::listJoin(array_map(fn (array $area) => self::areaLabel($area), $strongest)).'.';
        $paragraphs[] = $needingCare === []
            ? 'No area of your chart scores as weak overall — the life areas below each carry a mix of supports and lessons.'
            : 'The areas that most reward conscious care are '.LifeAreaAnalyzer::listJoin(array_map(fn (array $area) => self::areaLabel($area), $needingCare)).'. The detailed sections below explain why, and what helps.';

        return [
            'dominant_element' => $dominantElement,
            'dominant_modality' => $dominantModality,
            'strongest_planet' => $strongestPlanet,
            'strongest_areas' => array_map(fn (array $area) => $area['house'], $strongest),
            'areas_needing_care' => array_map(fn (array $area) => $area['house'], $needingCare),
            'paragraphs' => $paragraphs,
        ];
    }

    /**
     * Highest ratio of total Shadbala to the classical minimum required —
     * the standard way to compare planets whose required minimums differ.
     */
    private static function strongestPlanet(ChartContext $chart): ?string
    {
        $totals = $chart->shadbala['total_rupas'] ?? null;
        $minimums = $chart->shadbala['minimum_required_rupas'] ?? null;
        if ($totals === null || $minimums === null) {
            return null;
        }

        $ratios = [];
        foreach ($totals as $planet => $rupas) {
            if (isset($minimums[$planet]) && $minimums[$planet] > 0) {
                $ratios[$planet] = $rupas / $minimums[$planet];
            }
        }
        arsort($ratios);

        return array_key_first($ratios);
    }

    /**
     * @param  array{house: int, title: string}  $area
     */
    private static function areaLabel(array $area): string
    {
        return strtolower($area['title']).' ('.Ordinal::suffix($area['house']).' house)';
    }
}
