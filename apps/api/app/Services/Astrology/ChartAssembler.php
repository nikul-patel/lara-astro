<?php

namespace App\Services\Astrology;

/**
 * Computes planetary positions, ascendant, and whole-sign houses for a
 * given moment and place — the core "cast a chart" step shared by
 * BirthChartCalculator (the natal chart) and
 * YearlyForecast\VarshphalCalculator (the solar-return chart). Extracted
 * so both calculators stay in sync on how a chart is assembled rather
 * than duplicating this logic.
 */
class ChartAssembler
{
    /**
     * @return array{
     *     chart_longitudes: array<string, float>,
     *     ascendant_longitude: float,
     *     ascendant: array{sign: string, degree: string},
     *     planetary_positions: list<array{name: string, sign: string, degree: string, longitude: float}>,
     *     houses: list<array{number: int, sign: string, planets: list<string>}>,
     * }
     */
    public static function assemble(float $julianDay, float $placeLatitude, float $placeLongitude, string $system): array
    {
        $tropicalLongitudes = [
            'Sun' => SunPosition::apparentLongitude($julianDay),
            'Moon' => MoonPosition::apparentLongitude($julianDay),
            'Mercury' => PlanetaryElements::geocentricLongitude('mercury', $julianDay),
            'Venus' => PlanetaryElements::geocentricLongitude('venus', $julianDay),
            'Mars' => PlanetaryElements::geocentricLongitude('mars', $julianDay),
            'Jupiter' => PlanetaryElements::geocentricLongitude('jupiter', $julianDay),
            'Saturn' => PlanetaryElements::geocentricLongitude('saturn', $julianDay),
            'Rahu' => LunarNodes::rahuLongitude($julianDay),
            'Ketu' => LunarNodes::ketuLongitude($julianDay),
        ];

        $ayanamsa = $system === 'vedic' ? Ayanamsa::lahiri($julianDay) : 0.0;

        $chartLongitudes = [];
        foreach ($tropicalLongitudes as $planet => $longitude) {
            $chartLongitudes[$planet] = AstroMath::normalizeDegrees($longitude - $ayanamsa);
        }

        $tropicalAscendant = Houses::ascendant($julianDay, $placeLatitude, $placeLongitude);
        $ascendantLongitude = AstroMath::normalizeDegrees($tropicalAscendant - $ayanamsa);

        $planetaryPositions = [];
        foreach ($chartLongitudes as $planet => $longitude) {
            $planetaryPositions[] = [
                'name' => $planet,
                'sign' => ZodiacSigns::forLongitude($longitude),
                'degree' => ZodiacSigns::formatDegreeInSign($longitude),
                'longitude' => round($longitude, 4),
            ];
        }

        return [
            'chart_longitudes' => $chartLongitudes,
            'ascendant_longitude' => $ascendantLongitude,
            'ascendant' => [
                'sign' => ZodiacSigns::forLongitude($ascendantLongitude),
                'degree' => ZodiacSigns::formatDegreeInSign($ascendantLongitude),
            ],
            'planetary_positions' => $planetaryPositions,
            'houses' => Houses::wholeSignHouses($ascendantLongitude, $chartLongitudes),
        ];
    }
}
