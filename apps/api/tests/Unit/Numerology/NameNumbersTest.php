<?php

use App\Services\Numerology\DestinyNumber;
use App\Services\Numerology\PersonalityNumber;
use App\Services\Numerology\SoulUrgeNumber;

test('Destiny, Soul Urge, and Personality partition the same letters consistently', function () {
    // "JOHN SMITH" Pythagorean values: J=1 O=6 H=8 N=5 S=1 M=4 I=9 T=2 H=8.
    // Vowels (O, I) = 6+9=15 -> 1+5=6. Consonants (J,H,N,S,M,T,H) = 29 -> 2+9=11 (Master Number, stops there).
    // Full-name sum = 15+29=44 -> 4+4=8, matching vowels-raw + consonants-raw before each is independently reduced.
    expect(SoulUrgeNumber::forName('JOHN SMITH'))->toBe(6)
        ->and(PersonalityNumber::forName('JOHN SMITH'))->toBe(11)
        ->and(DestinyNumber::forName('JOHN SMITH'))->toBe(8);
});

test('non-alphabetic characters in a name are ignored', function () {
    expect(DestinyNumber::forName('Jo-Hn'))->toBe(DestinyNumber::forName('John'));
});
