<?php

namespace App\Services\Astrology\Predictions\Templates;

/**
 * Curated career/job/business prediction text. See MarriageTemplates'
 * docblock for the multi-locale shape rationale.
 */
class CareerTemplates
{
    public const TEMPLATES = [
        'en' => [
            'tenth_lord_yoga' => 'Your 10th-house lord, {lord}, is part of a {yogaName} — a strong combination for recognition, authority, and rising status in your career or business.',
            'tenth_lord_exalted' => 'Your 10th-house lord, {lord}, is exalted in {sign}, pointing toward strong professional achievement and a career that commands respect.',
            'tenth_lord_debilitated' => 'Your 10th-house lord, {lord}, is debilitated in {sign}, suggesting a career path that demands extra persistence early on before it finds its footing.',
            'tenth_lord_kendra_trikona' => 'Your 10th-house lord, {lord}, sits in the {house} house, a favorable placement for steady professional growth and a well-regarded reputation.',
            'tenth_lord_dusthana' => 'Your 10th-house lord, {lord}, is placed in the {house} house, indicating career gains that may come through service, competition, or unconventional means rather than a straightforward path.',
            'tenth_lord_default' => 'Your 10th-house lord, {lord}, is placed in {sign} in the {house} house, shaping the direction your career or business is likely to take.',
        ],
        'hi' => [],
        'gu' => [],
    ];
}
