<?php

namespace App\Services\Astrology\Matching\Kootas;

use App\Services\Astrology\HouseLords;
use App\Services\Astrology\PlanetaryFriendship;

/**
 * Graha Maitri Koota (5 points): compares the natural Parashari friendship
 * between the planets ruling each partner's Moon sign. Uses the standard
 * 5-point scale — both-friends 5, friend/neutral 4, both-neutral 3,
 * friend/enemy 1, neutral/enemy 0.5, both-enemies 0 — applied to the
 * ordered pair (rules aren't always symmetric, e.g. Moon counts Mercury a
 * friend but Mercury counts Moon an enemy). The underlying friendship
 * table lives in PlanetaryFriendship, shared with other features that need
 * natural friendship (e.g. Avkahada Chakra's "good planets").
 */
class GrahaMaitriKoota
{
    public const MAX_POINTS = 5;

    /**
     * @return array{name: string, points: float, max_points: float, description: string}
     */
    public static function evaluate(string $brideRashi, string $groomRashi): array
    {
        $brideLord = HouseLords::SIGN_RULERS[$brideRashi];
        $groomLord = HouseLords::SIGN_RULERS[$groomRashi];

        $brideToGroom = PlanetaryFriendship::relationship($brideLord, $groomLord);
        $groomToBride = PlanetaryFriendship::relationship($groomLord, $brideLord);

        $points = match (true) {
            $brideToGroom === 'friend' && $groomToBride === 'friend' => 5.0,
            in_array('friend', [$brideToGroom, $groomToBride], true) && in_array('neutral', [$brideToGroom, $groomToBride], true) => 4.0,
            $brideToGroom === 'neutral' && $groomToBride === 'neutral' => 3.0,
            in_array('friend', [$brideToGroom, $groomToBride], true) && in_array('enemy', [$brideToGroom, $groomToBride], true) => 1.0,
            in_array('neutral', [$brideToGroom, $groomToBride], true) && in_array('enemy', [$brideToGroom, $groomToBride], true) => 0.5,
            default => 0.0,
        };

        return [
            'name' => 'Graha Maitri',
            'points' => $points,
            'max_points' => self::MAX_POINTS,
            'description' => "Rashi lords {$brideLord} (bride) and {$groomLord} (groom): {$brideToGroom}/{$groomToBride} mutual relationship.",
        ];
    }
}
