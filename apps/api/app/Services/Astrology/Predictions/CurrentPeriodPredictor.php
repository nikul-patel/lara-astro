<?php

namespace App\Services\Astrology\Predictions;

use App\Services\Astrology\Predictions\Templates\LifeAreaTemplates;
use App\Services\Astrology\Predictions\Templates\ReadingPhrases;
use App\Services\Astrology\ZodiacSigns;
use Carbon\CarbonImmutable;

/**
 * Reads the Mahadasha/Antardasha running on a given date the way
 * Phaladeepika ch. 20 ("Dasas of the Bhava lords") frames it: a period
 * ruler delivers the affairs of the houses it RULES and the house it
 * OCCUPIES, coloured by its dignity, and an Antardasha is read through its
 * own houses plus its house-distance from the Mahadasha lord (trines and
 * kendras cooperate, 6-8 and 2-12 strain).
 *
 * Date-dependent, so it is computed at request time (the PDF report)
 * rather than stored in a saved chart's result — the same reasoning that
 * keeps Sade Sati and today's transits out of BirthChartCalculator.
 */
class CurrentPeriodPredictor
{
    private const UPCOMING_COUNT = 4;

    /**
     * @return array{mahadasha: array{lord: string, start: string, end: string}, antardasha: array{lord: string, start: string, end: string}, paragraphs: list<string>, upcoming: list<array{mahadasha_lord: string, lord: string, start: string, end: string, text: string}>}|null
     */
    public static function generate(ChartContext $chart, CarbonImmutable $on): ?array
    {
        $date = $on->toDateString();
        $mahadashas = $chart->mahadashas ?? [];

        $mahadashaIndex = collect($mahadashas)->search(fn (array $period) => $period['start'] <= $date && $date < $period['end']);
        if ($mahadashaIndex === false) {
            return null;
        }

        $mahadasha = $mahadashas[$mahadashaIndex];
        $antardashaIndex = collect($mahadasha['antardashas'])->search(fn (array $period) => $period['start'] <= $date && $date < $period['end']);
        if ($antardashaIndex === false) {
            return null;
        }
        $antardasha = $mahadasha['antardashas'][$antardashaIndex];

        $paragraphs = [
            "You are currently in the {$mahadasha['lord']} Mahadasha (".self::formatDate($mahadasha['start']).' – '.self::formatDate($mahadasha['end']).'). '.self::rulerReading($chart, $mahadasha['lord'], 'this chapter of life'),
            "Within it, the {$antardasha['lord']} Antardasha runs from ".self::formatDate($antardasha['start']).' to '.self::formatDate($antardasha['end']).'. '.self::rulerReading($chart, $antardasha['lord'], 'this sub-period'),
        ];

        if ($antardasha['lord'] !== $mahadasha['lord']) {
            $relationship = self::relationship($chart, $mahadasha['lord'], $antardasha['lord']);
            if ($relationship !== null) {
                $paragraphs[] = ReadingPhrases::PERIOD_RELATIONSHIP['en'][$relationship];
            }
        }

        return [
            'mahadasha' => ['lord' => $mahadasha['lord'], 'start' => $mahadasha['start'], 'end' => $mahadasha['end']],
            'antardasha' => ['lord' => $antardasha['lord'], 'start' => $antardasha['start'], 'end' => $antardasha['end']],
            'paragraphs' => $paragraphs,
            'upcoming' => self::upcoming($chart, $mahadashas, $mahadashaIndex, $antardashaIndex),
        ];
    }

    private static function rulerReading(ChartContext $chart, string $lord, string $scope): string
    {
        $theme = ReadingPhrases::PERIOD_THEME['en'][$lord];
        $placement = $chart->houseOf($lord);
        $ruled = $chart->housesRuledBy($lord);

        $sentence = $ruled === []
            ? "As a shadow planet, {$lord} rules no house of its own and works through its placement".($placement !== null ? ' in your '.self::areaPhrase($placement) : '').", bringing {$theme} to {$scope}."
            : "{$lord} rules your ".LifeAreaAnalyzer::listJoin(array_map(fn (int $house) => self::areaPhrase($house), $ruled))
                .($placement !== null ? ', and sits in your '.self::areaPhrase($placement) : '')
                .", so {$scope} emphasises {$theme}, channelled into those areas of life.";

        return trim($sentence.' '.LifeAreaAnalyzer::dignitySentence($chart, $lord));
    }

    private static function relationship(ChartContext $chart, string $mahadashaLord, string $antardashaLord): ?string
    {
        $fromSign = $chart->signOf($mahadashaLord);
        $toSign = $chart->signOf($antardashaLord);
        if ($fromSign === null || $toSign === null) {
            return null;
        }

        return match (ZodiacSigns::offset($fromSign, $toSign)) {
            1 => 'conjunct',
            5, 9 => 'trine',
            4, 10 => 'kendra',
            7 => 'opposition',
            3, 11 => 'upachaya',
            2, 12 => 'dvirdvadasha',
            default => 'shadashtaka',
        };
    }

    /**
     * @param  list<array{lord: string, start: string, end: string, antardashas: list<array{lord: string, start: string, end: string}>}>  $mahadashas
     * @return list<array{mahadasha_lord: string, lord: string, start: string, end: string, text: string}>
     */
    private static function upcoming(ChartContext $chart, array $mahadashas, int $mahadashaIndex, int $antardashaIndex): array
    {
        $upcoming = [];
        $m = $mahadashaIndex;
        $a = $antardashaIndex + 1;

        while (count($upcoming) < self::UPCOMING_COUNT && isset($mahadashas[$m])) {
            if (! isset($mahadashas[$m]['antardashas'][$a])) {
                $m++;
                $a = 0;

                continue;
            }

            $period = $mahadashas[$m]['antardashas'][$a];
            $houses = array_unique(array_filter([...$chart->housesRuledBy($period['lord']), $chart->houseOf($period['lord'])]));
            sort($houses);

            $upcoming[] = [
                'mahadasha_lord' => $mahadashas[$m]['lord'],
                'lord' => $period['lord'],
                'start' => $period['start'],
                'end' => $period['end'],
                'text' => ucfirst(ReadingPhrases::PERIOD_THEME['en'][$period['lord']])
                    .($houses === [] ? '' : ', touching your '.LifeAreaAnalyzer::listJoin(array_map(fn (int $house) => self::areaPhrase($house), $houses))).'.',
            ];
            $a++;
        }

        return $upcoming;
    }

    private static function areaPhrase(int $house): string
    {
        return Ordinal::suffix($house).' house ('.strtolower(LifeAreaTemplates::AREAS['en'][$house]['title']).')';
    }

    private static function formatDate(string $date): string
    {
        return CarbonImmutable::parse($date)->format('M Y');
    }
}
