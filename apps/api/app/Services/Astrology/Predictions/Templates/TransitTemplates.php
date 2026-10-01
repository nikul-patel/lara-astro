<?php

namespace App\Services\Astrology\Predictions\Templates;

/**
 * Curated Gochar (transit) narrative text: one template per graha (9
 * total), describing that planet's classical general transit effect, with
 * its current house-from-natal-Moon and that house's signification
 * (HouseSignifications) interpolated in — the same "N lords, house
 * interpolated" scope choice as DashaNarrativeTemplates (#79) and
 * VarshaphalNarrativeTemplates, applied here to avoid a 9x12=108-entry
 * cross-product of bespoke transit paragraphs. See MarriageTemplates'
 * docblock for the multi-locale shape rationale.
 */
class TransitTemplates
{
    public const TEMPLATES = [
        'en' => [
            'Sun' => 'The Sun transits each sign in about a month, bringing a short-lived spotlight on whatever house it crosses — a period of heightened visibility, energy, and focus on authority figures. Currently in your {house}th house from the Moon, which governs {signification}, the Sun is likely to bring extra attention and activity to that area of life for the next few weeks.',
            'Moon' => "The Moon transits each sign in about two and a quarter days, the fastest-moving classical Gochar and the one most felt day-to-day in mood and immediate circumstances. Currently in your {house}th house from the Moon, which governs {signification}, today's Lunar transit is likely to color your mood and daily focus around that area most directly.",
            'Mars' => "Mars transits each sign in roughly a month and a half, bringing bursts of energy, drive, and sometimes friction to wherever it crosses. Currently in your {house}th house from the Moon, which governs {signification}, Mars's transit is likely to bring both initiative and some impatience to that area of life over the coming weeks.",
            'Mercury' => "Mercury transits each sign in two to three weeks (longer when retrograde), bringing a period of sharper focus on communication, decisions, and short-term planning to wherever it crosses. Currently in your {house}th house from the Moon, which governs {signification}, Mercury's transit is likely to bring extra mental activity and conversations around that area of life.",
            'Jupiter' => "Jupiter transits each sign over roughly a year, one of the most closely watched classical Gochars since its house-from-Moon placement is traditionally read as favorable or challenging by specific, named positions (2nd, 5th, 7th, 9th, and 11th from the Moon are classically considered auspicious; others call for more caution). Currently in your {house}th house from the Moon, which governs {signification}, Jupiter's transit is likely to bring its characteristic growth and opportunity most directly into that area of life this year.",
            'Venus' => "Venus transits each sign in roughly a month (longer when retrograde), bringing a period of heightened focus on comfort, relationships, and finances to wherever it crosses. Currently in your {house}th house from the Moon, which governs {signification}, Venus's transit is likely to bring extra ease and enjoyment — or, if already under pressure elsewhere, extra spending or relationship attention — to that area of life.",
            'Saturn' => "Saturn transits each sign over roughly two and a half years, the slowest and most closely watched classical Gochar — its passage through the 12th, 1st, and 2nd houses from the Moon is the basis of Sade Sati, and its passage through the 4th or 8th is Panoti (Dhaiya), both periods asking for patience and discipline rather than quick results. Currently in your {house}th house from the Moon, which governs {signification}, Saturn's transit is likely to apply its slow, structural pressure most directly to that area of life for an extended stretch.",
            'Rahu' => "Rahu transits each sign over roughly a year and a half, moving in retrograde motion through the zodiac and bringing sudden, unconventional developments and an often restless ambition to wherever it crosses. Currently in your {house}th house from the Moon, which governs {signification}, Rahu's transit is likely to bring unpredictable shifts and heightened ambition into that area of life for an extended period.",
            'Ketu' => "Ketu transits opposite Rahu, always exactly six signs away, bringing detachment, introspection, and sometimes sudden endings or unexpected gains to wherever it crosses. Currently in your {house}th house from the Moon, which governs {signification}, Ketu's transit is likely to bring a pull toward release or reassessment in that area of life for an extended period.",
        ],
        'hi' => [],
        'gu' => [],
    ];
}
