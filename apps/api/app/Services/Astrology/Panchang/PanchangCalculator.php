<?php

namespace App\Services\Astrology\Panchang;

use App\Services\Astrology\AstroMath;
use App\Services\Astrology\Ayanamsa;
use App\Services\Astrology\JulianDay;
use App\Services\Astrology\MoonPosition;
use App\Services\Astrology\Nakshatra;
use App\Services\Astrology\PlaceLookup;
use App\Services\Astrology\SunPosition;
use Carbon\CarbonImmutable;

/**
 * The daily Panchang ("five limbs"): Tithi, Vaar, Nakshatra, (Nitya) Yoga,
 * and Karana, plus sunrise/sunset for the place — the daily almanac every
 * Vedic astrology site/app publishes on its homepage.
 *
 * Tithi/Yoga/Karana/Nakshatra are computed from a single local-noon
 * snapshot of the sidereal Sun and Moon longitudes, the same "one
 * representative moment rather than tracking the exact transition instant"
 * simplification YearlyForecast\TransitForecast already uses — a tithi can
 * genuinely change between sunrise and midnight, and a Panchang purist
 * would report which limb is active "at sunrise" plus its end time; this
 * gives the day's dominant values, not a sunrise-anchored or
 * transition-aware reading.
 */
class PanchangCalculator
{
    /**
     * @param  array{date: string, place: string}  $input
     * @return array{
     *     date: string,
     *     location_matched: bool,
     *     vaar: array{name: string, lord: string},
     *     tithi: array{number: int, name: string, paksha: string},
     *     nakshatra: array{index: int, name: string, lord: string, pada: int},
     *     yoga: array{index: int, name: string},
     *     karana: string,
     *     sunrise: ?string,
     *     sunset: ?string,
     * }
     */
    public static function calculate(array $input): array
    {
        $location = PlaceLookup::resolve($input['place']);
        $localMidnight = CarbonImmutable::parse($input['date'], $location['timezone'])->startOfDay();

        $noonJulianDay = JulianDay::fromUtc($localMidnight->setTime(12, 0)->utc());
        $ayanamsa = Ayanamsa::lahiri($noonJulianDay);

        $sunLongitude = AstroMath::normalizeDegrees(SunPosition::apparentLongitude($noonJulianDay) - $ayanamsa);
        $moonLongitude = AstroMath::normalizeDegrees(MoonPosition::apparentLongitude($noonJulianDay) - $ayanamsa);

        $sunriseSunset = SunriseSunset::forDate($localMidnight, $location['latitude'], $location['longitude']);

        return [
            'date' => $localMidnight->toDateString(),
            'location_matched' => $location['matched'],
            'vaar' => Vaar::forDate($localMidnight),
            'tithi' => Tithi::forLongitudes($sunLongitude, $moonLongitude),
            'nakshatra' => Nakshatra::forLongitude($moonLongitude),
            'yoga' => NityaYoga::forLongitudes($sunLongitude, $moonLongitude),
            'karana' => Karana::forLongitudes($sunLongitude, $moonLongitude),
            'sunrise' => $sunriseSunset['sunrise'],
            'sunset' => $sunriseSunset['sunset'],
        ];
    }
}
