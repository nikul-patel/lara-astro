<?php

namespace App\Services\Numerology;

/**
 * The Destiny (a.k.a. Expression) Number: the reduced sum of every letter
 * in the full birth name, via the Pythagorean alphabet. Represents life
 * purpose/potential in mainstream numerology, distinct from the Life Path
 * Number (which comes from the birth date, not the name).
 */
class DestinyNumber
{
    public static function forName(string $name): int
    {
        $sum = 0;
        foreach (str_split(strtoupper($name)) as $character) {
            if (ctype_alpha($character)) {
                $sum += PythagoreanAlphabet::valueOf($character);
            }
        }

        return DigitReducer::reduce($sum);
    }
}
