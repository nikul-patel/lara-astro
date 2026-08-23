<?php

namespace App\Services\Astrology;

use App\Services\Astrology\Yogas\DhanaYoga;
use App\Services\Astrology\Yogas\ForeignSettlementIndicators;
use App\Services\Astrology\Yogas\GajakesariYoga;
use App\Services\Astrology\Yogas\NeechaBhangaYoga;
use App\Services\Astrology\Yogas\RajYoga;
use App\Services\Astrology\Yogas\VipreetRajaYoga;

/**
 * Orchestrates every yoga detector in Services/Astrology/Yogas against a
 * computed whole-sign house chart, merging their results into one list.
 * Adding a new yoga means writing a new detector class (same
 * `detect(array $houses): array` shape) and registering it in DETECTORS
 * below — nothing else in this class needs to change.
 */
class YogaEngine
{
    private const DETECTORS = [
        RajYoga::class,
        DhanaYoga::class,
        GajakesariYoga::class,
        NeechaBhangaYoga::class,
        VipreetRajaYoga::class,
        ForeignSettlementIndicators::class,
    ];

    /**
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     * @return list<array{key: string, name: string, category: string, planets: list<string>, houses: list<int>, description: string}>
     */
    public static function detect(array $houses): array
    {
        $found = [];

        foreach (self::DETECTORS as $detector) {
            array_push($found, ...$detector::detect($houses));
        }

        return $found;
    }
}
