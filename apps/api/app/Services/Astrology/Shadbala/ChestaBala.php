<?php

namespace App\Services\Astrology\Shadbala;

use App\Services\Astrology\PlanetaryElements;

/**
 * Chesta Bala ("motional strength"): classically the angular distance
 * between a planet's true and mean position in the geocentric synodic
 * (sighra) cycle — maximum when a planet is retrograde or stationary
 * ("exerting effort" against its mean motion), falling toward zero at its
 * fastest direct motion. Only the 5 "star" planets (Mars, Mercury,
 * Jupiter, Venus, Saturn) get a Chesta Bala in the classical system — the
 * Sun and Moon never retrograde, so they're excluded here just as they are
 * from every published Shadbala table.
 *
 * The exact classical formula needs each planet's Surya-Siddhantic mean
 * longitude, a wholly separate mean-motion model this engine doesn't
 * implement (it uses Meeus/Standish throughout — see
 * PlanetaryElements's own doc comment). Reproducing that model exactly
 * without a way to verify it against an authoritative source wasn't
 * possible in this environment, so this is an explicit, documented
 * approximation instead of an unverified guess: apparent daily motion
 * (speed), found by numerically differentiating this engine's own
 * existing geocentric longitude function one day apart, compared against
 * each planet's well-known mean daily motion (360° / orbital period —
 * independently verifiable: Mars ≈687d→0.524°/d, Mercury ≈88d→4.092°/d,
 * Jupiter ≈4333d→0.0831°/d, Venus ≈225d→1.602°/d, Saturn ≈10759d→0.0334°/d).
 * This correctly captures the classical rule's DIRECTION (retrograde/
 * stationary = strongest, fast direct = weakest) and is independently
 * verifiable via any known retrograde window, but its 0-60 scaling curve
 * is this engine's own linear approximation, not a classical-formula-exact
 * replica.
 */
class ChestaBala
{
    private const MEAN_DAILY_MOTION = [
        'Mars' => 0.5240, 'Mercury' => 4.0923, 'Jupiter' => 0.0831,
        'Venus' => 1.60215, 'Saturn' => 0.033439,
    ];

    /** @return array<string, float> Planet => Virupas (max 60, min 0); only the 5 star planets are keyed. */
    public static function calculate(float $julianDay): array
    {
        $values = [];

        foreach (self::MEAN_DAILY_MOTION as $planet => $meanSpeed) {
            $todayLongitude = PlanetaryElements::geocentricLongitude(strtolower($planet), $julianDay);
            $tomorrowLongitude = PlanetaryElements::geocentricLongitude(strtolower($planet), $julianDay + 1);

            $speed = fmod($tomorrowLongitude - $todayLongitude + 540, 360) - 180;

            $values[$planet] = $speed <= 0
                ? 60.0
                : round(max(0, min(60, 60 * (1 - $speed / (2 * $meanSpeed)))), 2);
        }

        return $values;
    }
}
