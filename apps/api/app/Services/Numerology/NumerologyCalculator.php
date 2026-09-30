<?php

namespace App\Services\Numerology;

use Carbon\CarbonImmutable;

/**
 * Orchestrates a full numerology reading: Life Path (from the birth date)
 * plus Destiny/Soul Urge/Personality (from the full birth name), each with
 * a short interpretation. A separate, deliberately astronomy-free system
 * from Services/Astrology — no chart, no ephemeris, pure arithmetic on the
 * name and date — so it lives in its own top-level namespace.
 */
class NumerologyCalculator
{
    /**
     * @param  array{name: string, dob: string}  $input
     * @return array{
     *     life_path: array{number: int, meaning: string},
     *     destiny: array{number: int, meaning: string},
     *     soul_urge: array{number: int, meaning: string},
     *     personality: array{number: int, meaning: string},
     * }
     */
    public static function calculate(array $input): array
    {
        $dob = CarbonImmutable::parse($input['dob']);
        $name = $input['name'];

        $lifePath = LifePathNumber::forDate($dob);
        $destiny = DestinyNumber::forName($name);
        $soulUrge = SoulUrgeNumber::forName($name);
        $personality = PersonalityNumber::forName($name);

        return [
            'life_path' => ['number' => $lifePath, 'meaning' => NumerologyMeanings::forNumber($lifePath)],
            'destiny' => ['number' => $destiny, 'meaning' => NumerologyMeanings::forNumber($destiny)],
            'soul_urge' => ['number' => $soulUrge, 'meaning' => NumerologyMeanings::forNumber($soulUrge)],
            'personality' => ['number' => $personality, 'meaning' => NumerologyMeanings::forNumber($personality)],
        ];
    }
}
