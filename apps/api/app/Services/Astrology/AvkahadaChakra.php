<?php

namespace App\Services\Astrology;

use App\Services\Astrology\Matching\NakshatraAttributes;
use App\Services\Astrology\Remedies\PlanetRemedies;

/**
 * Avkahada Chakra: a compact "at a glance" summary panel competitor
 * reports lead with, built entirely from classical attributes this engine
 * already computes with verified confidence elsewhere — Varna, Yoni, Gana,
 * Vashya, and Nadi are the same lookup tables Kundali Matching's Ashtakoot
 * kootas already use (see Matching\NakshatraAttributes), exposed here
 * standalone rather than only as half of a two-person comparison.
 *
 * Scope note: competitor "Avkahada Chakra" panels also typically include a
 * Paya (metal) attribute and a "Favourable Points" / "Ghatak" (malefic)
 * block — lucky numbers, good years, lucky stone/metal/days, bad
 * day/karan/lagna/month/nakshatra/tithi/yoga/planet, and similar. Those
 * are deliberately NOT included here: unlike Varna/Yoni/Gana/Vashya/Nadi
 * (which this codebase already verified independently while building
 * Kundali Matching) or "good planets"/"friendly signs" (derivable from the
 * already-tested PlanetaryFriendship table) or "lucky stone" (reusing the
 * already-tested PlanetRemedies gemstone table), Paya and most of the
 * Favourable/Ghatak entries don't have a single, independently-verifiable
 * classical source available in this environment (no network access to
 * cross-check against a cited reference) — presenting invented-sounding
 * values with unwarranted confidence would be worse than omitting them.
 * If a specific, citable source is identified later, these can be added.
 */
class AvkahadaChakra
{
    /**
     * @param  array{index: int, name: string}  $nakshatra
     * @return array{
     *     varna: string,
     *     yoni: string,
     *     gana: string,
     *     vashya: string,
     *     nadi: string,
     *     good_planets: list<string>,
     *     friendly_signs: list<string>,
     *     lucky_stone: string,
     *     lucky_day: string,
     * }
     */
    public static function forChart(array $nakshatra, string $moonSign, string $ascendantSign): array
    {
        $lagnaLord = HouseLords::SIGN_RULERS[$ascendantSign];
        $goodPlanets = PlanetaryFriendship::FRIENDS[$lagnaLord];

        $friendlySigns = array_keys(array_filter(
            HouseLords::SIGN_RULERS,
            fn (string $ruler) => in_array($ruler, $goodPlanets, true),
        ));
        sort($friendlySigns);

        return [
            'varna' => NakshatraAttributes::varnaOf($moonSign),
            'yoni' => NakshatraAttributes::yoniOf($nakshatra['name']),
            'gana' => NakshatraAttributes::ganaOf($nakshatra['name']),
            'vashya' => NakshatraAttributes::vashyaOf($moonSign),
            'nadi' => NakshatraAttributes::nadiOf($nakshatra['name']),
            'good_planets' => $goodPlanets,
            'friendly_signs' => $friendlySigns,
            'lucky_stone' => PlanetRemedies::REMEDIES[$lagnaLord]['gemstone'],
            'lucky_day' => PlanetRemedies::REMEDIES[$lagnaLord]['fasting_day'],
        ];
    }
}
