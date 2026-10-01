<?php

namespace App\Services\Numerology;

/**
 * The Soul Urge (a.k.a. Heart's Desire) Number: the reduced sum of only
 * the vowels in the birth name — what the person inwardly wants, versus
 * DestinyNumber's full-name "public purpose" and PersonalityNumber's
 * consonant-only "outward impression".
 */
class SoulUrgeNumber
{
    public static function forName(string $name): int
    {
        $sum = 0;
        foreach (str_split(strtoupper($name)) as $character) {
            if (PythagoreanAlphabet::isVowel($character)) {
                $sum += PythagoreanAlphabet::valueOf($character);
            }
        }

        return DigitReducer::reduce($sum);
    }
}
