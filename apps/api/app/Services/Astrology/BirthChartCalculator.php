<?php

namespace App\Services\Astrology;

use App\Models\Setting;
use App\Services\Astrology\Ashtakvarga\AshtakvargaCalculator;
use App\Services\Astrology\Houses\PlacidusCusps;
use App\Services\Astrology\Jaimini\CharDasha;
use App\Services\Astrology\Jaimini\Karakas;
use App\Services\Astrology\KP\RulingPlanets;
use App\Services\Astrology\KP\Significators;
use App\Services\Astrology\KP\SubLord;
use App\Services\Astrology\LalKitab\LalKitabChart;
use App\Services\Astrology\Panchang\Vaar;
use App\Services\Astrology\Predictions\PredictionEngine;
use App\Services\Astrology\Remedies\RemedyEngine;
use App\Services\Astrology\Shadbala\BhavabalaCalculator;
use App\Services\Astrology\Shadbala\ShadbalaCalculator;
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
            ? PredictionEngine::generate($houses, $yogas, $chart['ascendant']['sign'], $nakshatra['name'] ?? null)
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
        $jaimini = null;
        $shadbala = null;
        $bhavabala = null;
        $avastha = null;
        if ($system === 'vedic') {
            $classicalPlanetSigns = [];
            foreach (PlanetaryFriendship::CLASSICAL_PLANETS as $planet) {
                $classicalPlanetSigns[$planet] = ZodiacSigns::forLongitude($chartLongitudes[$planet]);
            }
            $friendshipTable = PlanetaryFriendship::table($classicalPlanetSigns);
            $avastha = Avastha::forChart($chartLongitudes, $classicalPlanetSigns);

            $jaimini = [
                'atmakaraka' => Karakas::atmakaraka($chartLongitudes),
                'karakamsa' => Karakas::karakamsa($chartLongitudes),
                'swamsa' => Karakas::swamsa($ascendant),
                'char_dasha' => CharDasha::timeline($chart['ascendant']['sign'], $classicalPlanetSigns, $localDateTime),
            ];

            $shadbala = ShadbalaCalculator::calculate(
                $julianDay,
                $chartLongitudes,
                $classicalPlanetSigns,
                $houses,
                $localDateTime,
                $location['latitude'],
                $location['longitude']
            );
            $bhavabala = BhavabalaCalculator::calculate($shadbala['total_virupas'], $chartLongitudes, $houses);
        }
        $lalKitab = $system === 'vedic' ? LalKitabChart::build($chartLongitudes, $chart['ascendant']['sign']) : null;
        $aspects = WesternAspects::detect($chartLongitudes);

        // Placidus cusps are computed in the tropical frame (pure RAMC/
        // latitude/obliquity geometry, no zodiac dependency) and then
        // shifted into whichever frame this chart's system uses — same
        // ayanamsa ChartAssembler already subtracted from every planet
        // and the whole-sign ascendant, kept in sync here.
        $ayanamsa = $system === 'vedic' ? Ayanamsa::lahiri($julianDay) : 0.0;
        $placidusCusps = PlacidusCusps::calculate($julianDay, $location['latitude'], $location['longitude']);
        $shiftedCusps = [];
        foreach ($placidusCusps as $houseNumber => $longitude) {
            $shiftedCusps[$houseNumber] = AstroMath::normalizeDegrees($longitude - $ayanamsa);
        }

        $cuspPlanets = PlacidusCusps::planetsByHouse($shiftedCusps, $chartLongitudes);
        $bhavaMadhya = [];
        foreach ($shiftedCusps as $houseNumber => $longitude) {
            $bhavaMadhya[] = [
                'house' => $houseNumber,
                'sign' => ZodiacSigns::forLongitude($longitude),
                'degree' => ZodiacSigns::formatDegreeInSign($longitude),
                'longitude' => round($longitude, 4),
                'planets' => $cuspPlanets[$houseNumber],
            ];
        }

        // Cuspal aspects: available for both systems, like `aspects`
        // above — aspect-by-angular-separation isn't a sidereal-only
        // concept, it just uses whichever frame this chart already
        // produced. Planet-to-cusp only (see WesternAspects::
        // detectBetweenGroups's doc comment for why cusp-to-cusp is
        // excluded — opposite cusps are trivially 180° apart by
        // construction, not a meaningful finding).
        $cuspLabels = [];
        foreach ($shiftedCusps as $houseNumber => $longitude) {
            $cuspLabels["House{$houseNumber}"] = $longitude;
        }
        $cuspalAspects = WesternAspects::detectBetweenGroups($chartLongitudes, $cuspLabels);

        $kp = null;
        if ($system === 'vedic') {
            $kpSubLords = [];
            foreach ($chartLongitudes as $planet => $longitude) {
                $kpSubLords[$planet] = SubLord::forLongitude($longitude);
            }

            $kpCusps = [];
            foreach ($shiftedCusps as $houseNumber => $longitude) {
                $kpCusps[] = ['house' => $houseNumber] + SubLord::forLongitude($longitude);
            }

            $kpAscendant = SubLord::forLongitude($ascendant);

            $planetNakshatraLords = [];
            foreach ($kpSubLords as $planet => $subLord) {
                $planetNakshatraLords[$planet] = $subLord['nakshatra_lord'];
            }
            $houseSignificators = Significators::forHouses($bhavaMadhya, $planetNakshatraLords);

            $kp = [
                'sub_lords' => $kpSubLords,
                'ascendant' => $kpAscendant,
                'cusps' => $kpCusps,
                'house_significators' => $houseSignificators,
                'planet_significations' => Significators::planetSignifications($houseSignificators),
                'ruling_planets' => RulingPlanets::compute(
                    Vaar::forDate($localDateTime)['lord'],
                    $chart['ascendant']['sign'],
                    $kpAscendant,
                    $classicalPlanetSigns['Moon'],
                    $kpSubLords['Moon']
                ),
            ];
        }

        return [
            'timezone' => $location['timezone'],
            'system' => $system,
            'chart_style' => $chartStyle,
            'recommendation' => $recommendation,
            'planetary_positions' => $planetaryPositions,
            'houses' => $houses,
            'ascendant' => $chart['ascendant'],
            // Exposed alongside the formatted `ascendant` block above
            // purely so downstream longitude-only consumers (e.g.
            // KundaliReportGenerator's Shodashvarga table, which needs a
            // raw longitude to feed VargaCalculator::sign(), not a
            // pre-formatted sign/degree pair) don't have to re-geocode and
            // recompute the whole chart just to get a number this method
            // already has in `$ascendant`.
            'ascendant_longitude' => $ascendant,
            'nakshatra' => $nakshatra,
            'dasha' => $dasha,
            'yogas' => $yogas,
            'predictions' => $predictions,
            'remedies' => $remedies,
            'ashtakvarga' => $ashtakvarga,
            'avkahada' => $avkahada,
            'friendship_table' => $friendshipTable,
            'jaimini' => $jaimini,
            'shadbala' => $shadbala,
            'bhavabala' => $bhavabala,
            'avastha' => $avastha,
            'kp' => $kp,
            'lal_kitab' => $lalKitab,
            'bhava_madhya' => $bhavaMadhya,
            'aspects' => $aspects,
            'cuspal_aspects' => $cuspalAspects,
            'location_matched' => $location['matched'],
        ];
    }
}
