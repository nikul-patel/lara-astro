<?php

namespace App\Services\Astrology\Shadbala;

use App\Services\Astrology\AstroMath;
use App\Services\Astrology\Panchang\SunriseSunset;
use App\Services\Astrology\Panchang\Tithi;
use App\Services\Astrology\Panchang\Vaar;
use Carbon\CarbonImmutable;

/**
 * Kala Bala ("temporal strength"): strength a planet draws from the
 * moment of birth itself — time of day, lunar phase, and calendrical
 * lordships. This engine implements the sub-components that rest on
 * verifiable, unambiguous facts (clock time, sunrise/sunset, lunar
 * elongation, weekday, solar declination): Nathonnatha, Paksha, Tribhaga,
 * Vara, Hora, Ayana, and Yuddha Bala.
 *
 * Deliberately excluded: Abda Bala and Masa Bala (the year- and
 * month-lord components). Classical texts compute these from an
 * "Ahargana" day-count since a Kali Yuga epoch, using a weekday-advances-
 * by-2-per-month/by-3-per-year shortcut — but sources disagree on the
 * exact epoch anchor, and this environment has no way to verify one
 * against an authoritative reference. Rather than present an unverified
 * epoch's numbers as classical fact, they're left out; Vara and Hora
 * Bala (the other two calendrical components, resting only on the
 * independently-verifiable birth weekday and sunrise-anchored planetary
 * hour) are implemented in full. This narrows Kala Bala's maximum by 45
 * Virupas (15 Abda + 30 Masa) out of several hundred — the same
 * "document the gap, don't fabricate the number" policy this engine
 * applies elsewhere (Avkahada's Paya panel, Jaimini's Swamsa ambiguity).
 *
 * Vara Bala uses the CIVIL calendar weekday (same convention as
 * Panchang\Vaar/PanchangCalculator elsewhere in this codebase — a
 * midnight-to-midnight day). Hora Bala's planetary-hour sequence is
 * necessarily sunrise-anchored (hours aren't a midnight-to-midnight
 * concept), so for a birth before today's sunrise it correctly uses
 * YESTERDAY's weekday as the hour sequence's starting lord — the
 * Panchang day that began at yesterday's sunrise hasn't ended yet. These
 * two bala's "which weekday" can therefore differ for an early-morning
 * birth; that's intentional, not an inconsistency.
 */
class KalaBala
{
    /** Chaldean descending order the planetary-hour sequence cycles through, starting from each day's own weekday lord. */
    private const CHALDEAN_ORDER = ['Saturn', 'Jupiter', 'Mars', 'Sun', 'Venus', 'Mercury', 'Moon'];

    private const DAY_STRONG = ['Sun', 'Jupiter', 'Venus'];

    private const NIGHT_STRONG = ['Moon', 'Mars', 'Saturn'];

    /**
     * Nathonnatha Bala: day-strong planets (Sun, Jupiter, Venus) peak at
     * local noon and bottom out at midnight; night-strong planets (Moon,
     * Mars, Saturn) do the reverse; Mercury is always strong. Uses civil
     * clock time (midnight/noon) rather than true solar midnight/noon —
     * within this engine's stated minute-level tolerance.
     *
     * @return array<string, float>
     */
    public static function nathonnathaBala(CarbonImmutable $localBirthMoment): array
    {
        $hour = $localBirthMoment->hour + $localBirthMoment->minute / 60 + $localBirthMoment->second / 3600;
        $dayPosition = $hour < 12 ? $hour * 5 : (24 - $hour) * 5; // 0 at midnight, 60 at noon

        $values = ['Mercury' => 60.0];
        foreach (self::DAY_STRONG as $planet) {
            $values[$planet] = round($dayPosition, 2);
        }
        foreach (self::NIGHT_STRONG as $planet) {
            $values[$planet] = round(60 - $dayPosition, 2);
        }

        return $values;
    }

    /**
     * Paksha Bala: strength from the Moon-Sun elongation, maximum (60) at
     * full moon, minimum (0) at new moon, for benefics (Mercury, Jupiter,
     * Venus, and the Moon when waxing); the reverse for malefics (Sun,
     * Mars, Saturn, and the Moon when waning). The Moon itself always
     * scores double, classically, since Paksha strength is fundamentally
     * about the Moon's own phase.
     *
     * @return array<string, float>
     */
    public static function pakshaBala(float $sunLongitude, float $moonLongitude): array
    {
        $elongation = AstroMath::normalizeDegrees($moonLongitude - $sunLongitude);
        if ($elongation > 180) {
            $elongation = 360 - $elongation;
        }
        $pb = $elongation / 3;

        $waxing = Tithi::forLongitudes($sunLongitude, $moonLongitude)['paksha'] === 'Shukla';

        return [
            'Sun' => round(60 - $pb, 2),
            'Moon' => round(2 * $pb, 2),
            'Mars' => round(60 - $pb, 2),
            'Mercury' => round($pb, 2),
            'Jupiter' => round($pb, 2),
            'Venus' => round($pb, 2),
            'Saturn' => round(60 - $pb, 2),
        ];
    }

