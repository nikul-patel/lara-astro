<?php

use App\Services\Astrology\Transits\Transit;
use App\Services\Astrology\ZodiacSigns;
use Carbon\CarbonImmutable;

test('houseFromMoon() returns 1 when the transiting sign equals the natal Moon sign, counting forward from there', function () {
    expect(Transit::houseFromMoon('Leo', 'Leo'))->toBe(1);
    expect(Transit::houseFromMoon('Virgo', 'Leo'))->toBe(2);
    expect(Transit::houseFromMoon('Cancer', 'Leo'))->toBe(12); // One sign behind wraps to the 12th house.
});

test('houseFromMoon() matches ZodiacSigns::offset() exactly, for every sign pair', function () {
    foreach (ZodiacSigns::NAMES as $moonSign) {
        foreach (ZodiacSigns::NAMES as $transitSign) {
            expect(Transit::houseFromMoon($transitSign, $moonSign))->toBe(ZodiacSigns::offset($moonSign, $transitSign));
        }
    }
});

test('signsAt() returns all 9 classical grahas, each resolved to a valid zodiac sign', function () {
    $signs = Transit::signsAt(CarbonImmutable::parse('2026-08-22 12:00:00', 'UTC'));

    expect(array_keys($signs))->toBe(['Sun', 'Moon', 'Mercury', 'Venus', 'Mars', 'Jupiter', 'Saturn', 'Rahu', 'Ketu']);
    foreach ($signs as $sign) {
        expect($sign)->toBeIn(ZodiacSigns::NAMES);
    }
});

test('Rahu and Ketu are always exactly opposite signs, at any transit moment', function () {
    $signs = Transit::signsAt(CarbonImmutable::parse('2030-03-15 06:00:00', 'UTC'));

    $rahuIndex = array_search($signs['Rahu'], ZodiacSigns::NAMES, true);
    $ketuIndex = array_search($signs['Ketu'], ZodiacSigns::NAMES, true);

    expect(($ketuIndex - $rahuIndex + 12) % 12)->toBe(6);
});

test('forNatalMoon() pairs each transit sign with its house-from-Moon, consistently', function () {
    $transitSigns = ['Sun' => 'Libra', 'Moon' => 'Capricorn'];

    $result = Transit::forNatalMoon($transitSigns, 'Cancer');

    expect($result['Sun'])->toBe(['sign' => 'Libra', 'house_from_moon' => 4]);
    expect($result['Moon'])->toBe(['sign' => 'Capricorn', 'house_from_moon' => 7]);
});
