<?php

namespace App\Services\Astrology\Predictions\Templates;

/**
 * Curated marriage/partnership prediction text, keyed by locale then by
 * template key. Only 'en' is authored for now — the array is shaped for
 * all three site locales from day one (see the open risk about
 * multi-locale prediction text in the implementation plan) so adding
 * 'hi'/'gu' content later doesn't require touching the predictor logic.
 */
class MarriageTemplates
{
    public const TEMPLATES = [
        'en' => [
            'seventh_lord_exalted' => 'Your 7th-house lord, {lord}, is exalted in {sign} — a strong classical indicator of a fulfilling, harmonious partnership and a spouse of good standing.',
            'seventh_lord_debilitated' => 'Your 7th-house lord, {lord}, is debilitated in {sign}, suggesting delays or early friction in matters of marriage that tend to ease with maturity, patience, and effort.',
            'seventh_lord_kendra_trikona' => 'Your 7th-house lord, {lord}, sits in the {house} house — a supportive placement associated with a stable, cooperative partnership.',
            'seventh_lord_dusthana' => 'Your 7th-house lord, {lord}, is placed in the {house} house, indicating some obstacles, distance, or an unconventional path to marriage before things settle.',
            'seventh_lord_default' => 'Your 7th-house lord, {lord}, is placed in {sign} in the {house} house, shaping the nature and timing of significant partnerships in your life.',
        ],
        'hi' => [],
        'gu' => [],
    ];
}
