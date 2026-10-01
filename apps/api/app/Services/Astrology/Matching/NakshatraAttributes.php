<?php

namespace App\Services\Astrology\Matching;

/**
 * Classification tables keyed by nakshatra name (App\Services\Astrology\
 * Nakshatra::NAMES) or rashi name (App\Services\Astrology\ZodiacSigns::
 * NAMES), shared by the Ashtakoot koota classes in this namespace. Each
 * table is the standard set published in classical Muhurta/matchmaking
 * texts; Vashya is the one documented simplification (see below).
 */
class NakshatraAttributes
{
    /**
     * Animal symbol and gender for each nakshatra, used by YoniKoota. 14
     * animals map onto the 27 nakshatras, several repeating with the
     * opposite gender (e.g. Purva/Uttara Ashadha aren't a pair here —
     * Ashwini's Horse-M pairs with Shatabhisha's Horse-F, etc.).
     *
     * @var array<string, array{animal: string, gender: string}>
     */
    public const YONI = [
        'Ashwini' => ['animal' => 'Horse', 'gender' => 'M'],
        'Bharani' => ['animal' => 'Elephant', 'gender' => 'M'],
        'Krittika' => ['animal' => 'Goat', 'gender' => 'F'],
        'Rohini' => ['animal' => 'Serpent', 'gender' => 'M'],
        'Mrigashira' => ['animal' => 'Serpent', 'gender' => 'F'],
        'Ardra' => ['animal' => 'Dog', 'gender' => 'F'],
        'Punarvasu' => ['animal' => 'Cat', 'gender' => 'F'],
        'Pushya' => ['animal' => 'Goat', 'gender' => 'M'],
        'Ashlesha' => ['animal' => 'Cat', 'gender' => 'M'],
        'Magha' => ['animal' => 'Rat', 'gender' => 'M'],
        'Purva Phalguni' => ['animal' => 'Rat', 'gender' => 'F'],
        'Uttara Phalguni' => ['animal' => 'Cow', 'gender' => 'F'],
        'Hasta' => ['animal' => 'Buffalo', 'gender' => 'F'],
        'Chitra' => ['animal' => 'Tiger', 'gender' => 'F'],
        'Swati' => ['animal' => 'Buffalo', 'gender' => 'M'],
        'Vishakha' => ['animal' => 'Tiger', 'gender' => 'M'],
        'Anuradha' => ['animal' => 'Deer', 'gender' => 'F'],
        'Jyeshtha' => ['animal' => 'Deer', 'gender' => 'M'],
        'Mula' => ['animal' => 'Dog', 'gender' => 'M'],
        'Purva Ashadha' => ['animal' => 'Monkey', 'gender' => 'F'],
        'Uttara Ashadha' => ['animal' => 'Mongoose', 'gender' => 'M'],
        'Shravana' => ['animal' => 'Monkey', 'gender' => 'M'],
        'Dhanishta' => ['animal' => 'Lion', 'gender' => 'F'],
        'Shatabhisha' => ['animal' => 'Horse', 'gender' => 'F'],
        'Purva Bhadrapada' => ['animal' => 'Lion', 'gender' => 'M'],
        'Uttara Bhadrapada' => ['animal' => 'Cow', 'gender' => 'M'],
        'Revati' => ['animal' => 'Elephant', 'gender' => 'F'],
    ];

    /**
     * Classical natural-enemy animal pairs (order-independent) for
     * YoniKoota's worst-case score.
     *
     * @var list<array{0: string, 1: string}>
     */
    public const YONI_ENEMIES = [
        ['Cow', 'Tiger'],
        ['Horse', 'Buffalo'],
        ['Elephant', 'Lion'],
        ['Dog', 'Deer'],
        ['Goat', 'Monkey'],
        ['Serpent', 'Mongoose'],
        ['Rat', 'Cat'],
    ];

