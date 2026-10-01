<?php

namespace App\Services\Astrology;

/**
 * Natural (Naisargika) Parashari planetary friendship — fixed, independent
 * of any chart, unlike temporal friendship (which depends on each planet's
 * house-distance from every other planet in a specific chart). Promoted
 * here from Matching\Kootas\GrahaMaitriKoota (which used to keep this
 * table to itself) so other features needing "is X naturally friendly to
 * Y" don't duplicate it.
 */
class PlanetaryFriendship
{
    /** @var array<string, list<string>> */
    public const FRIENDS = [
        'Sun' => ['Moon', 'Mars', 'Jupiter'],
        'Moon' => ['Sun', 'Mercury'],
        'Mars' => ['Sun', 'Moon', 'Jupiter'],
        'Mercury' => ['Sun', 'Venus'],
        'Jupiter' => ['Sun', 'Moon', 'Mars'],
        'Venus' => ['Mercury', 'Saturn'],
        'Saturn' => ['Mercury', 'Venus'],
    ];

    /** @var array<string, list<string>> */
    public const ENEMIES = [
        'Sun' => ['Venus', 'Saturn'],
        'Moon' => [],
        'Mars' => ['Mercury'],
        'Mercury' => ['Moon'],
        'Jupiter' => ['Mercury', 'Venus'],
        'Venus' => ['Sun', 'Moon'],
        'Saturn' => ['Sun', 'Moon', 'Mars'],
    ];

    public const CLASSICAL_PLANETS = ['Sun', 'Moon', 'Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn'];

    /** Temporal friendship house-offsets (2nd/3rd/4th/10th/11th/12th from a planet are temporal friends; everything else, including the planet's own sign, is a temporal enemy). */
    private const TEMPORAL_FRIEND_OFFSETS = [2, 3, 4, 10, 11, 12];

    /** Whether $from naturally counts $to as friend/neutral/enemy — not necessarily symmetric (e.g. Moon counts Mercury a friend, Mercury counts Moon an enemy). */
    public static function relationship(string $from, string $to): string
    {
        if (in_array($to, self::FRIENDS[$from], true)) {
            return 'friend';
        }

        if (in_array($to, self::ENEMIES[$from], true)) {
            return 'enemy';
        }

        return 'neutral';
    }

    /**
     * Tatkalika (temporal) friendship: depends only on the current chart's
     * sign-distance between the two planets, unlike the fixed natural
     * table above.
     */
    public static function temporalRelationship(string $fromSign, string $toSign): string
    {
        $offset = ZodiacSigns::offset($fromSign, $toSign);

        return in_array($offset, self::TEMPORAL_FRIEND_OFFSETS, true) ? 'friend' : 'enemy';
    }

    /**
     * Panchadha Maitri: the standard 5-level combination of natural and
     * temporal friendship — natural+temporal friend is the closest bond
     * (Adhi Mitra), natural+temporal enemy the bitterest (Adhi Shatru),
     * and a mismatch between the two settles to neutral or to whichever
     * extreme the temporal reading pulls it toward.
     */
    public static function combined(string $natural, string $temporal): string
    {
        return match (true) {
            $natural === 'friend' && $temporal === 'friend' => 'great_friend',
            $natural === 'friend' && $temporal === 'enemy' => 'neutral',
            $natural === 'neutral' && $temporal === 'friend' => 'friend',
            $natural === 'neutral' && $temporal === 'enemy' => 'enemy',
            $natural === 'enemy' && $temporal === 'friend' => 'neutral',
            default => 'great_enemy',
        };
    }

    /**
     * The full 7x6 (42-row) Panchadha Maitri table for a chart's 7
     * classical planets.
     *
     * @param  array<string, string>  $planetSigns  Planet name => sign, must include all 7 classical planets.
     * @return list<array{from: string, to: string, natural: string, temporal: string, combined: string}>
     */
    public static function table(array $planetSigns): array
    {
        $rows = [];

        foreach (self::CLASSICAL_PLANETS as $from) {
            foreach (self::CLASSICAL_PLANETS as $to) {
                if ($from === $to) {
                    continue;
                }

                $natural = self::relationship($from, $to);
                $temporal = self::temporalRelationship($planetSigns[$from], $planetSigns[$to]);

                $rows[] = [
                    'from' => $from,
                    'to' => $to,
                    'natural' => $natural,
                    'temporal' => $temporal,
                    'combined' => self::combined($natural, $temporal),
                ];
            }
        }

        return $rows;
    }
}
