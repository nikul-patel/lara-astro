<?php

namespace App\Services\Astrology\LalKitab;

use App\Services\Astrology\HouseLords;
use App\Services\Astrology\ZodiacSigns;

/**
 * Lal Kitab is a genuinely separate astrological paradigm from the
 * Parashari system this engine otherwise implements (see
 * BirthChartCalculator) — North Indian, Urdu/Persian-influenced in
 * origin, with its own house-placement convention, its own "Pucca
 * Ghar"/"Kaccha Ghar" (strong/weak house) framework, planetary "Rinn"
 * (karmic debt) theory, and a large codified remedy library distinct from
 * (and much larger than) this engine's existing Remedies\RemedyEngine.
 *
 * SCOPE OF THIS CLASS — read before extending it:
 *
 * This implements only the Lal Kitab "fixed" chart construction and Pucca
 * Ghar (own/strong-house) status for the 7 classical planets. It
 * deliberately does NOT implement Rinn (debt) detection, prediction text,
 * or the remedy library, which the originating GitHub issue (#78)
 * explicitly scoped together with this chart work but flagged as needing
 * "a specific published Lal Kitab ruleset/edition" to follow, since
 * "Lal Kitab practice varies more between sources than classical
 * Parashari astrology does."
 *
 * Why those three are excluded: this session attempted web research for
 * a citable, authoritative source (Wikipedia, archive.org's scanned
 * Lal Kitab editions, astrosage.com, paramarsh.app, and a Scribd
 * transcription were all tried) but every one of those domains was
 * blocked by this environment's network egress policy, and the original
 * five Lal Kitab editions are themselves written in Urdu — not something
 * this session could translate and verify even if fetched. Falling back
 * to this model's own general knowledge of Rinn's house-trigger table
 * produced internally INCONSISTENT recollections across cross-checks
 * (different half-remembered versions disagreeing on which house numbers
 * trigger which named debt) — a strong signal that presenting any single
 * version as authoritative would risk real misinformation, which this
 * codebase's established policy (documented gaps over fabricated
 * specifics — see Avkahada\AvkahadaChakra, Shadbala\KalaBala's Abda/Masa
 * exclusion) explicitly avoids. Rinn/predictions/remedies should be
 * implemented in a follow-up once a specific, named, verifiable edition
 * is chosen (per the issue's own acceptance-criteria wording).
 *
 * What IS implemented is low-ambiguity and consistently described across
 * every source encountered, including this model's own training
 * knowledge: unlike the whole-sign system (Houses.php), where house 1 is
 * whichever sign the Ascendant occupies, Lal Kitab's chart fixes house 1
 * to Aries always, house 2 to Taurus, and so on through house 12 =
 * Pisces — a planet's house is simply its own sign's position, 1-12,
 * regardless of where the Ascendant falls. The real Ascendant/Lagna still
 * matters in this fixed frame (recorded separately as `ascendant_house`,
 * since which fixed house the actual Lagna point falls into is itself a
 * reading point), it just isn't what houses are numbered FROM.
 *
 * Pucca Ghar ("strong/own house"): for the 7 classical planets, this is
 * mechanically identical to classical Parashari own-sign rulership
 * (HouseLords::SIGN_RULERS) just re-expressed as a fixed house number
 * instead of a sign name — e.g. the Sun's Pucca Ghar is always house 5
 * (Leo), Saturn's is houses 10 and 11 (Capricorn/Aquarius). This part
 * carries no edition-to-edition ambiguity. Rahu and Ketu are deliberately
 * excluded from `pucca_ghar` — classical Parashari doesn't assign them an
 * own sign at all, and the Lal-Kitab-specific house each is conventionally
 * given (commonly cited as house 6 or house 12, depending on the source)
 * is exactly the kind of edition-dependent detail this class avoids
 * presenting as settled fact.
 */
class LalKitabChart
{
    private const CLASSICAL_PLANETS_WITH_OWN_SIGNS = ['Sun', 'Moon', 'Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn'];

    /**
     * @param  array<string, float>  $chartLongitudes  Sidereal longitudes, every chart body (Sun..Ketu).
     * @return array{houses: list<array{number: int, sign: string, planets: list<string>}>, ascendant_house: int, empty_houses: list<int>, pucca_ghar: array<string, bool>}
     */
    public static function build(array $chartLongitudes, string $ascendantSign): array
    {
        $houses = [];
        foreach (ZodiacSigns::NAMES as $index => $sign) {
            $houseNumber = $index + 1;
            $planetsInHouse = [];
            foreach ($chartLongitudes as $planet => $longitude) {
                if (ZodiacSigns::forLongitude($longitude) === $sign) {
                    $planetsInHouse[] = $planet;
                }
            }

            $houses[] = ['number' => $houseNumber, 'sign' => $sign, 'planets' => $planetsInHouse];
        }

        $ascendantHouse = array_search($ascendantSign, ZodiacSigns::NAMES, true) + 1;

        $emptyHouses = array_values(array_map(
            fn (array $house) => $house['number'],
            array_filter($houses, fn (array $house) => $house['planets'] === [])
        ));

        $puccaGhar = [];
        foreach (self::CLASSICAL_PLANETS_WITH_OWN_SIGNS as $planet) {
            $sign = ZodiacSigns::forLongitude($chartLongitudes[$planet]);
            $puccaGhar[$planet] = HouseLords::SIGN_RULERS[$sign] === $planet;
        }

        return [
            'houses' => $houses,
            'ascendant_house' => $ascendantHouse,
            'empty_houses' => $emptyHouses,
            'pucca_ghar' => $puccaGhar,
        ];
    }
}
