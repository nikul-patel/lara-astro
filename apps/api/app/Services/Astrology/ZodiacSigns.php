<?php

namespace App\Services\Astrology;

class ZodiacSigns
{
    public const NAMES = [
        'Aries', 'Taurus', 'Gemini', 'Cancer', 'Leo', 'Virgo',
        'Libra', 'Scorpio', 'Sagittarius', 'Capricorn', 'Aquarius', 'Pisces',
    ];

    /**
     * Standard 3-letter sign abbreviations — used only where a table needs
     * one column per sign (12+ columns: Ashtakvarga, Prastharashtakvarga,
     * Shodashvarga) and the full name would wrap or overflow; narrative
     * text elsewhere always uses the full name from NAMES.
     *
     * @var array<string, string>
     */
    public const ABBREVIATIONS = [
        'Aries' => 'Ari', 'Taurus' => 'Tau', 'Gemini' => 'Gem', 'Cancer' => 'Can',
        'Leo' => 'Leo', 'Virgo' => 'Vir', 'Libra' => 'Lib', 'Scorpio' => 'Sco',
        'Sagittarius' => 'Sag', 'Capricorn' => 'Cap', 'Aquarius' => 'Aqu', 'Pisces' => 'Pis',
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

        // Rounding the last half-minute of a sign up to 60' would either
        // print an out-of-range "30° 00′" (30 is the next sign's 0°, but
        // `sign` — computed separately from the unrounded longitude — still
        // names this one) or, at the very end of Pisces, roll past 29° into
        // a nonexistent 30th degree. Clamp instead of carrying the rollover
        // into the degree.
        if ($minutes === 60) {
            $minutes = 59;
        }

        return sprintf('%02d° %02d′', $wholeDegrees, $minutes);
    }

    /**
     * The house number (1-12) $toSign occupies when counted inclusively
     * from $fromSign — e.g. offset(Aries, Aries) = 1, offset(Aries, Taurus)
     * = 2. Shared by every place in this codebase that needs a
     * sign-to-sign distance rather than a longitude-to-house lookup
     * (YearlyForecast\TransitForecast's Gochara offsets, the Doshas and
     * Matching namespaces' rashi-based rules).
     */
    public static function offset(string $fromSign, string $toSign): int
    {
        $fromIndex = array_search($fromSign, self::NAMES, true);
        $toIndex = array_search($toSign, self::NAMES, true);

        return (($toIndex - $fromIndex + 12) % 12) + 1;
    }
}
