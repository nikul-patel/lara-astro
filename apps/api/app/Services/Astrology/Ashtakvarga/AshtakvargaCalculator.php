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
 *
 * Prastharashtakvarga ("spread-out Ashtakvarga", #85) is the same
 * calculation one level less summed: each contributor's individual 0/1
 * bindu per sign, kept instead of being discarded once added into
 * Bhinnashtakavarga's per-sign total — i.e. `prastharashtakvarga[subject]
 * [contributor][sign]` sums (over contributor) to exactly
 * `bhinnashtakavarga[subject][sign]`, which sums (over subject) to exactly
 * `sarvashtakavarga[sign]`. No new classical rule beyond what
 * Bhinnashtakavarga already uses — purely keeping an intermediate value
 * this method was already computing.
 */
class AshtakvargaCalculator
{
    /**
     * @param  list<array{name: string, sign: string}>  $planetaryPositions  Must include the 7 classical planets (extra entries like Rahu/Ketu are ignored — they aren't Ashtakvarga contributors).
     * @return array{
     *     bhinnashtakavarga: array<string, array<string, int>>,
     *     sarvashtakavarga: array<string, int>,
     *     prastharashtakvarga: array<string, array<string, array<string, int>>>,
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
        $prastharashtakvarga = [];
        $sarvashtakavarga = array_fill_keys(ZodiacSigns::NAMES, 0);

        foreach (BinduTables::SUBJECT_PLANETS as $subject) {
            $signTotals = array_fill_keys(ZodiacSigns::NAMES, 0);
            $contributorBindus = [];

            foreach (BinduTables::TABLES[$subject] as $contributor => $houses) {
                $contributorSign = $contributorSigns[$contributor];
                $bindus = array_fill_keys(ZodiacSigns::NAMES, 0);

                foreach (ZodiacSigns::NAMES as $sign) {
                    if (in_array(ZodiacSigns::offset($contributorSign, $sign), $houses, true)) {
                        $signTotals[$sign]++;
                        $bindus[$sign] = 1;
                    }
                }

                $contributorBindus[$contributor] = $bindus;
            }

            $bhinnashtakavarga[$subject] = $signTotals;
            $prastharashtakvarga[$subject] = $contributorBindus;

            foreach ($signTotals as $sign => $points) {
                $sarvashtakavarga[$sign] += $points;
            }
        }

        return [
            'bhinnashtakavarga' => $bhinnashtakavarga,
            'sarvashtakavarga' => $sarvashtakavarga,
            'prastharashtakvarga' => $prastharashtakvarga,
        ];
    }
}
