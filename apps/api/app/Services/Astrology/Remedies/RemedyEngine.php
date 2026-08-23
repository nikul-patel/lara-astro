<?php

namespace App\Services\Astrology\Remedies;

use App\Services\Astrology\AfflictionDetector;

/**
 * Generates remedy suggestions for every afflicted planet in the chart
 * (see AfflictionDetector). Every entry carries the same caution note —
 * this content is guidance, not professional advice, and gemstones in
 * particular should never be recommended without that caveat attached.
 */
class RemedyEngine
{
    private const PLANETS = ['Sun', 'Moon', 'Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn', 'Rahu', 'Ketu'];

    public const CAUTION_NOTE = 'This is a traditional suggestion offered for guidance only, not medical, legal, or financial advice — consult a qualified professional (and a certified gemologist before wearing any gemstone) before acting on it.';

    /**
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     * @return list<array{planet: string, afflictions: list<string>, gemstone: string, mantra: string, donation: string, fasting_day: string, caution_note: string}>
     */
    public static function generate(array $houses, array $planetLongitudes): array
    {
        $remedies = [];

        foreach (self::PLANETS as $planet) {
            $afflictions = AfflictionDetector::afflictions($planet, $houses, $planetLongitudes);

            if ($afflictions === []) {
                continue;
            }

            $remedies[] = [
                'planet' => $planet,
                'afflictions' => $afflictions,
                ...PlanetRemedies::REMEDIES[$planet],
                'caution_note' => self::CAUTION_NOTE,
            ];
        }

        return $remedies;
    }
}
