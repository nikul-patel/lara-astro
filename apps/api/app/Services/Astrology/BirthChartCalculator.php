<?php

namespace App\Services\Astrology;

use App\Models\Setting;
use App\Services\Astrology\Ashtakvarga\AshtakvargaCalculator;
use App\Services\Astrology\Predictions\PredictionEngine;
use App\Services\Astrology\Remedies\RemedyEngine;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

/**
 * Orchestrates a full birth chart calculation: geocode the birth place,
 * compute planetary positions and houses, and assemble the response shape
 * apps/web's ChartResult type expects (see apps/web/lib/api/types.ts and
 * docs/API_CONTRACT.md's Birth Chart section).
 *
 * Precision note (applies to every class in this namespace): this is a
 * self-hosted, dependency-free calculation engine built on well-documented
 * low-precision astronomical algorithms (Meeus's Sun/Moon series, Standish's
 * planetary Keplerian elements) rather than a Swiss Ephemeris binding —
 * PRD §8's stated recommendation — because this build environment has no
 * route to install/download one (no compiler for a C extension, no network
 * access to fetch Swiss Ephemeris's data files). Accuracy is good enough to
 * place planets in their correct sign and compute a sound ascendant/houses
 * for chart display, but is not suitable for arc-second-precision use.
 * Swapping in a real Swiss Ephemeris binding later only means replacing
 * SunPosition/MoonPosition/PlanetaryElements/LunarNodes behind the same
 * interface these classes already establish.
 */
class BirthChartCalculator
{
    /**
     * @param  array{name: string, dob: string, time: string, place: string, system?: ?string, chart_style?: ?string}  $input
     * @return array<string, mixed>
     */
    public static function calculate(array $input): array
    {
        $location = PlaceLookup::resolve($input['place']);
        $recommendation = RegionRecommendation::forLocation($location['state']);

        $setting = Setting::current();
        $system = $input['system'] ?? $recommendation['system'];

        if ($system === 'western' && ! $setting->astrology_western_enabled) {
            throw ValidationException::withMessages([
                'system' => 'Western system is not offered for this deployment.',
            ]);
        }

        // A deployment-forced style (PRD §8 point 4) overrides even an
        // explicit request override — that's the point of forcing it.
        // $recommendation['chart_style'] already reflects the forced style
        // when set (see RegionRecommendation), so it takes precedence here
        // rather than the request's own chart_style.
        $chartStyle = $system === 'vedic'
            ? ($setting->astrology_forced_chart_style ?? $input['chart_style'] ?? $recommendation['chart_style'])
            : null;

        $localDateTime = CarbonImmutable::parse("{$input['dob']} {$input['time']}", $location['timezone']);
        $utc = $localDateTime->utc();
        $julianDay = JulianDay::fromUtc($utc);

        $chart = ChartAssembler::assemble($julianDay, $location['latitude'], $location['longitude'], $system);
        $chartLongitudes = $chart['chart_longitudes'];
        $ascendant = $chart['ascendant_longitude'];
        $planetaryPositions = $chart['planetary_positions'];
        $houses = $chart['houses'];

        // Nakshatra and Vimshottari dasha are Vedic-specific (sidereal)
        // concepts with no Western-astrology equivalent, same as
        // chart_style above.
        $nakshatra = $system === 'vedic' ? Nakshatra::forLongitude($chartLongitudes['Moon']) : null;
        $dasha = $system === 'vedic'
            ? [
                'mahadasha' => VimshottariDasha::timeline($chartLongitudes['Moon'], $localDateTime),
                'yogini' => YoginiDasha::timeline($chartLongitudes['Moon'], $localDateTime),
            ]
            : null;
        $yogas = $system === 'vedic' ? YogaEngine::detect($houses) : null;
        $predictions = $system === 'vedic' && $setting->astrology_predictions_enabled
            ? PredictionEngine::generate($houses, $yogas)
            : null;
        $remedies = $system === 'vedic' && $setting->astrology_predictions_enabled
            ? RemedyEngine::generate($houses, $chartLongitudes)
            : null;
        $ashtakvarga = $system === 'vedic'
            ? AshtakvargaCalculator::calculate($planetaryPositions, $chart['ascendant']['sign'])
            : null;
        $avkahada = $system === 'vedic'
            ? AvkahadaChakra::forChart($nakshatra, HouseLords::signOfPlanet('Moon', $houses), $chart['ascendant']['sign'])
            : null;
        $friendshipTable = null;
        if ($system === 'vedic') {
            $classicalPlanetSigns = [];
            foreach (PlanetaryFriendship::CLASSICAL_PLANETS as $planet) {
                $classicalPlanetSigns[$planet] = ZodiacSigns::forLongitude($chartLongitudes[$planet]);
            }
            $friendshipTable = PlanetaryFriendship::table($classicalPlanetSigns);
        }
        $aspects = WesternAspects::detect($chartLongitudes);

        return [
            'timezone' => $location['timezone'],
            'system' => $system,
            'chart_style' => $chartStyle,
            'recommendation' => $recommendation,
            'planetary_positions' => $planetaryPositions,
            'houses' => $houses,
            'ascendant' => $chart['ascendant'],
            'nakshatra' => $nakshatra,
            'dasha' => $dasha,
            'yogas' => $yogas,
            'predictions' => $predictions,
            'remedies' => $remedies,
            'ashtakvarga' => $ashtakvarga,
            'avkahada' => $avkahada,
            'friendship_table' => $friendshipTable,
            'aspects' => $aspects,
            'location_matched' => $location['matched'],
        ];
    }
}
