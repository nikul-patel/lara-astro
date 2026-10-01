<?php

namespace App\Services\Astrology\Varga;

use App\Services\Astrology\AstroMath;
use App\Services\Astrology\ZodiacSigns;
use InvalidArgumentException;

/**
 * Shodashvarga: the 16 classical divisional (varga) charts, each dividing
 * every 30° sign into N equal segments and mapping each segment to a sign
 * by a fixed classical rule. 14 of the 16 vargas share the same general
 * shape — a per-varga "which sign does segment 0 start from" rule, then
 * counting forward one sign per segment — captured here as
 * {@see self::startingSignIndex()}. D2 (Hora) and D30 (Trimsamsa) are
 * genuinely irregular (Hora only ever resolves to 2 signs; Trimsamsa uses
 * unequal degree spans assigned to specific planets/signs) and are handled
 * as their own cases rather than forced into the general formula.
 *
 * All computed purely from a longitude already produced elsewhere in this
 * engine (see BirthChartCalculator), so these inherit that engine's
 * existing sign-level precision — no new astronomical calculation is
 * needed.
 */
class VargaCalculator
{
    /** Division count per varga. D1 is the birth chart itself, included here only for completeness/validation. */
    public const DIVISIONS = [
        'D1' => 1, 'D2' => 2, 'D3' => 3, 'D4' => 4, 'D7' => 7, 'D9' => 9,
        'D10' => 10, 'D12' => 12, 'D16' => 16, 'D20' => 20, 'D24' => 24,
        'D27' => 27, 'D30' => 30, 'D40' => 40, 'D45' => 45, 'D60' => 60,
    ];

    /**
     * Fixed per-varga step (how many signs forward each segment advances)
     * for every varga handled by the general formula — D3/D4 don't
     * advance one sign per segment like the rest.
     */
    private const STEP = [
        'D3' => 4, 'D4' => 3, 'D7' => 1, 'D9' => 1, 'D10' => 1, 'D12' => 1,
        'D16' => 1, 'D20' => 1, 'D24' => 1, 'D27' => 1, 'D40' => 1, 'D45' => 1, 'D60' => 1,
    ];

    public static function sign(string $varga, float $longitude): string
    {
        if (! array_key_exists($varga, self::DIVISIONS)) {
            throw new InvalidArgumentException("Unknown varga: {$varga}");
        }

        $normalized = AstroMath::normalizeDegrees($longitude);
        $natalSignIndex = (int) floor($normalized / 30);
        $degreeInSign = $normalized - $natalSignIndex * 30;

        if ($varga === 'D1') {
            return ZodiacSigns::NAMES[$natalSignIndex];
        }

        if ($varga === 'D2') {
            return self::horaSign($natalSignIndex, $degreeInSign);
        }

        if ($varga === 'D30') {
            return self::trimsamsaSign($natalSignIndex, $degreeInSign);
        }

        $divisionSpan = 30 / self::DIVISIONS[$varga];
        $segmentIndex = min((int) floor($degreeInSign / $divisionSpan), self::DIVISIONS[$varga] - 1);

        $startIndex = self::startingSignIndex($varga, $natalSignIndex);
        $resultIndex = ($startIndex + $segmentIndex * self::STEP[$varga]) % 12;

        return ZodiacSigns::NAMES[$resultIndex];
    }

    /** Fire=0, Earth=1, Air=2, Water=3 — Aries(0) is fire, and every 4th sign shares an element. */
    private static function element(int $signIndex): int
    {
        return $signIndex % 4;
    }

    /** Movable=0, Fixed=1, Dual=2 — Aries(0) is movable, and every 3rd sign shares a modality. */
    private static function modality(int $signIndex): int
    {
        return $signIndex % 3;
    }

    /** Classical "odd sign" (1st, 3rd, 5th... in 1-indexed terms) — Aries(index 0) is odd. */
    private static function isOddSign(int $signIndex): bool
    {
        return $signIndex % 2 === 0;
    }