    /** @var array<string, list<string>> */
    public const GANA = [
        'Deva' => ['Ashwini', 'Mrigashira', 'Punarvasu', 'Pushya', 'Hasta', 'Swati', 'Anuradha', 'Shravana', 'Revati'],
        'Manushya' => ['Bharani', 'Rohini', 'Ardra', 'Purva Phalguni', 'Uttara Phalguni', 'Purva Ashadha', 'Uttara Ashadha', 'Purva Bhadrapada', 'Uttara Bhadrapada'],
        'Rakshasa' => ['Krittika', 'Ashlesha', 'Magha', 'Chitra', 'Vishakha', 'Jyeshtha', 'Mula', 'Dhanishta', 'Shatabhisha'],
    ];

    /** @var array<string, list<string>> */
    public const NADI = [
        'Aadi' => ['Ashwini', 'Ardra', 'Punarvasu', 'Uttara Phalguni', 'Hasta', 'Jyeshtha', 'Mula', 'Shatabhisha', 'Purva Bhadrapada'],
        'Madhya' => ['Bharani', 'Mrigashira', 'Pushya', 'Purva Phalguni', 'Chitra', 'Anuradha', 'Purva Ashadha', 'Dhanishta', 'Uttara Bhadrapada'],
        'Antya' => ['Krittika', 'Rohini', 'Ashlesha', 'Magha', 'Swati', 'Vishakha', 'Uttara Ashadha', 'Shravana', 'Revati'],
    ];

    /** @var array<string, list<string>> */
    public const VARNA = [
        'Brahmin' => ['Cancer', 'Scorpio', 'Pisces'],
        'Kshatriya' => ['Aries', 'Leo', 'Sagittarius'],
        'Vaishya' => ['Taurus', 'Virgo', 'Capricorn'],
        'Shudra' => ['Gemini', 'Libra', 'Aquarius'],
    ];

    /** Hierarchy rank, highest first — VarnaKoota needs the ordering, not just the grouping. */
    public const VARNA_RANK = ['Brahmin', 'Kshatriya', 'Vaishya', 'Shudra'];

    /**
     * Vashya groups whole-sign rashi into. Classical texts split a few
     * signs (Leo's latter half, Sagittarius/Capricorn's animal-vs-human
     * halves) across two groups; this codebase's astrology engine only
     * resolves planets to a whole sign (see BirthChartCalculator's
     * precision note), so each sign here is assigned to the single group
     * its majority/primary classification belongs to rather than split.
     *
     * @var array<string, list<string>>
     */
    public const VASHYA = [
        'Chatushpada' => ['Aries', 'Taurus', 'Sagittarius', 'Capricorn'],
        'Manav' => ['Gemini', 'Virgo', 'Libra', 'Aquarius'],
        'Jalachar' => ['Cancer', 'Pisces'],
        'Vanchar' => ['Leo'],
        'Keet' => ['Scorpio'],
    ];

    public static function yoniOf(string $nakshatra): string
    {
        return self::YONI[$nakshatra]['animal'];
    }

    public static function ganaOf(string $nakshatra): string
    {
        foreach (self::GANA as $gana => $members) {
            if (in_array($nakshatra, $members, true)) {
                return $gana;
            }
        }

        throw new \InvalidArgumentException("Unknown nakshatra: {$nakshatra}");
    }

    public static function nadiOf(string $nakshatra): string
    {
        foreach (self::NADI as $nadi => $members) {
            if (in_array($nakshatra, $members, true)) {
                return $nadi;
            }
        }

        throw new \InvalidArgumentException("Unknown nakshatra: {$nakshatra}");
    }

    public static function varnaOf(string $rashi): string
    {
        foreach (self::VARNA as $varna => $members) {
            if (in_array($rashi, $members, true)) {
                return $varna;
            }
        }

        throw new \InvalidArgumentException("Unknown rashi: {$rashi}");
    }

    public static function vashyaOf(string $rashi): string
    {
        foreach (self::VASHYA as $vashya => $members) {
            if (in_array($rashi, $members, true)) {
                return $vashya;
            }
        }

        throw new \InvalidArgumentException("Unknown rashi: {$rashi}");
    }
}
