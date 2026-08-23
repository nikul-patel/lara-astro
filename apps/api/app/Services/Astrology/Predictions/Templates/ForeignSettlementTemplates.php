<?php

namespace App\Services\Astrology\Predictions\Templates;

/**
 * Curated foreign-settlement / job-or-business-abroad prediction text.
 * See MarriageTemplates' docblock for the multi-locale shape rationale.
 */
class ForeignSettlementTemplates
{
    public const TEMPLATES = [
        'en' => [
            'rahu_12th' => 'Rahu in your 12th house is a classical signal of a strong pull toward foreign lands — you may find unconventional, technology-driven, or import/export-style work abroad especially rewarding.',
            'moon_12th' => 'The Moon in your 12th house draws you toward living or working overseas, often in people-facing fields such as hospitality, travel, healthcare, or public-facing roles.',
            'ninth_twelfth_link' => 'Your 9th-house lord ({lord9}) and 12th-house lord ({lord12}) are strongly linked, favoring fortune built specifically through overseas business partnerships, consulting, or trade connections.',
            'twelfth_lord_strong' => 'Your 12th-house lord, {lord}, is well placed in the {house}th house, supporting a genuinely successful stint abroad — for study, a long-term posting, or business — rather than brief travel.',
            'moderate' => 'Your 12th-house lord, {lord}, in {sign} suggests occasional travel or short-term opportunities abroad, though the stronger combinations for permanent settlement overseas aren\'t prominent in this chart.',
            'default' => 'Your chart doesn\'t show a strong classical pull toward foreign settlement; your career and business opportunities are more likely to be strongest closer to home, with travel abroad remaining occasional rather than a defining theme.',
        ],
        'hi' => [],
        'gu' => [],
    ];
}
