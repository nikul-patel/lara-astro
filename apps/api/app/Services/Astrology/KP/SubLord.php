<?php

namespace App\Services\Astrology\KP;

use App\Services\Astrology\Nakshatra;
use App\Services\Astrology\VimshottariDasha;

/**
 * Krishnamurti Paddhati (KP) sub-lord theory: each of the 27 nakshatras
 * (13°20' / 800 arcminutes) further subdivides into the same 9 Vimshottari
 * lords, in the same fixed cyclical order (Nakshatra::LORD_CYCLE),
 * starting from the nakshatra's OWN lord — the exact same
 * self-similar-subdivision rule VimshottariDasha::subPeriods() applies to
 * TIME (an Antardasha-within-Mahadasha), here applied to ARC instead: each
 * sub-lord's span is the nakshatra's 800' scaled by that lord's share of
 * the 120-year cycle. This gives 27 x 9 = 249 sub-divisions across the
 * zodiac, each with a genuinely unequal, verifiable span — e.g. Ketu's
 * sub-span is always 800' x 7/120 = 46'40", Venus's always 800' x 20/120 =
 * 2°13'20" (both independently well-known constants in published KP
 * sub-lord tables, not just self-consistent arithmetic).
 *
 * Ayanamsa note: traditional KP practice uses the Krishnamurti ayanamsa,
 * not Lahiri. The two are calibrated to be very close (Krishnamurti was
 * designed to track Lahiri closely, within roughly an arcminute in the
 * modern era) but aren't identical, so a sub-lord boundary a longitude
 * sits very near could occasionally differ from "pure" KP software. This
 * engine doesn't implement a second ayanamsa — sub-lords here are computed
 * from the same Lahiri sidereal longitudes every other Vedic feature uses,
 * a documented simplification rather than an attempt at KP-exact
 * ayanamsa precision.
 */
class SubLord
{
    private const NAKSHATRA_SPAN = 360 / 27; // 13°20' = 800'

    private const CYCLE_YEARS = 120;

    /**
     * The 9 sub-lord spans within one nakshatra, in degrees-within-
     * nakshatra (0 to 13.3333...), cycling Nakshatra::LORD_CYCLE starting
     * from $nakshatraLord.
     *
     * @return list<array{lord: string, from: float, to: float}>
     */
    public static function boundaries(string $nakshatraLord): array
    {
        $lords = Nakshatra::LORD_CYCLE;
        $startIndex = array_search($nakshatraLord, $lords, true);

        $boundaries = [];
        $cursor = 0.0;

        for ($i = 0; $i < 9; $i++) {
            $lord = $lords[($startIndex + $i) % 9];
            $span = self::NAKSHATRA_SPAN * VimshottariDasha::LORD_YEARS[$lord] / self::CYCLE_YEARS;

            $from = $cursor;
            $to = $i === 8 ? self::NAKSHATRA_SPAN : $cursor + $span;

            $boundaries[] = ['lord' => $lord, 'from' => $from, 'to' => $to];
            $cursor = $to;
        }

        return $boundaries;
    }

    /**
     * @return array{nakshatra: string, nakshatra_lord: string, pada: int, sub_lord: string}
     */
    public static function forLongitude(float $siderealLongitude): array
    {
        $nakshatra = Nakshatra::forLongitude($siderealLongitude);
        $withinNakshatra = Nakshatra::fractionElapsed($siderealLongitude) * self::NAKSHATRA_SPAN;

        $subLord = $nakshatra['lord'];
        foreach (self::boundaries($nakshatra['lord']) as $boundary) {
            if ($withinNakshatra >= $boundary['from'] && $withinNakshatra < $boundary['to']) {
                $subLord = $boundary['lord'];
                break;
            }
        }

        return [
            'nakshatra' => $nakshatra['name'],
            'nakshatra_lord' => $nakshatra['lord'],
            'pada' => $nakshatra['pada'],
            'sub_lord' => $subLord,
        ];
    }
}
