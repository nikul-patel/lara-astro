<?php

namespace App\Services\Astrology\Panchang;

use App\Services\Astrology\AstroMath;

/**
 * Nitya Yoga (a.k.a. Panchang Yoga): the Sun+Moon longitude sum divided
 * into 27 equal 13°20' segments, each with its own classical name — a
 * completely different concept from the planetary-combination "Yoga" in
 * Services/Astrology/YogaEngine (Raj Yoga etc.), hence this class living
 * under the Panchang namespace with an unambiguous "Nitya" prefix.
 */
class NityaYoga
{
    private const SPAN = 360 / 27; // 13°20'

    public const NAMES = [
        'Vishkambha', 'Priti', 'Ayushman', 'Saubhagya', 'Shobhana', 'Atiganda', 'Sukarma',
        'Dhriti', 'Shula', 'Ganda', 'Vriddhi', 'Dhruva', 'Vyaghata', 'Harshana', 'Vajra',
        'Siddhi', 'Vyatipata', 'Variyana', 'Parigha', 'Shiva', 'Siddha', 'Sadhya', 'Shubha',
        'Shukla', 'Brahma', 'Indra', 'Vaidhriti',
    ];

    /**
     * @return array{index: int, name: string}
     */
    public static function forLongitudes(float $sunLongitude, float $moonLongitude): array
    {
        $sum = AstroMath::normalizeDegrees($sunLongitude + $moonLongitude);
        $index = (int) floor($sum / self::SPAN);

        return ['index' => $index, 'name' => self::NAMES[$index]];
    }
}
