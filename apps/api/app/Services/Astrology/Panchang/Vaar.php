<?php

namespace App\Services\Astrology\Panchang;

use Carbon\CarbonImmutable;

/**
 * Vaar: the Panchang weekday, each ruled by a classical planet — the same
 * planet names used throughout Services/Astrology, in the familiar
 * Sun→Saturn order starting from Sunday.
 */
class Vaar
{
    private const NAMES = [
        0 => 'Ravivar', 1 => 'Somvar', 2 => 'Mangalvar', 3 => 'Budhvar',
        4 => 'Guruvar', 5 => 'Shukravar', 6 => 'Shanivar',
    ];

    private const LORDS = [
        0 => 'Sun', 1 => 'Moon', 2 => 'Mars', 3 => 'Mercury',
        4 => 'Jupiter', 5 => 'Venus', 6 => 'Saturn',
    ];

    /**
     * @return array{name: string, lord: string}
     */
    public static function forDate(CarbonImmutable $localDate): array
    {
        $dayOfWeek = (int) $localDate->format('w'); // 0 (Sunday) - 6 (Saturday)

        return ['name' => self::NAMES[$dayOfWeek], 'lord' => self::LORDS[$dayOfWeek]];
    }
}
