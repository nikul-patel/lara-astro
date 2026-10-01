<?php

namespace App\Services\Astrology\Predictions\Templates;

/**
 * Curated per-Mahadasha-lord narrative text: one template per Vimshottari
 * dasha lord (9 total), describing that graha's classical general-effect
 * signification during its period, with the lord's natal house placement
 * and that house's own signification (HouseSignifications) interpolated
 * in — the "9 lords, house placement interpolated" scope this codebase
 * chose over a full 9x12=108-entry cross-product (see
 * DashaNarrativePredictor's doc comment and docs/API_CONTRACT.md).
 *
 * See MarriageTemplates' docblock for the multi-locale shape rationale.
 */
class DashaNarrativeTemplates
{
    public const TEMPLATES = [
        'en' => [
            'Sun' => "The Sun's Mahadasha tends to bring a period of growing authority, visibility, and self-assertion — connections with government, father figures, or people in positions of power often strengthen or come under focus. Sitting in your {house} house, which governs {signification}, the Sun's period is likely to color those very themes most strongly, for better where the Sun is well-placed and with more friction where it is pressured.",
            'Moon' => "The Moon's Mahadasha tends to bring emotional sensitivity, shifting circumstances, and a stronger pull toward home, mother, and the public's perception of you — fortunes during this period often rise and fall with the Moon's own waxing and waning nature. Sitting in your {house} house, which governs {signification}, the Moon's period is likely to bring its emotional ebb and flow most noticeably into that area of life.",
            'Mars' => "Mars's Mahadasha tends to bring courage, assertiveness, and a push toward action — property matters, disputes, physical exertion, and dealings with siblings commonly come to the fore, alongside a real risk of accidents or conflict if that energy isn't channeled well. Sitting in your {house} house, which governs {signification}, Mars's period is likely to bring both its drive and its friction most directly into that domain.",
            'Rahu' => "Rahu's Mahadasha tends to bring sudden, unconventional change — material ambition, foreign connections, and rapid shifts in fortune are its hallmark, often alongside a restless, hard-to-satisfy hunger and some confusion about direction. Sitting in your {house} house, which governs {signification}, Rahu's period is likely to bring its unpredictable intensity most directly into that area of life.",
            'Jupiter' => "Jupiter's Mahadasha tends to bring expansion, wisdom, and good fortune — growth in wealth, children, marriage, higher learning, and connections with teachers or mentors are its classical hallmarks, making it one of the more favorably regarded periods overall. Sitting in your {house} house, which governs {signification}, Jupiter's period is likely to bring its growth and good fortune most visibly into that domain.",
            'Saturn' => "Saturn's Mahadasha tends to bring discipline, delay, and hard-won, long-lasting results — effort during this period rarely pays off quickly, but what it builds tends to be durable, alongside real lessons around patience, responsibility, and the health of bones and joints. Sitting in your {house} house, which governs {signification}, Saturn's period is likely to apply its slow, structural pressure most directly to that area of life.",
            'Mercury' => "Mercury's Mahadasha tends to bring sharper intellect, stronger communication, and opportunities tied to business, trade, and education — short journeys and a generally adaptable, quick-thinking period are its hallmark, though nervous tension can rise alongside the mental activity. Sitting in your {house} house, which governs {signification}, Mercury's period is likely to channel its intellectual energy most directly into that domain.",
            'Ketu' => "Ketu's Mahadasha tends to bring detachment, introspection, and a pull toward the spiritual or unconventional — sudden losses and equally sudden, unexpected gains both run through its classical signification, along with some confusion about direction tied to unresolved past patterns. Sitting in your {house} house, which governs {signification}, Ketu's period is likely to bring its detaching, sometimes disruptive influence most directly into that area of life.",
            'Venus' => "Venus's Mahadasha tends to bring comfort, relationships, and prosperity to the fore — marriage, romance, artistic pursuits, and financial gain are its classical hallmarks, making it broadly one of the more pleasant periods, provided Venus isn't heavily pressured elsewhere in the chart. Sitting in your {house} house, which governs {signification}, Venus's period is likely to bring its comfort and prosperity most visibly into that domain.",
        ],
        'hi' => [],
        'gu' => [],
    ];
}
