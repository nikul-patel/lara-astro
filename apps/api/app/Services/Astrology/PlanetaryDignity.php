<?php

namespace App\Services\Astrology;

/**
 * Classical sign-based dignity (exaltation, debilitation, own-sign,
 * moolatrikona) and combustion. Dignity here is evaluated at the sign
 * level, not the exact degree — the exaltation/debilitation "deep point"
 * degrees only matter for fine-grained strength scoring (shadbala), which
 * this engine doesn't attempt; sign-level dignity is what the yoga rules
 * in Services/Astrology/Yogas actually need.
 *
 * Rahu/Ketu's exaltation and debilitation signs are genuinely contested
 * across classical texts (unlike the 7 classical grahas, where there's
 * broad consensus); this table uses the commonly-cited Taurus/Scorpio
 * convention, flagged as a stated simplification rather than settled
 * doctrine.
 */
class PlanetaryDignity
{
    public const EXALTATION_SIGN = [
        'Sun' => 'Aries', 'Moon' => 'Taurus', 'Mars' => 'Capricorn', 'Mercury' => 'Virgo',
        'Jupiter' => 'Cancer', 'Venus' => 'Pisces', 'Saturn' => 'Libra',
        'Rahu' => 'Taurus', 'Ketu' => 'Scorpio',
    ];

    public const DEBILITATION_SIGN = [
        'Sun' => 'Libra', 'Moon' => 'Scorpio', 'Mars' => 'Cancer', 'Mercury' => 'Pisces',
        'Jupiter' => 'Capricorn', 'Venus' => 'Virgo', 'Saturn' => 'Aries',
        'Rahu' => 'Scorpio', 'Ketu' => 'Taurus',
    ];

    public const OWN_SIGNS = [
        'Sun' => ['Leo'], 'Moon' => ['Cancer'], 'Mars' => ['Aries', 'Scorpio'],
        'Mercury' => ['Gemini', 'Virgo'], 'Jupiter' => ['Sagittarius', 'Pisces'],
        'Venus' => ['Taurus', 'Libra'], 'Saturn' => ['Capricorn', 'Aquarius'],
    ];

    /**
     * Moolatrikona sign and degree range, where it differs from the full
     * own-sign (e.g. Sun's Moolatrikona is only 0-20° Leo; the rest of Leo
     * is merely own-sign). Rahu/Ketu have no classical Moolatrikona.
     *
     * @var array<string, array{sign: string, from: float, to: float}>
     */
    public const MOOLATRIKONA = [
        'Sun' => ['sign' => 'Leo', 'from' => 0.0, 'to' => 20.0],
        'Moon' => ['sign' => 'Taurus', 'from' => 4.0, 'to' => 30.0],
        'Mars' => ['sign' => 'Aries', 'from' => 0.0, 'to' => 12.0],
        'Mercury' => ['sign' => 'Virgo', 'from' => 16.0, 'to' => 20.0],
        'Jupiter' => ['sign' => 'Sagittarius', 'from' => 0.0, 'to' => 10.0],
        'Venus' => ['sign' => 'Libra', 'from' => 0.0, 'to' => 15.0],
        'Saturn' => ['sign' => 'Aquarius', 'from' => 0.0, 'to' => 20.0],
    ];

    /**
     * Combustion orb (maximum angular distance from the Sun, in degrees,
     * within which a planet is considered combust/"burnt"). Classical
     * texts vary this by direct vs. retrograde motion; this engine doesn't
     * compute retrograde status, so a single direct-motion orb is used
     * throughout — a stated simplification.
     */
    public const COMBUSTION_ORB = [
        'Moon' => 12.0, 'Mars' => 17.0, 'Mercury' => 14.0,
        'Jupiter' => 11.0, 'Venus' => 10.0, 'Saturn' => 15.0,
    ];

    public static function isExalted(string $planet, string $sign): bool
    {
        return (self::EXALTATION_SIGN[$planet] ?? null) === $sign;
    }

    public static function isDebilitated(string $planet, string $sign): bool
    {
        return (self::DEBILITATION_SIGN[$planet] ?? null) === $sign;
    }

    public static function isOwnSign(string $planet, string $sign): bool
    {
        return in_array($sign, self::OWN_SIGNS[$planet] ?? [], true);
    }

    /**
     * The planet ruling another planet's exaltation sign — the "dispositor
     * of the exaltation sign", used by Neecha Bhanga's aspect-based
     * cancellation condition (see Yogas\NeechaBhangaYoga).
     */
    public static function exaltationSignRuler(string $planet): string
    {
        return HouseLords::SIGN_RULERS[self::EXALTATION_SIGN[$planet]];
    }

    /**
     * The planet ruling another planet's debilitation sign — its
     * "dispositor", used by Neecha Bhanga's kendra-placement condition.
     */
    public static function debilitationSignRuler(string $planet): string
    {
        return HouseLords::SIGN_RULERS[self::DEBILITATION_SIGN[$planet]];
    }

    /**
     * Whether $planet is within combustion range of the Sun. Not
     * meaningful for the Sun itself or the lunar nodes (Rahu/Ketu are
     * shadow points, not physical bodies the Sun can combust).
     */
    public static function isCombust(string $planet, array $planetLongitudes): bool
    {
        if (! isset(self::COMBUSTION_ORB[$planet], $planetLongitudes[$planet], $planetLongitudes['Sun'])) {
            return false;
        }

        $distance = abs(AstroMath::normalizeDegrees($planetLongitudes[$planet] - $planetLongitudes['Sun']));
        if ($distance > 180) {
            $distance = 360 - $distance;
        }

        return $distance <= self::COMBUSTION_ORB[$planet];
    }
}