    /**
     * Tribhaga Bala: the day (sunrise-sunset) and night (sunset-sunrise)
     * are each divided into 3 equal parts; the lord of the part birth
     * falls in scores 60 (day parts: Mercury, Sun, Saturn in order; night
     * parts: Moon, Venus, Mars in order). Jupiter always scores 60
     * regardless — classically the universal lord of every Tribhaga.
     *
     * @return array<string, float>
     */
    public static function tribhagaBala(CarbonImmutable $localBirthMoment, float $latitude, float $longitude): array
    {
        $values = array_fill_keys(['Sun', 'Moon', 'Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn'], 0.0);
        $values['Jupiter'] = 60.0;

        $segment = self::segment($localBirthMoment, $latitude, $longitude);
        if ($segment === null) {
            return $values;
        }

        $fraction = $segment['start']->diffInSeconds($localBirthMoment) / $segment['start']->diffInSeconds($segment['end']);
        $part = min(2, (int) floor($fraction * 3));

        $lords = $segment['is_day'] ? ['Mercury', 'Sun', 'Saturn'] : ['Moon', 'Venus', 'Mars'];
        $values[$lords[$part]] = 60.0;

        return $values;
    }

    /**
     * Vara Bala: 45 Virupas to the lord of the birth's civil weekday.
     *
     * @return array<string, float>
     */
    public static function varaBala(CarbonImmutable $localBirthMoment): array
    {
        $values = array_fill_keys(['Sun', 'Moon', 'Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn'], 0.0);
        $values[Vaar::forDate($localBirthMoment)['lord']] = 45.0;

        return $values;
    }

    /**
     * Hora Bala: 60 Virupas to the lord of the planetary hour (1/12th of
     * the day or night) birth falls in — the Chaldean sequence, starting
     * from the Panchang day's own weekday lord at sunrise.
     *
     * @return array<string, float>
     */
    public static function horaBala(CarbonImmutable $localBirthMoment, float $latitude, float $longitude): array
    {
        $values = array_fill_keys(['Sun', 'Moon', 'Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn'], 0.0);

        $segment = self::segment($localBirthMoment, $latitude, $longitude);
        if ($segment === null) {
            return $values;
        }

        $fraction = $segment['start']->diffInSeconds($localBirthMoment) / $segment['start']->diffInSeconds($segment['end']);
        $horaWithinSegment = min(11, (int) floor($fraction * 12));
        $horaIndex = $segment['is_day'] ? $horaWithinSegment : 12 + $horaWithinSegment;

        $weekdayLord = Vaar::forDate($segment['panchang_day'])['lord'];
        $startIndex = array_search($weekdayLord, self::CHALDEAN_ORDER, true);
        $horaLord = self::CHALDEAN_ORDER[($startIndex + $horaIndex) % 7];

        $values[$horaLord] = 60.0;

        return $values;
    }

    /**
     * Ayana Bala: strength from each planet's own declination (its
     * position north/south of the celestial equator), scaled so the
     * Sun's maximum obliquity-bound declination (≈24°) maps to a full 60
     * Virupas. The Sun's own score is doubled, classically, since the
     * Sun's declination is what defines Uttarayana/Dakshinayana (the
     * "ayana" itself) for every other planet.
     *
     * Uses each planet's TROPICAL longitude (declination is a tropical-
     * frame quantity, meaningless in the sidereal zodiac), recomputed
     * here rather than threading tropical longitudes through
     * BirthChartCalculator's sidereal-only contract, and assumes zero
     * ecliptic latitude — the same simplification this engine already
     * makes for every other planet (see ChartAssembler).
     *
     * @param  array<string, float>  $tropicalLongitudes
     * @return array<string, float>
     */
    public static function ayanaBala(array $tropicalLongitudes, float $obliquity): array
    {
        $values = [];
        foreach (['Sun', 'Moon', 'Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn'] as $planet) {
            $declination = AstroMath::atan2Deg(
                AstroMath::sinDeg($tropicalLongitudes[$planet]) * AstroMath::sinDeg($obliquity),
                sqrt(1 - (AstroMath::sinDeg($tropicalLongitudes[$planet]) * AstroMath::sinDeg($obliquity)) ** 2)
            );

            $ayana = (24 + $declination) * 1.25;
            if ($planet === 'Sun') {
                $ayana *= 2;
            }

            $values[$planet] = round(max(0, min($planet === 'Sun' ? 120 : 60, $ayana)), 2);
        }

        return $values;
    }

