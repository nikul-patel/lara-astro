<?php

namespace App\Services\Astrology\Predictions\Templates;

/**
 * Smaller reusable phrase sets the detailed reading composes alongside
 * the big LordInHouse/PlanetInHouse libraries: how each sign colours a
 * house, how dignity and Vedic aspects (graha drishti) modify a planet's
 * results, short period themes for dasha readings, and the element and
 * modality balance used in the chart overview.
 *
 * Slots use TemplateRenderer's {name} syntax. See MarriageTemplates'
 * docblock for the multi-locale shape rationale.
 */
class ReadingPhrases
{
    /** How a sign on a house's cusp colours that house's affairs (element + modality + ruler nature). */
    public const SIGN_QUALITY = [
        'en' => [
            'Aries' => 'Aries, a fiery, pioneering sign ruled by Mars, which gives this area a quick, direct and self-starting quality',
            'Taurus' => 'Taurus, a steady earth sign ruled by Venus, which gives this area a patient, comfort-seeking and value-conscious quality',
            'Gemini' => 'Gemini, a curious air sign ruled by Mercury, which gives this area a versatile, communicative and changeable quality',
            'Cancer' => 'Cancer, a nurturing water sign ruled by the Moon, which gives this area an emotional, protective and family-minded quality',
            'Leo' => 'Leo, a regal fire sign ruled by the Sun, which gives this area a proud, generous and visible quality',
            'Virgo' => 'Virgo, a precise earth sign ruled by Mercury, which gives this area an analytical, service-oriented and detail-conscious quality',
            'Libra' => 'Libra, a balanced air sign ruled by Venus, which gives this area a diplomatic, aesthetic and partnership-minded quality',
            'Scorpio' => 'Scorpio, an intense water sign ruled by Mars, which gives this area a deep, private and transformative quality',
            'Sagittarius' => 'Sagittarius, an expansive fire sign ruled by Jupiter, which gives this area an optimistic, principled and adventurous quality',
            'Capricorn' => 'Capricorn, a disciplined earth sign ruled by Saturn, which gives this area a practical, ambitious and slow-building quality',
            'Aquarius' => 'Aquarius, an idealistic air sign ruled by Saturn, which gives this area an independent, humanitarian and unconventional quality',
            'Pisces' => 'Pisces, a compassionate water sign ruled by Jupiter, which gives this area an intuitive, generous and spiritual quality',
        ],
    ];

    /** Dignity sentence for a planet in a sign — keyed by PlanetStrength's dignity levels. */
    public const DIGNITY = [
        'en' => [
            'exalted' => '{planet} is exalted in {sign}, the sign where it expresses its highest qualities, which strongly amplifies these results.',
            'moolatrikona' => '{planet} is in its moolatrikona sign, {sign} — a position of official authority that makes its results reliable and strong.',
            'own' => '{planet} occupies its own sign, {sign}, where it is comfortable and dependable.',
            'friendly' => '{planet} sits in {sign}, the sign of a friendly planet, which helps it deliver its results smoothly.',
            'neutral' => '{planet} is in {sign}, a neutral sign for it, so its results depend largely on the other factors in play.',
            'enemy' => '{planet} sits in {sign}, a sign ruled by an unfriendly planet, so its results may come with some friction or require extra effort.',
            'debilitated' => '{planet} is debilitated in {sign}, its weakest sign — results may come later or ask for more conscious effort, which makes the remedies in this report especially relevant.',
            'node' => 'As a shadow planet, {planet} channels the agenda of {dispositor}, the ruler of {sign}, so the condition of {dispositor} shapes how it delivers.',
            'combust' => 'It is also close to the Sun (combust), which can hide, delay or overheat its significations.',
        ],
    ];

    /** The influence a planet's graha drishti (Vedic aspect) brings to the house it aspects. */
    public const ASPECT = [
        'en' => [
            'Jupiter' => 'Jupiter’s aspect is the most protective influence in Vedic astrology — it expands, blesses and softens difficulties here.',
            'Venus' => 'Venus’s aspect adds harmony, comfort and grace.',
            'Mercury' => 'Mercury’s aspect adds intelligence, adaptability and good communication.',
            'Moon' => 'The Moon’s aspect brings sensitivity, popularity and nurturing, if fluctuating, results.',
            'Sun' => 'The Sun’s aspect brings authority and visibility, along with a touch of heat and ego.',
            'Mars' => 'Mars’s aspect energises this area but can add haste, conflict or accidents if unmanaged.',
            'Saturn' => 'Saturn’s aspect brings delay, responsibility and maturity — results come slowly but last.',
            'Rahu' => 'Rahu’s aspect adds ambition, unconventional twists and occasional confusion.',
            'Ketu' => 'Ketu’s aspect adds detachment, sudden turns and spiritual depth.',
        ],
    ];

