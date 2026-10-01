<?php

namespace App\Services\Numerology;

/**
 * The Personality Number: the reduced sum of only the consonants in the
 * birth name — the outward impression others form, complementing
 * SoulUrgeNumber's vowel-only "inner desire" half of the same name.
 */
class PersonalityNumber
{
    public static function forName(string $name): int
    {
        $sum = 0;
        foreach (str_split(strtoupper($name)) as $character) {
            if (PythagoreanAlphabet::isConsonant($character)) {
                $sum += PythagoreanAlphabet::valueOf($character);
            }
        }

        return DigitReducer::reduce($sum);
    }
}
