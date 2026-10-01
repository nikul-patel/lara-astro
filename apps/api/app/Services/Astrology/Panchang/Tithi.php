<?php

namespace App\Services\Astrology\Panchang;

use App\Services\Astrology\AstroMath;

/**
 * Tithi: the lunar day, defined by the Moon-minus-Sun angular distance
 * divided into 30 equal 12° segments — 15 "Shukla Paksha" (waxing, Moon
 * ahead of Sun) plus 15 "Krishna Paksha" (waning), each with its own
 * classical name repeating every fortnight except the two that only occur
 * once per lunar month (Purnima/full moon, Amavasya/new moon).
 */
class Tithi
{
    private const SPAN = 360 / 30; // 12°

    public const NAMES = [
        'Pratipada', 'Dwitiya', 'Tritiya', 'Chaturthi', 'Panchami', 'Shashthi', 'Saptami',
        'Ashtami', 'Navami', 'Dashami', 'Ekadashi', 'Dwadashi', 'Trayodashi', 'Chaturdashi',
        'Purnima',
        'Pratipada', 'Dwitiya', 'Tritiya', 'Chaturthi', 'Panchami', 'Shashthi', 'Saptami',
        'Ashtami', 'Navami', 'Dashami', 'Ekadashi', 'Dwadashi', 'Trayodashi', 'Chaturdashi',
        'Amavasya',
    ];

    /**
     * @return array{number: int, name: string, paksha: string}
     */
    public static function forLongitudes(float $sunLongitude, float $moonLongitude): array
    {
        $angle = AstroMath::normalizeDegrees($moonLongitude - $sunLongitude);
        $index = (int) floor($angle / self::SPAN); // 0-29

        return [
            'number' => $index + 1, // 1-30, matching how Panchangs conventionally number tithis
            'name' => self::NAMES[$index],
            'paksha' => $index < 15 ? 'Shukla' : 'Krishna',
        ];
    }
}
