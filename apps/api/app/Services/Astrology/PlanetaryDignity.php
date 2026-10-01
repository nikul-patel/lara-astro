<?php

namespace App\Services\Astrology;

/**
 * Classical sign-based dignity (exaltation, debilitation, own-sign,
 * moolatrikona) and combustion. Dignity here is evaluated at the sign
 * level, not the exact degree, for every lookup except
 * {@see self::DEEP_EXALTATION_DEGREE} — sign-level dignity is what the
 * yoga rules in Services/Astrology/Yogas need; the deep-point degrees
 * exist only for Shadbala's Uchcha Bala (Services/Astrology/Shadbala),
 * which does need exact-degree strength scoring.
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

    /**
     * Exact degree-within-sign of each planet's deep exaltation point
     * (within {@see self::EXALTATION_SIGN}) — e.g. the Sun is only
     * maximally exalted at 10° Aries, not anywhere in Aries. Deep
     * debilitation sits at the same degree offset within the opposite
     * (7th) sign. Classical values (BPHS); no Rahu/Ketu entry since their
     * exaltation is already a stated simplification with no commonly-cited
     * deep-point degree.
     */
    public const DEEP_EXALTATION_DEGREE = [
        'Sun' => 10.0, 'Moon' => 3.0, 'Mars' => 28.0, 'Mercury' => 15.0,
        'Jupiter' => 5.0, 'Venus' => 27.0, 'Saturn' => 20.0,
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
     * Absolute longitude (0-360°) of the planet's deep debilitation point —
     * the reference Shadbala's Uchcha Bala measures angular distance from
     * (see Shadbala\SthanaBala::uchchaBala()).
     */
    public static function deepDebilitationLongitude(string $planet): float
    {
        $signIndex = array_search(self::DEBILITATION_SIGN[$planet], ZodiacSigns::NAMES, true);

        return $signIndex * 30 + self::DEEP_EXALTATION_DEGREE[$planet];
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
