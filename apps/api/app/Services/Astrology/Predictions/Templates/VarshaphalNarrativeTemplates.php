<?php

namespace App\Services\Astrology\Predictions\Templates;

/**
 * Curated per-Mudda-Dasha-lord narrative text for the Varshaphal (annual
 * chart) period breakdown — same "9 lords, house placement interpolated"
 * scope as DashaNarrativeTemplates, framed for a single year's period
 * rather than a multi-year life phase. See MarriageTemplates' docblock
 * for the multi-locale shape rationale.
 */
class VarshaphalNarrativeTemplates
{
    public const TEMPLATES = [
        'en' => [
            'Sun' => "During this Sun period of the year, expect a noticeable rise in visibility, authority, and dealings with people in positions of power or with your father. In the return chart it falls in your {house}th house, which governs {signification} — the Sun's spotlight is likely to fall most directly on that area for the weeks this period lasts.",
            'Moon' => "During this Moon period of the year, expect emotional sensitivity, shifting day-to-day circumstances, and a stronger pull toward home and public perception. In the return chart it falls in your {house}th house, which governs {signification} — the Moon's changeable mood is likely to color that area most directly for the weeks this period lasts.",
            'Mars' => "During this Mars period of the year, expect a push toward action, assertiveness, and possibly friction or disputes if that energy isn't channeled well. In the return chart it falls in your {house}th house, which governs {signification} — Mars's drive (and its risk of conflict) is likely to land most directly on that area for the weeks this period lasts.",
            'Rahu' => "During this Rahu period of the year, expect sudden, unconventional developments and a restless, hard-to-satisfy ambition. In the return chart it falls in your {house}th house, which governs {signification} — Rahu's unpredictable intensity is likely to surface most directly in that area for the weeks this period lasts.",
            'Jupiter' => "During this Jupiter period of the year, expect growth, good fortune, and opportunities tied to learning, finances, or mentors — generally one of the more favorable periods of the year. In the return chart it falls in your {house}th house, which governs {signification} — Jupiter's expansion is likely to show up most visibly in that area for the weeks this period lasts.",
            'Saturn' => "During this Saturn period of the year, expect discipline, delay, and slower but more durable progress than usual. In the return chart it falls in your {house}th house, which governs {signification} — Saturn's steady, structural pressure is likely to apply most directly to that area for the weeks this period lasts.",
            'Mercury' => "During this Mercury period of the year, expect sharper thinking, busier communication, and opportunities tied to business, trade, or short journeys. In the return chart it falls in your {house}th house, which governs {signification} — Mercury's quick, adaptable energy is likely to channel most directly into that area for the weeks this period lasts.",
            'Ketu' => "During this Ketu period of the year, expect detachment, introspection, and the possibility of sudden, unexpected shifts — losses and equally sudden gains both run through its nature. In the return chart it falls in your {house}th house, which governs {signification} — Ketu's disruptive, detaching influence is likely to surface most directly in that area for the weeks this period lasts.",
            'Venus' => "During this Venus period of the year, expect comfort, pleasant social and romantic developments, and often a welcome financial upturn — broadly one of the more agreeable periods of the year. In the return chart it falls in your {house}th house, which governs {signification} — Venus's ease and prosperity are likely to show up most visibly in that area for the weeks this period lasts.",
        ],
        'hi' => [],
        'gu' => [],
    ];
}
