<?php

namespace App\Services\Numerology;

/**
 * The Pythagorean letter→digit mapping (the numerology system most
 * mainstream apps use, versus the older Chaldean system — a documented
 * choice, not an oversight; swapping systems later only means replacing
 * this table). Letters map to 1-9 in three repeating groups of 9:
 * A/J/S=1, B/K/T=2, ... I/R=9.
 */
class PythagoreanAlphabet
{
    private const LETTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    private const VOWELS = ['A', 'E', 'I', 'O', 'U'];

    /**
     * @return array<string, int>
     */
    public static function letterValues(): array
    {
        $values = [];
        foreach (str_split(self::LETTERS) as $index => $letter) {
            $values[$letter] = ($index % 9) + 1;
        }

        return $values;
    }

    public static function valueOf(string $letter): int
    {
        return self::letterValues()[strtoupper($letter)] ?? 0;
    }

    public static function isVowel(string $letter): bool
    {
        return in_array(strtoupper($letter), self::VOWELS, true);
    }

    /**
     * Y is a well-known edge case in every numerology system (sometimes a
     * vowel, sometimes a consonant, depending on whether it's sounded as
     * one in the name). We follow the common simplified rule most
     * calculators use: Y always counts as a consonant. Documented here so
     * it's a deliberate choice, not a silent gap.
     */
    public static function isConsonant(string $letter): bool
    {
        $upper = strtoupper($letter);

        return ctype_alpha($upper) && ! self::isVowel($upper);
    }
}