    private static function startingSignIndex(string $varga, int $natalSignIndex): int
    {
        return match ($varga) {
            // Drekkana/Chaturthamsa count forward from the natal sign itself (step handles the trikona/kendra spacing).
            'D3', 'D4', 'D12', 'D60' => $natalSignIndex,
            // Saptamsa: odd signs count from themselves, even signs from the 7th sign from them.
            'D7' => self::isOddSign($natalSignIndex) ? $natalSignIndex : ($natalSignIndex + 6) % 12,
            // Navamsa: starts from the fixed sign of the natal sign's element (fire->Aries, earth->Capricorn, air->Libra, water->Cancer).
            'D9' => [0, 9, 6, 3][self::element($natalSignIndex)],
            // Dasamsa: odd signs count from themselves, even signs from the 9th sign from them.
            'D10' => self::isOddSign($natalSignIndex) ? $natalSignIndex : ($natalSignIndex + 8) % 12,
            // Shodasamsa: starts from the fixed sign of the natal sign's modality (movable->Aries, fixed->Leo, dual->Sagittarius).
            'D16' => [0, 4, 8][self::modality($natalSignIndex)],
            // Vimsamsa: movable->Aries, fixed->Sagittarius, dual->Leo.
            'D20' => [0, 8, 4][self::modality($natalSignIndex)],
            // Chaturvimsamsa: odd signs start from Leo (Sun's sign), even signs from Cancer (Moon's sign).
            'D24' => self::isOddSign($natalSignIndex) ? 4 : 3,
            // Saptavimsamsa (Bhamsha): starts from the fixed sign of the natal sign's element (fire->Aries, earth->Cancer, air->Libra, water->Capricorn).
            'D27' => [0, 3, 6, 9][self::element($natalSignIndex)],
            // Khavedamsa: odd signs start from Aries, even signs from Libra.
            'D40' => self::isOddSign($natalSignIndex) ? 0 : 6,
            // Akshavedamsa: movable->Aries, fixed->Leo, dual->Sagittarius (same pattern as Shodasamsa).
            'D45' => [0, 4, 8][self::modality($natalSignIndex)],
            default => throw new InvalidArgumentException("No starting-sign rule for {$varga}"),
        };
    }

    /**
     * Hora (D2) only ever resolves to Cancer (Moon's Hora) or Leo (Sun's
     * Hora) — not the full zodiac. Odd signs: first half is Sun's Hora,
     * second half is Moon's. Even signs: reversed.
     */
    private static function horaSign(int $natalSignIndex, float $degreeInSign): string
    {
        $firstHalf = $degreeInSign < 15;
        $isOdd = self::isOddSign($natalSignIndex);

        $isSunHora = $isOdd ? $firstHalf : ! $firstHalf;

        return $isSunHora ? 'Leo' : 'Cancer';
    }

    /**
     * Trimsamsa (D30): the one varga with unequal degree spans, each
     * assigned to a specific planet's sign rather than counted evenly.
     * Classical (BPHS) rule, odd signs: Mars 0-5 (Aries), Saturn 5-10
     * (Aquarius), Jupiter 10-18 (Sagittarius), Mercury 18-25 (Gemini),
     * Venus 25-30 (Libra). Even signs use the same 5 spans in reverse
     * order with each planet's OTHER sign: Venus 0-5 (Taurus), Mercury
     * 5-12 (Virgo), Jupiter 12-20 (Pisces), Saturn 20-25 (Capricorn),
     * Mars 25-30 (Scorpio).
     */
    private static function trimsamsaSign(int $natalSignIndex, float $degreeInSign): string
    {
        if (self::isOddSign($natalSignIndex)) {
            return match (true) {
                $degreeInSign < 5 => 'Aries',
                $degreeInSign < 10 => 'Aquarius',
                $degreeInSign < 18 => 'Sagittarius',
                $degreeInSign < 25 => 'Gemini',
                default => 'Libra',
            };
        }

        return match (true) {
            $degreeInSign < 5 => 'Taurus',
            $degreeInSign < 12 => 'Virgo',
            $degreeInSign < 20 => 'Pisces',
            $degreeInSign < 25 => 'Capricorn',
            default => 'Scorpio',
        };
    }

    /**
     * Builds the same whole-sign house shape BirthChartCalculator's main
     * chart uses, but from pre-computed varga signs rather than
     * longitudes (a varga chart has no meaningful "degree within sign" of
     * its own — each planet simply occupies a resulting sign).
     *
     * @param  array<string, string>  $planetSigns
     * @return list<array{number: int, sign: string, planets: list<string>}>
     */
    public static function housesFromSigns(string $ascendantSign, array $planetSigns): array
    {
        $ascendantIndex = array_search($ascendantSign, ZodiacSigns::NAMES, true);

        $houses = [];
        for ($houseNumber = 1; $houseNumber <= 12; $houseNumber++) {
            $sign = ZodiacSigns::NAMES[($ascendantIndex + $houseNumber - 1) % 12];

            $houses[] = [
                'number' => $houseNumber,
                'sign' => $sign,
                'planets' => array_keys(array_filter($planetSigns, fn (string $planetSign) => $planetSign === $sign)),
            ];
        }

        return $houses;
    }
}
