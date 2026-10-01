<?php

namespace App\Services\Astrology\Ashtakvarga;

use App\Services\Astrology\ZodiacSigns;

/**
 * Ashtakvarga ("eight-fold division"): a classical point-strength system
 * used to judge how favourable each sign is for each planet, and — via
 * Sarvashtakavarga's combined total — for assessing transit strength
 * generally. For each of the 7 classical planets' own Bhinnashtakavarga
 * ("divided Ashtakvarga"), each of 8 contributors (the 7 planets plus the
 * Ascendant) grants a bindu (point) to specific signs, counted as houses
 * from that contributor's own position — see {@see BinduTables} for the
 * fixed classical rule tables this reads from.
 */
class AshtakvargaCalculator
{
    /**
     * @param  list<array{name: string, sign: string}>  $planetaryPositions  Must include the 7 classical planets (extra entries like Rahu/Ketu are ignored — they aren't Ashtakvarga contributors).
     * @return array{
     *     bhinnashtakavarga: array<string, array<string, int>>,
     *     sarvashtakavarga: array<string, int>,
     * }
     */
    public static function calculate(array $planetaryPositions, string $ascendantSign): array
    {
        $contributorSigns = ['Ascendant' => $ascendantSign];
        foreach ($planetaryPositions as $planet) {
            if (in_array($planet['name'], BinduTables::CONTRIBUTORS, true)) {
                $contributorSigns[$planet['name']] = $planet['sign'];
            }
        }

        $bhinnashtakavarga = [];
        $sarvashtakavarga = array_fill_keys(ZodiacSigns::NAMES, 0);

        foreach (BinduTables::SUBJECT_PLANETS as $subject) {
            $signTotals = array_fill_keys(ZodiacSigns::NAMES, 0);

            foreach (BinduTables::TABLES[$subject] as $contributor => $houses) {
                $contributorSign = $contributorSigns[$contributor];

                foreach (ZodiacSigns::NAMES as $sign) {
                    if (in_array(ZodiacSigns::offset($contributorSign, $sign), $houses, true)) {
                        $signTotals[$sign]++;
                    }
                }
            }

            $bhinnashtakavarga[$subject] = $signTotals;

            foreach ($signTotals as $sign => $points) {
                $sarvashtakavarga[$sign] += $points;
            }
        }

        return [
            'bhinnashtakavarga' => $bhinnashtakavarga,
            'sarvashtakavarga' => $sarvashtakavarga,
        ];
    }
}
