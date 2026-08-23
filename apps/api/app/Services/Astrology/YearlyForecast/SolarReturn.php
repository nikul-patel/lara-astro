<?php

namespace App\Services\Astrology\YearlyForecast;

use App\Services\Astrology\AstroMath;
use App\Services\Astrology\Ayanamsa;
use App\Services\Astrology\JulianDay;
use App\Services\Astrology\SunPosition;
use Carbon\CarbonImmutable;

/**
 * Finds the exact moment in a given year the transiting sidereal Sun
 * returns to its natal longitude — the anchor moment for a Tajika
 * Varshphal chart. Solved by Newton-Raphson: the Sun's motion is close
 * enough to its mean rate (~0.9856°/day) that a same-month-and-day
 * starting guess converges to sub-hour precision within a couple of
 * iterations.
 */
class SolarReturn
{
    private const SUN_MEAN_MOTION_PER_DAY = 0.9856;

    private const MAX_ITERATIONS = 6;

    private const CONVERGENCE_THRESHOLD_DEGREES = 0.0001;

    public static function find(float $natalSunSiderealLongitude, int $year, CarbonImmutable $birthMoment): CarbonImmutable
    {
        $guess = $birthMoment->setDate($year, $birthMoment->month, min($birthMoment->day, CarbonImmutable::create($year, $birthMoment->month, 1)->daysInMonth));

        for ($i = 0; $i < self::MAX_ITERATIONS; $i++) {
            $julianDay = JulianDay::fromUtc($guess->utc());
            $ayanamsa = Ayanamsa::lahiri($julianDay);
            $currentLongitude = AstroMath::normalizeDegrees(SunPosition::apparentLongitude($julianDay) - $ayanamsa);

            $diff = self::signedDifference($currentLongitude, $natalSunSiderealLongitude);

            if (abs($diff) < self::CONVERGENCE_THRESHOLD_DEGREES) {
                break;
            }

            $guess = $guess->subRealSeconds(($diff / self::SUN_MEAN_MOTION_PER_DAY) * 86400);
        }

        return $guess;
    }

    /**
     * The signed angular difference $a - $b, normalized to (-180, 180] so
     * the Newton step always moves toward the nearer root rather than the
     * long way around the zodiac.
     */
    private static function signedDifference(float $a, float $b): float
    {
        $diff = AstroMath::normalizeDegrees($a - $b);

        return $diff > 180 ? $diff - 360 : $diff;
    }
}
