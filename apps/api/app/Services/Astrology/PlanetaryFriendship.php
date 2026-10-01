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
}
