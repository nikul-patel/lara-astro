<?php

namespace App\Services\Astrology;

use App\Models\Setting;

/**
 * Region-aware system/chart-style recommendation (PRD §8 point 1), derived
 * from the birth place's geocoded state (per docs/API_CONTRACT.md: "the API
 * returns its region-based recommendation (derived from `place` geocoding)"
 * — distinct from the frontend's separate visitor-IP-based default shown
 * before a chart is calculated).
 *
 * Respects the per-deployment Settings overrides (PRD §8 point 4): a
 * deployment can force a specific chart style, or disable the Western
 * override entirely.
 */
class RegionRecommendation
{
    /**
     * North Indian states also recommend "north_indian" style, but that's
     * already this method's default — for every state not listed below,
     * including those outside India, matching PRD §8 point 1's "visitor
     * outside India → recommend Vedic, North Indian style by default".
     *
     * @var list<string>
     */
    private const SOUTH_INDIAN_STATES = ['Tamil Nadu', 'Karnataka', 'Andhra Pradesh', 'Telangana', 'Kerala'];

    private const EAST_INDIAN_STATES = ['West Bengal', 'Odisha', 'Assam'];

    /**
     * @return array{system: string, chart_style: string}
     */
    public static function forLocation(?string $state): array
    {
        $chartStyle = match (true) {
            in_array($state, self::SOUTH_INDIAN_STATES, true) => 'south_indian',
            in_array($state, self::EAST_INDIAN_STATES, true) => 'east_indian',
            default => 'north_indian',
        };

        $setting = Setting::current();

        if ($setting->astrology_forced_chart_style) {
            $chartStyle = $setting->astrology_forced_chart_style;
        }

        return ['system' => 'vedic', 'chart_style' => $chartStyle];
    }
}
