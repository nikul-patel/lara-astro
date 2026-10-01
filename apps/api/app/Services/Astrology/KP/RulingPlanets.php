<?php

namespace App\Services\Astrology\KP;

use App\Services\Astrology\HouseLords;
use App\Services\Astrology\Panchang\Vaar;

/**
 * KP (Krishnamurti Paddhati) Ruling Planets: the 7 planetary influences
 * considered active for a chart — the civil weekday's lord, the
 * Ascendant's sign/star(nakshatra)/sub lord, and the Moon's sign/star/sub
 * lord. Pure composition of already-computed values (the weekday lord from
 * {@see Vaar}, sign lords from
 * {@see HouseLords}, star/sub lords from {@see SubLord::forLongitude()})
 * — no new classical rule.
 */
class RulingPlanets
{
    /**
     * @param  array{nakshatra_lord: string, sub_lord: string}  $ascendantSubLord  BirthChartCalculator's `kp.ascendant`.
     * @param  array{nakshatra_lord: string, sub_lord: string}  $moonSubLord  BirthChartCalculator's `kp.sub_lords['Moon']`.
     * @return array{day_lord: string, ascendant_sign_lord: string, ascendant_star_lord: string, ascendant_sub_lord: string, moon_sign_lord: string, moon_star_lord: string, moon_sub_lord: string}
     */
    public static function compute(
        string $dayLord,
        string $ascendantSign,
        array $ascendantSubLord,
        string $moonSign,
        array $moonSubLord
    ): array {
        return [
            'day_lord' => $dayLord,
            'ascendant_sign_lord' => HouseLords::SIGN_RULERS[$ascendantSign],
            'ascendant_star_lord' => $ascendantSubLord['nakshatra_lord'],
            'ascendant_sub_lord' => $ascendantSubLord['sub_lord'],
            'moon_sign_lord' => HouseLords::SIGN_RULERS[$moonSign],
            'moon_star_lord' => $moonSubLord['nakshatra_lord'],
            'moon_sub_lord' => $moonSubLord['sub_lord'],
        ];
    }
}
