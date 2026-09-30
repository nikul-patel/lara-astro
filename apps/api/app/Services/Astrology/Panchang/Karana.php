<?php

namespace App\Services\Astrology\Panchang;

use App\Services\Astrology\AstroMath;

/**
 * Karana: half a Tithi (6° of Moon-minus-Sun angular distance), giving 60
 * karanas across a lunar month — but only 11 classical names. 7 "movable"
 * (Chara) karanas repeat in a fixed cycle through karana-indices 1-56
 * (8 full cycles); the remaining 4 — one "fixed" (Sthira) karana at index 0
 * and three at the very end of the month (indices 57-59) — each occur
 * exactly once, always at the same point in the lunar cycle. This is the
 * standard classical scheme (see e.g. Surya Siddhanta-derived Panchang
 * tables); the index arithmetic below is the well-established way to
 * derive it rather than a simplification of our own.
 */
class Karana
{
    private const SPAN = 360 / 60; // 6°

    /**
     * The 7 movable karanas, in their fixed repeating order.
     */
    private const MOVABLE = ['Bava', 'Balava', 'Kaulava', 'Taitila', 'Gara', 'Vanija', 'Vishti'];

    public static function forLongitudes(float $sunLongitude, float $moonLongitude): string
    {
        $angle = AstroMath::normalizeDegrees($moonLongitude - $sunLongitude);
        $index = (int) floor($angle / self::SPAN); // 0-59

        return match (true) {
            $index === 0 => 'Kimstughna',
            $index >= 57 => ['Shakuni', 'Chatushpada', 'Naga'][$index - 57],
            default => self::MOVABLE[($index - 1) % 7],
        };
    }
}