    /** Appended when a natural malefic aspects or occupies an upachaya house (3, 6, 10, 11), where classical texts say its friction becomes productive. */
    public const UPACHAYA_MALEFIC = [
        'en' => 'In this house of growth (an upachaya house), classical texts say the friction of natural malefics turns into drive, competitive edge and results that improve with time.',
    ];

    /** What a planet's dasha period classically emphasises — a short noun phrase. */
    public const PERIOD_THEME = [
        'en' => [
            'Sun' => 'authority, recognition, self-assertion and dealings with government or father figures',
            'Moon' => 'emotions, home, the public, travel and nurturing relationships',
            'Mars' => 'energy, courage, property, competition and decisive action',
            'Mercury' => 'learning, communication, commerce, skills and networking',
            'Jupiter' => 'growth, wisdom, good fortune, children, teachers and ethical progress',
            'Venus' => 'relationships, comfort, creativity, luxury and financial gain',
            'Saturn' => 'discipline, hard work, responsibility, patience and durable achievement',
            'Rahu' => 'ambition, unconventional opportunities, foreign connections and rapid change',
            'Ketu' => 'introspection, detachment, spiritual insight and the release of old patterns',
        ],
    ];

    /** A planet's core gift, used when the overview names a standout planet. */
    public const PLANET_GIFT = [
        'en' => [
            'Sun' => 'leadership, vitality and self-confidence',
            'Moon' => 'emotional intelligence, empathy and public appeal',
            'Mars' => 'courage, drive and the ability to act decisively',
            'Mercury' => 'intellect, communication and commercial sense',
            'Jupiter' => 'wisdom, optimism and the protection of good fortune',
            'Venus' => 'charm, artistic taste and the ability to attract comfort and love',
            'Saturn' => 'discipline, endurance and the patience to build lasting results',
        ],
    ];

    /** House-distance relationship between a Mahadasha lord and its current Antardasha lord (counted from the former to the latter). */
    public const PERIOD_RELATIONSHIP = [
        'en' => [
            'conjunct' => 'The two period rulers sit together in the same house, so their themes blend closely and reinforce each other.',
            'trine' => 'The two period rulers are in a trine (5-9) relationship — among the most harmonious combinations, favouring growth and good fortune.',
            'kendra' => 'The two period rulers are in a kendra (4-10) relationship, which supports productive action and tangible progress.',
            'opposition' => 'The two period rulers face each other across the chart (1-7), so partnerships and the balancing of competing demands come to the fore.',
            'upachaya' => 'The two period rulers are in a 3-11 relationship, which classically supports effort, initiative and gains.',
            'dvirdvadasha' => 'The two period rulers are in a 2-12 relationship, which classically brings a give-and-take feel — some expenses balanced by gains.',
            'shadashtaka' => 'The two period rulers are in a 6-8 relationship, classically the most demanding combination — patience, health care and avoiding conflict are advised.',
        ],
    ];

    public const ELEMENT = [
        'en' => [
            'fire' => 'Fire dominates your chart: you are energetic, enthusiastic and action-oriented, with natural leadership and a need for purpose and challenge.',
            'earth' => 'Earth dominates your chart: you are practical, reliable and results-oriented, building security patiently and valuing what is tangible.',
            'air' => 'Air dominates your chart: you are intellectual, sociable and communicative, thriving on ideas, connection and variety.',
            'water' => 'Water dominates your chart: you are intuitive, empathetic and emotionally deep, guided strongly by feeling and relationships.',
        ],
    ];

    public const MODALITY = [
        'en' => [
            'cardinal' => 'Most of your planets sit in cardinal (movable) signs, which gives initiative and a drive to start new things.',
            'fixed' => 'Most of your planets sit in fixed signs, which gives determination, loyalty and staying power.',
            'mutable' => 'Most of your planets sit in dual (mutable) signs, which gives adaptability, versatility and a talent for learning.',
        ],
    ];
}
