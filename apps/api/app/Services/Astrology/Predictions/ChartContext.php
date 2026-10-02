<?php

namespace App\Services\Astrology\Predictions;

use App\Services\Astrology\Aspects;
use App\Services\Astrology\HouseLords;
use App\Services\Astrology\PlanetaryDignity;
use App\Services\Astrology\ZodiacSigns;

/**
 * The slice of a computed natal chart the detailed reading needs, with the
 * lookups every part of it repeats (who rules a house, who occupies or
 * aspects it, how dignified a planet is). Built either from
 * BirthChartCalculator's in-flight values or, via fromResult(), from a
 * saved chart's stored `result` JSON — the report generator uses the
 * latter so even charts saved before this reading existed get it.
 *
 * Sarvashtakavarga, the Mahadasha timeline and Shadbala are optional:
 * charts saved before those features shipped simply lack them, and every
 * consumer skips the factor rather than failing.
 */
class ChartContext
{
    /**
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     * @param  array<string, float>  $longitudes  Planet name => sidereal longitude.
     * @param  array<string, int>|null  $sarvashtakavarga  Sign => bindus.
     * @param  list<array{lord: string, start: string, end: string, antardashas: list<array{lord: string, start: string, end: string}>}>|null  $mahadashas
     * @param  array<string, mixed>|null  $shadbala
     */
    public function __construct(
        public readonly array $houses,
        public readonly array $longitudes,
        public readonly string $ascendantSign,
        public readonly ?array $sarvashtakavarga = null,
        public readonly ?array $mahadashas = null,
        public readonly ?array $shadbala = null,
    ) {}

    /**
     * @param  array<string, mixed>  $result  A BirthChartCalculator result (Vedic system).
     */
    public static function fromResult(array $result): self
    {
        $longitudes = [];
        foreach ($result['planetary_positions'] as $position) {
            $longitudes[$position['name']] = (float) $position['longitude'];
        }

        return new self(
            $result['houses'],
            $longitudes,
            $result['ascendant']['sign'],
            $result['ashtakvarga']['sarvashtakavarga'] ?? null,
            $result['dasha']['mahadasha'] ?? null,
            $result['shadbala'] ?? null,
        );
    }

    public function lordOf(int $house): string
    {
        return HouseLords::lordOfHouse($house, $this->houses);
    }

    public function houseOf(string $planet): ?int
    {
        return HouseLords::houseContainingPlanet($planet, $this->houses);
    }

    public function signOf(string $planet): ?string
    {
        return HouseLords::signOfPlanet($planet, $this->houses);
    }

    public function signOfHouse(int $house): string
    {
        return collect($this->houses)->firstWhere('number', $house)['sign'];
    }

    /**
     * @return list<string>
     */
    public function occupants(int $house): array
    {
        return collect($this->houses)->firstWhere('number', $house)['planets'];
    }

    /**
     * Houses ruled by a planet, ascending. Rahu and Ketu rule none under
     * the traditional Parashari rulerships HouseLords uses.
     *
     * @return list<int>
     */
    public function housesRuledBy(string $planet): array
    {
        return collect($this->houses)
            ->filter(fn (array $house) => HouseLords::SIGN_RULERS[$house['sign']] === $planet)
            ->pluck('number')
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Planets whose graha drishti falls on the given house (occupants are
     * excluded — a planet's influence on the house it sits in is already
     * accounted for as occupation).
     *
     * @return list<string>
     */
    public function aspectingPlanets(int $house): array
    {
        $aspecting = [];

        foreach ($this->houses as $from) {
            foreach ($from['planets'] as $planet) {
                if ($from['number'] !== $house && Aspects::aspectsHouse($planet, $from['number'], $house)) {
                    $aspecting[] = $planet;
                }
            }
        }

        return $aspecting;
    }

    public function dignity(string $planet): ?string
    {
        $sign = $this->signOf($planet);

        return $sign === null ? null : PlanetStrength::dignity($planet, $sign, $this->longitudes[$planet] ?? null);
    }

    public function isCombust(string $planet): bool
    {
        return PlanetaryDignity::isCombust($planet, $this->longitudes);
    }

    public function savFor(int $house): ?int
    {
        return $this->sarvashtakavarga[$this->signOfHouse($house)] ?? null;
    }

    /**
     * @return array{lord: string, start: string, end: string, antardashas: list<array{lord: string, start: string, end: string}>}|null
     */
    public function mahadashaOf(string $lord): ?array
    {
        return collect($this->mahadashas ?? [])->firstWhere('lord', $lord);
    }

    public function degreeInSign(string $planet): ?float
    {
        if (! isset($this->longitudes[$planet])) {
            return null;
        }

        $longitude = $this->longitudes[$planet];

        return $longitude - floor($longitude / 30) * 30;
    }

    public function elementOf(string $sign): string
    {
        return ['fire', 'earth', 'air', 'water'][array_search($sign, ZodiacSigns::NAMES, true) % 4];
    }

    public function modalityOf(string $sign): string
    {
        return ['cardinal', 'fixed', 'mutable'][array_search($sign, ZodiacSigns::NAMES, true) % 3];
    }
}
