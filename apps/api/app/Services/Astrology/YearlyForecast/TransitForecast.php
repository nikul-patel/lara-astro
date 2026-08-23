<?php

namespace App\Services\Astrology\YearlyForecast;

use App\Services\Astrology\AstroMath;
use App\Services\Astrology\Ayanamsa;
use App\Services\Astrology\JulianDay;
use App\Services\Astrology\PlanetaryElements;
use App\Services\Astrology\ZodiacSigns;
use Carbon\CarbonImmutable;

/**
 * A lightweight "year ahead" forecast: where transiting Jupiter and Saturn
 * sit relative to the natal Moon and Ascendant (Gochara), plus which
 * Vimshottari Mahadasha/Antardasha governs the requested year — no solar
 * return, Muntha, or Saham involved (see VarshphalCalculator for the full
 * traditional system).
 *
 * Jupiter/Saturn move slowly enough (roughly 1 sign per year and every 2.5
 * years respectively) that a single snapshot at the middle of the target
 * year (July 1, noon UTC) is a reasonable stand-in for "this year's
 * transit sign" — a deliberate simplification against tracking the exact
 * sign-ingress dates within the year, which would need denser sampling.
 */
class TransitForecast
{
    /**
     * @param  array{houses: list<array{number: int, sign: string, planets: list<string>}>, planetary_positions: list<array{name: string, sign: string, degree: string, longitude: float}>, dasha?: array{mahadasha: list<array{lord: string, start: string, end: string, antardashas: list<array{lord: string, start: string, end: string}>}>}}  $natalChart
     * @return array{year: int, jupiter_transit: array{sign: string, house_from_ascendant: int, house_from_moon: int}, saturn_transit: array{sign: string, house_from_ascendant: int, house_from_moon: int}, governing_dasha: list<array{mahadasha_lord: string, antardasha_lord: string, start: string, end: string}>}
     */
    public static function forYear(array $natalChart, int $year): array
    {
        $ascendantSign = $natalChart['houses'][0]['sign'];
        $moonSign = collect($natalChart['planetary_positions'])->firstWhere('name', 'Moon')['sign'];

        $julianDay = JulianDay::fromUtc(CarbonImmutable::create($year, 7, 1, 12, 0, 0, 'UTC'));
        $ayanamsa = Ayanamsa::lahiri($julianDay);

        $jupiterSign = ZodiacSigns::forLongitude(
            AstroMath::normalizeDegrees(PlanetaryElements::geocentricLongitude('jupiter', $julianDay) - $ayanamsa)
        );
        $saturnSign = ZodiacSigns::forLongitude(
            AstroMath::normalizeDegrees(PlanetaryElements::geocentricLongitude('saturn', $julianDay) - $ayanamsa)
        );

        return [
            'year' => $year,
            'jupiter_transit' => [
                'sign' => $jupiterSign,
                'house_from_ascendant' => self::houseOffset($ascendantSign, $jupiterSign),
                'house_from_moon' => self::houseOffset($moonSign, $jupiterSign),
            ],
            'saturn_transit' => [
                'sign' => $saturnSign,
                'house_from_ascendant' => self::houseOffset($ascendantSign, $saturnSign),
                'house_from_moon' => self::houseOffset($moonSign, $saturnSign),
            ],
            'governing_dasha' => self::governingDasha($natalChart['dasha']['mahadasha'] ?? [], $year),
        ];
    }

    private static function houseOffset(string $fromSign, string $toSign): int
    {
        $fromIndex = array_search($fromSign, ZodiacSigns::NAMES, true);
        $toIndex = array_search($toSign, ZodiacSigns::NAMES, true);

        return (($toIndex - $fromIndex + 12) % 12) + 1;
    }

    /**
     * Every Antardasha period (across every Mahadasha) whose span overlaps
     * the requested calendar year — typically 1-2 entries, occasionally 3
     * near a Mahadasha boundary.
     *
     * @param  list<array{lord: string, start: string, end: string, antardashas: list<array{lord: string, start: string, end: string}>}>  $mahadashas
     * @return list<array{mahadasha_lord: string, antardasha_lord: string, start: string, end: string}>
     */
    private static function governingDasha(array $mahadashas, int $year): array
    {
        $yearStart = CarbonImmutable::create($year, 1, 1, 0, 0, 0, 'UTC');
        $yearEnd = CarbonImmutable::create($year, 12, 31, 23, 59, 59, 'UTC');

        $active = [];

        foreach ($mahadashas as $mahadasha) {
            if (CarbonImmutable::parse($mahadasha['end'])->lt($yearStart) || CarbonImmutable::parse($mahadasha['start'])->gt($yearEnd)) {
                continue;
            }

            foreach ($mahadasha['antardashas'] as $antardasha) {
                if (CarbonImmutable::parse($antardasha['end'])->lt($yearStart) || CarbonImmutable::parse($antardasha['start'])->gt($yearEnd)) {
                    continue;
                }

                $active[] = [
                    'mahadasha_lord' => $mahadasha['lord'],
                    'antardasha_lord' => $antardasha['lord'],
                    'start' => $antardasha['start'],
                    'end' => $antardasha['end'],
                ];
            }
        }

        return $active;
    }
}
