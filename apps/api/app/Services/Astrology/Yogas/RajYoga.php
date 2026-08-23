<?php

namespace App\Services\Astrology\Yogas;

use App\Services\Astrology\HouseLords;

/**
 * Raj yoga: a kendra lord (1st/4th/7th/10th) and a trikona lord
 * (1st/5th/9th) linked by conjunction, exchange, or mutual aspect —
 * classically the archetypal combination for status, authority, and rise
 * in life. The 1st house is both a kendra and a trikona; its own lord
 * pairing with itself is skipped (LordRelationship::linked() already
 * returns false for identical planets, but the deduplication below also
 * collapses e.g. the (4th, 5th) and (5th, 4th) pairs discovered from
 * different house combinations into one reported yoga).
 */
class RajYoga
{
    private const KENDRA_HOUSES = [1, 4, 7, 10];

    private const TRIKONA_HOUSES = [1, 5, 9];

    /**
     * @param  list<array{number: int, sign: string, planets: list<string>}>  $houses
     * @return list<array{key: string, name: string, category: string, planets: list<string>, houses: list<int>, description: string}>
     */
    public static function detect(array $houses): array
    {
        $found = [];
        $seenPairs = [];

        foreach (self::KENDRA_HOUSES as $kendra) {
            $kendraLord = HouseLords::lordOfHouse($kendra, $houses);

            foreach (self::TRIKONA_HOUSES as $trikona) {
                if ($kendra === $trikona) {
                    continue;
                }

                $trikonaLord = HouseLords::lordOfHouse($trikona, $houses);
                $pairKey = implode('-', collect([$kendraLord, $trikonaLord])->sort()->all());

                if (isset($seenPairs[$pairKey]) || ! LordRelationship::linked($kendraLord, $kendra, $trikonaLord, $trikona, $houses)) {
                    continue;
                }

                $seenPairs[$pairKey] = true;
                $found[] = [
                    'key' => 'raj_yoga',
                    'name' => 'Raj Yoga',
                    'category' => 'status_and_authority',
                    'planets' => [$kendraLord, $trikonaLord],
                    'houses' => [$kendra, $trikona],
                    'description' => "The {$kendra}th-house lord ({$kendraLord}) and {$trikona}th-house lord ({$trikonaLord}) are linked, forming a Raj Yoga associated with rising status and authority.",
                ];
            }
        }

        return $found;
    }
}
