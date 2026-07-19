<?php

namespace App\Services\Astrology;

class ZodiacSigns
{
    public const NAMES = [
        'Aries', 'Taurus', 'Gemini', 'Cancer', 'Leo', 'Virgo',
        'Libra', 'Scorpio', 'Sagittarius', 'Capricorn', 'Aquarius', 'Pisces',
    ];

    public static function forLongitude(float $longitude): string
    {
        return self::NAMES[(int) floor(AstroMath::normalizeDegrees($longitude) / 30)];
    }

    /**
     * Formats a longitude as degrees-within-sign, e.g. "12° 18′" for 132.3°
     * (12.3° into Leo), matching the display format apps/web's demo chart
     * data already uses (see apps/web/lib/chart-display.ts).
     */
    public static function formatDegreeInSign(float $longitude): string
    {
        $degreeInSign = AstroMath::normalizeDegrees($longitude) - floor(AstroMath::normalizeDegrees($longitude) / 30) * 30;
        $wholeDegrees = (int) floor($degreeInSign);
        $minutes = (int) round(($degreeInSign - $wholeDegrees) * 60);

        if ($minutes === 60) {
            $minutes = 0;
            $wholeDegrees++;
        }

        return sprintf('%02d° %02d′', $wholeDegrees, $minutes);
    }
}
