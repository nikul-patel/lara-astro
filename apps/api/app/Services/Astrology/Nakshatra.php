<?php

namespace App\Services\Astrology;

/**
 * Nakshatra (lunar mansion) and pada for a sidereal longitude. The 27
 * nakshatras divide the zodiac into equal 13°20' segments starting at 0°
 * Aries (sidereal); each nakshatra further divides into 4 padas of 3°20'
 * each.
 *
 * The Moon's nakshatra at birth is the seed for Vimshottari dasha (see
 * VimshottariDasha): its lord is the dasha lord the native is born under,
 * and the Moon's position within it fixes how much of that first dasha's
 * period remains at birth.
 */
class Nakshatra
{
    private const SPAN = 360 / 27; // 13°20'

    private const PADA_SPAN = self::SPAN / 4; // 3°20'

    public const NAMES = [
        'Ashwini', 'Bharani', 'Krittika', 'Rohini', 'Mrigashira', 'Ardra',
        'Punarvasu', 'Pushya', 'Ashlesha', 'Magha', 'Purva Phalguni', 'Uttara Phalguni',
        'Hasta', 'Chitra', 'Swati', 'Vishakha', 'Anuradha', 'Jyeshtha',
        'Mula', 'Purva Ashadha', 'Uttara Ashadha', 'Shravana', 'Dhanishta', 'Shatabhisha',
        'Purva Bhadrapada', 'Uttara Bhadrapada', 'Revati',
    ];

    /**
     * The 9 Vimshottari dasha lords, in their fixed cyclical order,
     * repeating unchanged 3 times across the 27 nakshatras (Ashwini starts
     * the cycle under Ketu; nakshatras 10-18 and 19-27 repeat the same
     * 9-lord sequence).
     */
    public const LORD_CYCLE = ['Ketu', 'Venus', 'Sun', 'Moon', 'Mars', 'Rahu', 'Jupiter', 'Saturn', 'Mercury'];

    /**
     * @return array{index: int, name: string, lord: string, pada: int}
     */
    public static function forLongitude(float $siderealLongitude): array
    {
        $normalized = AstroMath::normalizeDegrees($siderealLongitude);
        $index = (int) floor($normalized / self::SPAN);
        $withinNakshatra = $normalized - $index * self::SPAN;
        $pada = (int) floor($withinNakshatra / self::PADA_SPAN) + 1;

        return [
            'index' => $index,
            'name' => self::NAMES[$index],
            'lord' => self::LORD_CYCLE[$index % 9],
            'pada' => $pada,
        ];
    }

    /**
     * How far the given longitude has progressed through its nakshatra, as
     * a fraction in [0, 1). Vimshottari dasha's balance-at-birth
     * calculation needs exactly this (see VimshottariDasha::timeline()).
     */
    public static function fractionElapsed(float $siderealLongitude): float
    {
        $normalized = AstroMath::normalizeDegrees($siderealLongitude);
        $withinNakshatra = $normalized - floor($normalized / self::SPAN) * self::SPAN;

        return $withinNakshatra / self::SPAN;
    }
}
