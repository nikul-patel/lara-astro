<?php

namespace Tests\Support;

use App\Services\Astrology\ZodiacSigns;

/**
 * Builds a minimal whole-sign houses array (the shape
 * Houses::wholeSignHouses() returns) for yoga-detector unit tests, without
 * needing a full BirthChartCalculator run through real ephemeris math.
 */
class YogaChartFixture
{
    /**
     * @param  array<string, int>  $planetHouses  planet name => house number (1-12)
     * @return list<array{number: int, sign: string, planets: list<string>}>
     */
    public static function houses(string $ascendantSign, array $planetHouses = []): array
    {
        $ascendantSignIndex = array_search($ascendantSign, ZodiacSigns::NAMES, true);

        $houses = [];
        for ($houseNumber = 1; $houseNumber <= 12; $houseNumber++) {
            $signIndex = ($ascendantSignIndex + $houseNumber - 1) % 12;

            $planetsInHouse = array_keys(array_filter(
                $planetHouses,
                fn (int $house) => $house === $houseNumber,
            ));

            $houses[] = [
                'number' => $houseNumber,
                'sign' => ZodiacSigns::NAMES[$signIndex],
                'planets' => $planetsInHouse,
            ];
        }

        return $houses;
    }
}
