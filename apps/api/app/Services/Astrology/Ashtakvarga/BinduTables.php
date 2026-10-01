<?php

namespace App\Services\Astrology\Ashtakvarga;

/**
 * The classical Bhinnashtakavarga bindu (point) tables from Brihat Parashara
 * Hora Shastra: for each of the 7 classical planets' own Ashtakvarga, each
 * of the 8 contributors (the 7 planets plus the Ascendant) grants a bindu to
 * specific houses counted from that contributor's own position. These are
 * fixed, universally-published classical tables — not derived or
 * computed — so they're transcribed here verbatim rather than built from a
 * formula.
 *
 * Self-consistency check (not a formal citation, but a genuine verification
 * performed while transcribing these tables): each planet's own total bindu
 * count, summed across all 8 contributors' house-lists, equals that
 * planet's well-known classical total — Sun 48, Moon 49, Mars 39,
 * Mercury 54, Jupiter 56, Venus 52, Saturn 39 (337 altogether). See
 * {@see BinduTablesTest} for the automated version of this check.
 */
class BinduTables
{
    /**
     * @var array<string, array<string, list<int>>>
     */
    public const TABLES = [
        'Sun' => [
            'Sun' => [1, 2, 4, 7, 8, 9, 10, 11],
            'Moon' => [3, 6, 10, 11],
            'Mars' => [1, 2, 4, 7, 8, 9, 10, 11],
            'Mercury' => [3, 5, 6, 9, 10, 11, 12],
            'Jupiter' => [5, 6, 9, 11],
            'Venus' => [6, 7, 12],
            'Saturn' => [1, 2, 4, 7, 8, 9, 10, 11],
            'Ascendant' => [3, 4, 6, 10, 11, 12],
        ],
        'Moon' => [
            'Sun' => [3, 6, 7, 8, 10, 11],
            'Moon' => [1, 3, 6, 7, 10, 11],
            'Mars' => [2, 3, 5, 6, 9, 10, 11],
            'Mercury' => [1, 3, 4, 5, 7, 8, 10, 11],
            'Jupiter' => [1, 4, 7, 8, 10, 11, 12],
            'Venus' => [3, 4, 5, 7, 9, 10, 11],
            'Saturn' => [3, 5, 6, 11],
            'Ascendant' => [3, 6, 10, 11],
        ],
        'Mars' => [
            'Sun' => [3, 5, 6, 10, 11],
            'Moon' => [3, 6, 11],
            'Mars' => [1, 2, 4, 7, 8, 10, 11],
            'Mercury' => [3, 5, 6, 11],
            'Jupiter' => [6, 10, 11, 12],
            'Venus' => [6, 8, 11, 12],
            'Saturn' => [1, 4, 7, 8, 9, 10, 11],
            'Ascendant' => [1, 3, 6, 10, 11],
        ],
        'Mercury' => [
            'Sun' => [5, 6, 9, 11, 12],
            'Moon' => [2, 4, 6, 8, 10, 11],
            'Mars' => [1, 2, 4, 7, 8, 9, 10, 11],
            'Mercury' => [1, 3, 5, 6, 9, 10, 11, 12],
            'Jupiter' => [6, 8, 11, 12],
            'Venus' => [1, 2, 3, 4, 5, 8, 9, 11],
            'Saturn' => [1, 2, 4, 7, 8, 9, 10, 11],
            'Ascendant' => [1, 2, 4, 6, 8, 10, 11],
        ],
        'Jupiter' => [
            'Sun' => [1, 2, 3, 4, 7, 8, 9, 10, 11],
            'Moon' => [2, 5, 7, 9, 11],
            'Mars' => [1, 2, 4, 7, 8, 10, 11],
            'Mercury' => [1, 2, 4, 5, 6, 9, 10, 11],
            'Jupiter' => [1, 2, 3, 4, 7, 8, 10, 11],
            'Venus' => [2, 5, 6, 9, 10, 11],
            'Saturn' => [3, 5, 6, 12],
            'Ascendant' => [1, 2, 4, 5, 6, 7, 9, 10, 11],
        ],
        'Venus' => [
            'Sun' => [8, 11, 12],
            'Moon' => [1, 2, 3, 4, 5, 8, 9, 11, 12],
            'Mars' => [3, 5, 6, 9, 11, 12],
            'Mercury' => [3, 5, 6, 9, 11],
            'Jupiter' => [5, 8, 9, 10, 11],
            'Venus' => [1, 2, 3, 4, 5, 8, 9, 10, 11],
            'Saturn' => [3, 4, 5, 8, 9, 10, 11],
            'Ascendant' => [1, 2, 3, 4, 5, 8, 9, 11],
        ],
        'Saturn' => [
            'Sun' => [1, 2, 4, 7, 8, 10, 11],
            'Moon' => [3, 6, 11],
            'Mars' => [3, 5, 6, 10, 11, 12],
            'Mercury' => [6, 8, 9, 10, 11, 12],
            'Jupiter' => [5, 6, 11, 12],
            'Venus' => [6, 11, 12],
            'Saturn' => [3, 5, 6, 11],
            'Ascendant' => [1, 3, 4, 6, 10, 11],
        ],
    ];

    public const SUBJECT_PLANETS = ['Sun', 'Moon', 'Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn'];

    public const CONTRIBUTORS = ['Sun', 'Moon', 'Mars', 'Mercury', 'Jupiter', 'Venus', 'Saturn', 'Ascendant'];

    /** Each subject planet's known classical total bindu count across all 12 signs (sums to 337). */
    public const KNOWN_TOTALS = [
        'Sun' => 48,
        'Moon' => 49,
        'Mars' => 39,
        'Mercury' => 54,
        'Jupiter' => 56,
        'Venus' => 52,
        'Saturn' => 39,
    ];
}
