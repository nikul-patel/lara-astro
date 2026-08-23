<?php

namespace App\Services\Astrology\Predictions\Templates;

/**
 * Curated education prediction text. See MarriageTemplates' docblock for
 * the multi-locale shape rationale.
 */
class EducationTemplates
{
    public const TEMPLATES = [
        'en' => [
            'fifth_lord_exalted' => 'Your 5th-house lord, {lord}, is exalted in {sign}, a strong indicator of sharp intellect and notable academic achievement.',
            'fifth_lord_kendra_trikona' => 'Your 5th-house lord, {lord}, sits in the {house}th house, favoring steady, focused learning and academic follow-through.',
            'fourth_or_fifth_debilitated' => 'Your {houseLabel}-house lord, {lord}, is debilitated in {sign}, suggesting learning may come with more effort or interruption than usual — extra structure and support tend to help.',
            'fourth_or_fifth_dusthana' => 'Your {houseLabel}-house lord, {lord}, is placed in the {house}th house, which can bring interruptions to formal schooling that are usually overcome with persistence.',
            'default' => 'Your 4th and 5th-house lords ({fourthLord}, {fifthLord}) shape a learning style that rewards steady, self-directed effort over any single dramatic turning point.',
        ],
        'hi' => [],
        'gu' => [],
    ];
}
