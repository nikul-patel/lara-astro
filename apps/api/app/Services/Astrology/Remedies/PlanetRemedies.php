<?php

namespace App\Services\Astrology\Remedies;

/**
 * Curated traditional remedy suggestions per graha — gemstone, mantra,
 * donation (daan), and fasting/observance day. Widely-cited standard
 * associations, not exhaustive classical literature; see RemedyEngine's
 * caution note, which every generated remedy carries alongside this data.
 */
class PlanetRemedies
{
    public const REMEDIES = [
        'Sun' => [
            'gemstone' => 'Ruby',
            'mantra' => 'Om Suryaya Namah',
            'donation' => 'Wheat, jaggery, or copper, given on Sundays',
            'fasting_day' => 'Sunday',
        ],
        'Moon' => [
            'gemstone' => 'Pearl',
            'mantra' => 'Om Chandraya Namah',
            'donation' => 'Rice, milk, or white cloth, given on Mondays',
            'fasting_day' => 'Monday',
        ],
        'Mars' => [
            'gemstone' => 'Red Coral',
            'mantra' => 'Om Angarakaya Namah',
            'donation' => 'Red lentils or jaggery, given on Tuesdays',
            'fasting_day' => 'Tuesday',
        ],
        'Mercury' => [
            'gemstone' => 'Emerald',
            'mantra' => 'Om Budhaya Namah',
            'donation' => 'Green vegetables or green cloth, given on Wednesdays',
            'fasting_day' => 'Wednesday',
        ],
        'Jupiter' => [
            'gemstone' => 'Yellow Sapphire',
            'mantra' => 'Om Gurave Namah',
            'donation' => 'Turmeric, gram, or yellow cloth, given on Thursdays',
            'fasting_day' => 'Thursday',
        ],
        'Venus' => [
            'gemstone' => 'Diamond',
            'mantra' => 'Om Shukraya Namah',
            'donation' => 'White clothes or sweets, given on Fridays',
            'fasting_day' => 'Friday',
        ],
        'Saturn' => [
            'gemstone' => 'Blue Sapphire',
            'mantra' => 'Om Shanaischaraya Namah',
            'donation' => 'Mustard oil, black sesame, or iron, given on Saturdays',
            'fasting_day' => 'Saturday',
        ],
        'Rahu' => [
            'gemstone' => 'Hessonite (Gomed)',
            'mantra' => 'Om Rahave Namah',
            'donation' => 'Mustard, black gram, or blankets, given on Saturdays',
            'fasting_day' => 'Saturday',
        ],
        'Ketu' => [
            'gemstone' => "Cat's Eye (Lehsunia)",
            'mantra' => 'Om Ketave Namah',
            'donation' => 'Sesame seeds or multicolored cloth, given on Saturdays',
            'fasting_day' => 'Saturday',
        ],
    ];
}
