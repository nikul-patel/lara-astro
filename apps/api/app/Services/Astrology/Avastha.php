<?php

namespace App\Services\Astrology;

/**
 * Avastha ("state"): classical systems describing a planet's condition in
 * the natal chart, distinct from (and additional to) Shadbala's numeric
 * strength. Two sub-systems are implemented here — see #83 for why a third,
 * Deeptadi Avastha, is deliberately excluded.
 *
 * Both rules below were cross-checked across two independently-phrased web
 * searches returning consistent results from several distinct sources each
 * (every specific source page itself was unreachable in this environment —
 * same network restriction noted throughout this codebase's other
 * citations — so this cites the cross-verification method, not a single
 * URL, per this codebase's verification-over-citation standard).
 *
 * Scoped to the 7 classical planets only, same as {@see PlanetaryFriendship}
 * and {@see Shadbala\ShadbalaCalculator} — Rahu/Ketu have no natural
 * friendship table or Moolatrikona to classify against, and no competitor
 * Avastha panel surfaced in this project's research includes them either.
 */
class Avastha
{
    /**
     * Baladi Avastha states in their natural forward order — an odd sign
     * (1st, 3rd, 5th... counting from Aries=1) steps through these in
     * order as its degree-in-sign increases; an even sign steps through
     * them in reverse. Either way, the middle 12-18° band is always Yuva.
     */
    private const BALADI_STATES = ['Bala', 'Kumara', 'Yuva', 'Vriddha', 'Mrita'];

    private const BALADI_BAND_WIDTH = 6.0;

    /**
     * Baladi Avastha: 5 age-based states by how far into its sign a planet
     * sits, in fixed 6° bands — Bala (infant, weakest), Kumara (adolescent),
     * Yuva (youth, strongest), Vriddha (elderly), Mrita (dead, weakest).
     * Direction reverses for even signs (Taurus, Cancer, Virgo, Scorpio,
     * Capricorn, Pisces) so the 12-18° "peak" band is always Yuva
     * regardless of which sign a planet occupies.
     */
    public static function baladi(float $longitude): string
    {
        $normalized = AstroMath::normalizeDegrees($longitude);
        $signIndex = (int) floor($normalized / 30);
        $degreeInSign = $normalized - $signIndex * 30;

        $band = min((int) floor($degreeInSign / self::BALADI_BAND_WIDTH), 4);
        $isOddSign = $signIndex % 2 === 0; // Aries (index 0) is the 1st sign, odd.

        return $isOddSign ? self::BALADI_STATES[$band] : self::BALADI_STATES[4 - $band];
    }

    /**
     * Jagrat/Swapna/Sushupta Avastha: 3 consciousness-like states by sign
     * dignity — Jagrat ("awake", strongest: own sign or exalted), Swapna
     * ("dreaming", middling: a naturally friendly or neutral planet's
     * sign), Sushupta ("asleep", weakest: debilitated or a naturally
     * inimical planet's sign).
     */
    public static function jagratSwapnaSushupta(string $planet, string $sign): string
    {
        if (PlanetaryDignity::isExalted($planet, $sign) || PlanetaryDignity::isOwnSign($planet, $sign)) {
            return 'Jagrat';
        }

        if (PlanetaryDignity::isDebilitated($planet, $sign)) {
            return 'Sushupta';
        }

        $lord = HouseLords::SIGN_RULERS[$sign];

        return match (PlanetaryFriendship::relationship($planet, $lord)) {
            'enemy' => 'Sushupta',
            default => 'Swapna', // 'friend' or 'neutral'.
        };
    }

    /**
     * @param  array<string, float>  $chartLongitudes
     * @param  array<string, string>  $rasiSigns  Each classical planet's D1 sign.
     * @return array<string, array{baladi: string, jagrat_swapna_sushupta: string}>
     */
    public static function forChart(array $chartLongitudes, array $rasiSigns): array
    {
        $result = [];
        foreach (PlanetaryFriendship::CLASSICAL_PLANETS as $planet) {
            $result[$planet] = [
                'baladi' => self::baladi($chartLongitudes[$planet]),
                'jagrat_swapna_sushupta' => self::jagratSwapnaSushupta($planet, $rasiSigns[$planet]),
            ];
        }

        return $result;
    }
}