    /**
     * Yuddha Bala ("planetary war"): when two of the 5 star planets
     * (Mars, Mercury, Jupiter, Venus, Saturn — Sun/Moon/the nodes never
     * fight) sit within 1° of each other, the one with more combined
     * Sthana+Dig+Kala(-so-far)+Chesta+Naisargika strength "wins",
     * scoring +(difference/disc-diameter-difference); the loser scores
     * the same amount negative. Disc-diameter ratios are the classical
     * relative values reproduced in the PyJHora reference (see
     * SthanaBala's doc comment for the citation) — Mercury smallest,
     * Jupiter largest, matching every classical description of the two.
     *
     * @param  array<string, float>  $chartLongitudes
     * @param  array<string, float>  $otherBalaTotals  Each star planet's Sthana+Dig+Kala(minus Yuddha)+Chesta+Naisargika sum.
     * @return array<string, float>
     */
    public static function yuddhaBala(array $chartLongitudes, array $otherBalaTotals): array
    {
        $starPlanets = ['Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn'];
        $discDiameter = ['Mars' => 9.4, 'Mercury' => 6.6, 'Jupiter' => 190.4, 'Venus' => 16.6, 'Saturn' => 158.0];

        $values = array_fill_keys($starPlanets, 0.0);

        foreach ($starPlanets as $i => $a) {
            foreach ($starPlanets as $j => $b) {
                if ($j <= $i) {
                    continue;
                }

                $distance = abs(AstroMath::normalizeDegrees($chartLongitudes[$a] - $chartLongitudes[$b]));
                if ($distance > 180) {
                    $distance = 360 - $distance;
                }
                if ($distance > 1.0) {
                    continue;
                }

                $diff = abs($otherBalaTotals[$a] - $otherBalaTotals[$b]);
                $diameterDiff = abs($discDiameter[$a] - $discDiameter[$b]);
                $yuddhaValue = $diameterDiff > 0 ? round($diff / $diameterDiff, 2) : 0.0;

                $winner = $otherBalaTotals[$a] >= $otherBalaTotals[$b] ? $a : $b;
                $loser = $winner === $a ? $b : $a;
                $values[$winner] = $yuddhaValue;
                $values[$loser] = -$yuddhaValue;
            }
        }

        return $values;
    }

    /**
     * @return ?array{start: CarbonImmutable, end: CarbonImmutable, is_day: bool, panchang_day: CarbonImmutable}
     */
    private static function segment(CarbonImmutable $localBirthMoment, float $latitude, float $longitude): ?array
    {
        $today = $localBirthMoment->startOfDay();
        $todayMoments = SunriseSunset::moments($today, $latitude, $longitude);

        if ($todayMoments['sunrise'] === null || $todayMoments['sunset'] === null) {
            return null; // Polar circle: sun never rises/sets — out of this engine's scope.
        }

        $todaySunrise = $todayMoments['sunrise']->setTimezone($localBirthMoment->timezone);
        $todaySunset = $todayMoments['sunset']->setTimezone($localBirthMoment->timezone);

        if ($localBirthMoment->gte($todaySunrise) && $localBirthMoment->lt($todaySunset)) {
            return ['start' => $todaySunrise, 'end' => $todaySunset, 'is_day' => true, 'panchang_day' => $today];
        }

        if ($localBirthMoment->gte($todaySunset)) {
            $tomorrowMoments = SunriseSunset::moments($today->addDay(), $latitude, $longitude);
            if ($tomorrowMoments['sunrise'] === null) {
                return null;
            }

            return [
                'start' => $todaySunset,
                'end' => $tomorrowMoments['sunrise']->setTimezone($localBirthMoment->timezone),
                'is_day' => false,
                'panchang_day' => $today,
            ];
        }

        // Before today's sunrise: still inside the Panchang day that began at yesterday's sunrise.
        $yesterday = $today->subDay();
        $yesterdayMoments = SunriseSunset::moments($yesterday, $latitude, $longitude);
        if ($yesterdayMoments['sunset'] === null) {
            return null;
        }

        return [
            'start' => $yesterdayMoments['sunset']->setTimezone($localBirthMoment->timezone),
            'end' => $todaySunrise,
            'is_day' => false,
            'panchang_day' => $yesterday,
        ];
    }
}
