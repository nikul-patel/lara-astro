<?php

namespace App\Services\Astrology\Predictions;

use App\Services\Astrology\HouseLords;
use App\Services\Astrology\PlanetaryDignity;
use App\Services\Astrology\PlanetaryFriendship;

/**
 * Sign-level dignity on the classical seven-step scale (exalted >
 * moolatrikona > own > friendly > neutral > enemy > debilitated), with the
 * friendly/neutral/enemy steps judged by the planet's natural (Naisargika)
 * relationship to the sign's ruler. Rahu and Ketu get only exalted/
 * debilitated (PlanetaryDignity's stated Taurus/Scorpio convention) or
 * 'node' — classical texts give them no rulership-based dignity.
 */
class PlanetStrength
{
    public const NATURAL_BENEFICS = ['Jupiter', 'Venus', 'Mercury', 'Moon'];

    public const NATURAL_MALEFICS = ['Sun', 'Mars', 'Saturn', 'Rahu', 'Ketu'];

    private const SCORE = [
        'exalted' => 3, 'moolatrikona' => 2, 'own' => 2, 'friendly' => 1,
        'neutral' => 0, 'node' => 0, 'enemy' => -1, 'debilitated' => -3,
    ];

    public static function dignity(string $planet, string $sign, ?float $longitude = null): string
    {
        if (PlanetaryDignity::isExalted($planet, $sign)) {
            return 'exalted';
        }

        if (PlanetaryDignity::isDebilitated($planet, $sign)) {
            return 'debilitated';
        }

        if (in_array($planet, ['Rahu', 'Ketu'], true)) {
            return 'node';
        }

        $moolatrikona = PlanetaryDignity::MOOLATRIKONA[$planet] ?? null;
        if ($moolatrikona !== null && $moolatrikona['sign'] === $sign && $longitude !== null) {
            $degree = $longitude - floor($longitude / 30) * 30;
            if ($degree >= $moolatrikona['from'] && $degree < $moolatrikona['to']) {
                return 'moolatrikona';
            }
        }

        if (PlanetaryDignity::isOwnSign($planet, $sign)) {
            return 'own';
        }

        return match (PlanetaryFriendship::relationship($planet, HouseLords::SIGN_RULERS[$sign])) {
            'friend' => 'friendly',
            'enemy' => 'enemy',
            default => 'neutral',
        };
    }

    public static function score(string $dignity): int
    {
        return self::SCORE[$dignity];
    }

    public static function isDignified(string $dignity): bool
    {
        return in_array($dignity, ['exalted', 'moolatrikona', 'own'], true);
    }
}
