<?php

namespace App\Services\Astrology\YearlyForecast;

use App\Services\Astrology\ChartAssembler;
use App\Services\Astrology\HouseLords;
use App\Services\Astrology\JulianDay;
use App\Services\Astrology\PlaceLookup;
use App\Services\Astrology\PlanetaryDignity;
use App\Services\Astrology\ZodiacSigns;
use Carbon\CarbonImmutable;

/**
 * Full Tajika Varshphal: casts a solar-return chart for the requested
 * year and derives the classical year-markers from it — Muntha, Varshesh
 * (year lord), and Saham (sensitive points). See TransitForecast for the
 * lighter-weight alternative this sits alongside.
 */
class VarshphalCalculator
{
    /**
     * @param  array{houses: list<array{number: int, sign: string, planets: list<string>}>, planetary_positions: list<array{name: string, sign: string, degree: string, longitude: float}>}  $natalChart
     * @param  array{dob: string, time: string, place: string}  $input
     */
    public static function forYear(array $natalChart, array $input, int $year): array
    {
        $location = PlaceLookup::resolve($input['place']);
        $birthMoment = CarbonImmutable::parse("{$input['dob']} {$input['time']}", $location['timezone']);
        $natalSunLongitude = collect($natalChart['planetary_positions'])->firstWhere('name', 'Sun')['longitude'];

        $returnMoment = SolarReturn::find($natalSunLongitude, $year, $birthMoment);
        $returnJulianDay = JulianDay::fromUtc($returnMoment->utc());
        $returnChart = ChartAssembler::assemble($returnJulianDay, $location['latitude'], $location['longitude'], 'vedic');

        $muntha = self::muntha($natalChart['houses'][0]['sign'], $year, $birthMoment);
        $varshesh = self::varshesh($muntha, $returnChart);
        $isDayBirth = self::isDayBirth($returnChart);
        $sahams = Saham::compute($returnChart['ascendant_longitude'], $returnChart['chart_longitudes'], $isDayBirth);

        return [
            'year' => $year,
            'solar_return_moment' => $returnMoment->toIso8601String(),
            'ascendant' => $returnChart['ascendant'],
            'planetary_positions' => $returnChart['planetary_positions'],
            'houses' => $returnChart['houses'],
            'muntha' => $muntha,
            'varshesh' => $varshesh,
            'is_day_birth' => $isDayBirth,
            'sahams' => $sahams,
        ];
    }

    /**
     * @return array{sign: string, lord: string}
     */
    private static function muntha(string $natalAscendantSign, int $year, CarbonImmutable $birthMoment): array
    {
        // The Muntha advances one sign per completed year of life; the
        // solar return in $year marks the start of the (year - birthYear)th
        // completed year.
        $completedYears = $year - $birthMoment->year;
        $natalIndex = array_search($natalAscendantSign, ZodiacSigns::NAMES, true);
        $munthaIndex = ($natalIndex + $completedYears) % 12;
        $sign = ZodiacSigns::NAMES[$munthaIndex];

        return ['sign' => $sign, 'lord' => HouseLords::SIGN_RULERS[$sign]];
    }

    /**
     * Simplified Varshesh (year lord) selection: the strongest by
     * sign-dignity among the Muntha lord, the return chart's ascendant
     * lord, and its Moon-sign lord — own-sign > exalted > neutral >
     * debilitated. Full classical Panchadhikari is a 5-fold contest across
     * positional, directional, temporal, natural, and aspectual strength;
     * this is a deliberately smaller, documented stand-in for it.
     *
     * @param  array{sign: string, lord: string}  $muntha
     * @param  array{houses: list<array{number: int, sign: string, planets: list<string>}>, planetary_positions: list<array{name: string, sign: string, degree: string, longitude: float}>}  $returnChart
     * @return array{lord: string, candidates: list<string>}
     */
    private static function varshesh(array $muntha, array $returnChart): array
    {
        $ascendantLord = HouseLords::lordOfHouse(1, $returnChart['houses']);
        $moonSign = collect($returnChart['planetary_positions'])->firstWhere('name', 'Moon')['sign'];
        $moonLord = HouseLords::SIGN_RULERS[$moonSign];

        $candidates = array_values(array_unique([$muntha['lord'], $ascendantLord, $moonLord]));

        $best = $candidates[0];
        $bestScore = -1;

        foreach ($candidates as $candidate) {
            $sign = HouseLords::signOfPlanet($candidate, $returnChart['houses']);
            $score = self::dignityScore($candidate, $sign);

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $candidate;
            }
        }

        return ['lord' => $best, 'candidates' => $candidates];
    }

    private static function dignityScore(string $planet, ?string $sign): int
    {
        if ($sign === null) {
            return 1;
        }

        return match (true) {
            PlanetaryDignity::isOwnSign($planet, $sign) => 4,
            PlanetaryDignity::isExalted($planet, $sign) => 3,
            PlanetaryDignity::isDebilitated($planet, $sign) => 0,
            default => 2,
        };
    }

    /**
     * Whether the Sun is above the horizon in the return chart — houses
     * 7-12 (ascending from the descendant toward the midheaven and back to
     * the ascendant) are the visible/upper half under the whole-sign
     * approximation this engine uses throughout.
     *
     * @param  array{houses: list<array{number: int, sign: string, planets: list<string>}>}  $returnChart
     */
    private static function isDayBirth(array $returnChart): bool
    {
        $sunHouse = HouseLords::houseContainingPlanet('Sun', $returnChart['houses']);

        return $sunHouse !== null && $sunHouse >= 7;
    }
}
