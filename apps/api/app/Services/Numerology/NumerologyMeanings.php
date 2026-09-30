<?php

namespace App\Services\Numerology;

/**
 * Short, standard keyword-style interpretations for each numerology
 * number (1-9 plus Master Numbers 11/22/33), the same "concise not
 * exhaustive" scope as Predictions/Templates in the Astrology namespace.
 */
class NumerologyMeanings
{
    private const MEANINGS = [
        1 => 'Independent, ambitious, and a natural leader — driven to originate rather than follow.',
        2 => 'Cooperative, diplomatic, and sensitive — a natural mediator who thrives in partnership.',
        3 => 'Expressive, creative, and sociable — communicates with warmth and imagination.',
        4 => 'Practical, disciplined, and grounded — builds steady, lasting foundations.',
        5 => 'Adventurous, adaptable, and freedom-loving — drawn to change and new experience.',
        6 => 'Nurturing, responsible, and harmony-seeking — a natural caretaker of home and community.',
        7 => 'Analytical, introspective, and spiritually curious — seeks truth beneath the surface.',
        8 => 'Ambitious, authoritative, and materially driven — a natural at building power and wealth.',
        9 => 'Compassionate, idealistic, and humanitarian — oriented toward service and closure.',
        11 => 'Master Number: intuitive and inspirational — a heightened, visionary version of 2\'s sensitivity.',
        22 => 'Master Number: the "master builder" — 4\'s discipline scaled to large, ambitious undertakings.',
        33 => 'Master Number: the "master teacher" — 6\'s nurturing instinct expressed as selfless guidance.',
    ];

    public static function forNumber(int $number): string
    {
        return self::MEANINGS[$number] ?? '';
    }
}
