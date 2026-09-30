<?php

namespace App\Services\Astrology\Doshas;

/**
 * Orchestrates every standalone dosha check against an already-computed
 * chart (BirthChartCalculator::calculate()'s output), the same
 * "orchestrator + independent detector classes" shape as YogaEngine. Sade
 * Sati is deliberately not included here — unlike Mangal/Kaal Sarp (fixed
 * facts about the natal chart), it depends on the current date, so it
 * lives in its own Transits\SadeSati class with its own endpoint.
 */
class DoshaEngine
{
    /**
     * @param  array<string, mixed>  $chart  A vedic-system BirthChartCalculator::calculate() result.
     * @return array{manglik: array<string, mixed>, kaal_sarp: array<string, mixed>}
     */
    public static function detect(array $chart): array
    {
        $chartLongitudes = [];
        foreach ($chart['planetary_positions'] as $planet) {
            $chartLongitudes[$planet['name']] = $planet['longitude'];
        }

        return [
            'manglik' => MangalDosha::detect($chart['houses']),
            'kaal_sarp' => KaalSarpDosha::detect($chartLongitudes, $chart['houses']),
        ];
    }
}
