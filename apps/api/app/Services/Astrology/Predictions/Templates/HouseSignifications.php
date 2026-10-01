<?php

namespace App\Services\Astrology\Predictions\Templates;

/**
 * The classical Parashari signification of each of the 12 houses, as a
 * short noun phrase — universally agreed across classical texts (unlike
 * Lal Kitab's Rinn table or Avkahada's excluded sub-panels), used to turn
 * a bare house NUMBER into something a reader can actually act on when
 * interpolated into a predictor's template text.
 */
class HouseSignifications
{
    public const SIGNIFICATION = [
        1 => 'self, physical body, and overall vitality',
        2 => 'wealth, family, and speech',
        3 => 'courage, siblings, and short journeys',
        4 => 'home, mother, and inner peace',
        5 => 'children, intelligence, and creative ventures',
        6 => 'obstacles, health challenges, and daily competition',
        7 => 'partnerships, marriage, and business dealings',
        8 => 'transformation, inheritance, and the unexpected',
        9 => 'fortune, higher learning, and long journeys',
        10 => 'career, public standing, and authority',
        11 => 'gains, income, and social circles',
        12 => 'expenses, foreign connections, and spiritual withdrawal',
    ];
}
