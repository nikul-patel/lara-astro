<?php

namespace App\Services\Astrology\Panchang;

use App\Services\Astrology\AstroMath;
use App\Services\Astrology\Houses;
use App\Services\Astrology\JulianDay;
use App\Services\Astrology\SunPosition;
use Carbon\CarbonImmutable;

/**
 * Sunrise/sunset for a given date and place, via the standard hour-angle
 * equation (Meeus ch. 15's sunrise/sunset method, simplified to one
 * iteration rather than his 2-3 iteration refinement — consistent with
 * this engine's stated low-precision tolerance, typically accurate to
 * within a minute or two, which is what every other class in this
 * namespace already targets).
 */
class SunriseSunset
{
    /**
     * Standard solar radius + atmospheric refraction correction applied to
     * every sunrise/sunset calculation (Meeus ch. 15): 50 arcminutes below
     * the geometric horizon.
     */
    private const ZENITH_OFFSET = 0.8333;

    /**
     * @return array{sunrise: ?string, sunset: ?string}
     */
    public static function forDate(CarbonImmutable $localMidnight, float $latitude, float $longitude): array
    {
        $moments = self::moments($localMidnight, $latitude, $longitude);

        return [
            'sunrise' => $moments['sunrise']?->setTimezone($localMidnight->timezone)->format('H:i'),
            'sunset' => $moments['sunset']?->setTimezone($localMidnight->timezone)->format('H:i'),
        ];
    }

    /**
     * Same calculation as {@see self::forDate()}, but returning the raw UTC
     * instants rather than formatted local time strings — what Shadbala's
     * Kala Bala components (Nathonnatha, Tribhaga, Hora Bala) need for
     * sub-minute day/night-length arithmetic rather than a display string.
     *
     * @return array{sunrise: ?CarbonImmutable, sunset: ?CarbonImmutable}
     */
    public static function moments(CarbonImmutable $localMidnight, float $latitude, float $longitude): array
    {
        $noonJulianDay = JulianDay::fromUtc($localMidnight->setTime(12, 0)->utc());
        $sunLongitude = SunPosition::apparentLongitude($noonJulianDay);
        $obliquity = Houses::obliquity($noonJulianDay);

        $declination = AstroMath::atan2Deg(
            AstroMath::sinDeg($sunLongitude) * AstroMath::sinDeg($obliquity),
            sqrt(1 - (AstroMath::sinDeg($sunLongitude) * AstroMath::sinDeg($obliquity)) ** 2)
        );

        $cosHourAngle = (AstroMath::sinDeg(-self::ZENITH_OFFSET) - AstroMath::sinDeg($latitude) * AstroMath::sinDeg($declination))
            / (AstroMath::cosDeg($latitude) * AstroMath::cosDeg($declination));

        // |cosHourAngle| > 1 means the sun never rises/sets that day (polar
        // circle) — out of scope for this engine's target audience, but
        // fail gracefully rather than returning NAN.
        if (abs($cosHourAngle) > 1) {
            return ['sunrise' => null, 'sunset' => null];
        }

        $hourAngle = rad2deg(acos($cosHourAngle));

        // Equation of time approximation, minutes (Meeus ch. 28, truncated
        // to its two dominant terms — the same "good enough for a
        // civil-time result, not arc-second precision" standard as the
        // rest of this engine).
        $t = JulianDay::centuriesSinceJ2000($noonJulianDay);
        $meanLongitude = AstroMath::normalizeDegrees(280.46646 + 36000.76983 * $t);

        // meanLongitude and sunLongitude are both normalized to [0, 360)
        // independently, so near either's 0°/360° crossing (the
        // equinoxes) their raw difference can read as ~360° instead of
        // the true few-degree gap — fold it to (-180, 180] before
        // converting to minutes, or the equation-of-time correction comes
        // out roughly 24 hours wrong and sunrise/sunset lands a full day
        // off near every equinox.
        $longitudeDelta = AstroMath::normalizeDegrees($meanLongitude - 0.0057183 - $sunLongitude + 180) - 180;
        $equationOfTimeMinutes = 4 * $longitudeDelta;

        $solarNoonUtcHours = 12 - $longitude / 15 - $equationOfTimeMinutes / 60;
        $sunriseUtcHours = $solarNoonUtcHours - $hourAngle / 15;
        $sunsetUtcHours = $solarNoonUtcHours + $hourAngle / 15;

        $sunriseUtc = $localMidnight->utc()->startOfDay()->addSeconds((int) round($sunriseUtcHours * 3600));
        $sunsetUtc = $localMidnight->utc()->startOfDay()->addSeconds((int) round($sunsetUtcHours * 3600));

        return ['sunrise' => $sunriseUtc, 'sunset' => $sunsetUtc];
    }
}
