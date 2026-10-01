<?php

namespace App\Services\Astrology\Shadbala;

/**
 * Naisargika Bala: fixed "natural strength" each classical planet carries
 * regardless of any chart, by its traditional rank (Sun > Moon > Venus >
 * Jupiter > Mercury > Mars > Saturn). The classical formula is simply
 * 60 * (8 - rank) / 7 for rank 1 (Sun) through 7 (Saturn), giving a clean
 * arithmetic sequence from 60 down to 60/7 — every published Shadbala
 * table (BV Raman, VP Jain, etc.) lists exactly these 7 values.
 */
class NaisargikaBala
{
    private const RANK = [
        'Sun' => 1, 'Moon' => 2, 'Venus' => 3, 'Jupiter' => 4,
        'Mercury' => 5, 'Mars' => 6, 'Saturn' => 7,
    ];

    /** @return array<string, float> Planet => Virupas (max 60, min 60/7 ≈ 8.57). */
    public static function calculate(): array
    {
        $values = [];
        foreach (self::RANK as $planet => $rank) {
            $values[$planet] = round(60 * (8 - $rank) / 7, 2);
        }

        return $values;
    }
}
