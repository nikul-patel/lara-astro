<?php

namespace App\Services\Astrology\Predictions\Templates;

/**
 * Bhava Phala of each graha by house (counted from the Lagna) — 9 planets
 * x 12 houses, following the classical scheme in Phaladeepika ch. 8 ("The
 * effects of the Sun and other planets") and Saravali, which agree on the
 * broad themes: natural malefics (Sun, Mars, Saturn, Rahu, Ketu) do well
 * in the upachaya houses 3, 6, 10 and 11 and strain the houses they
 * occupy elsewhere, while natural benefics (Jupiter, Venus, Mercury, Moon)
 * support the houses they occupy except the dusthanas 6, 8 and 12.
 * Paraphrased into modern, balanced, gender-neutral language; dignity and
 * aspects are layered on separately by the predictor.
 *
 * Indexed [planet][house]. See MarriageTemplates' docblock for the
 * multi-locale shape rationale.
 */
class PlanetInHouseTemplates
{
    public const TEMPLATES = [
        'en' => [
            'Sun' => [
                1 => 'The Sun here gives a commanding presence, leadership instincts and strong vitality; pride and impatience are the shadow side, so channelling that fire into purpose serves you best.',
                2 => 'The Sun here links wealth with authority and government, and lends your speech weight — keep a gentle tone in family matters, where it can come across as stern.',
                3 => 'The Sun here is classically strong: it gives courage, initiative and success through self-effort, with a talent for leading teams and taking bold decisions.',
                4 => 'The Sun here can make domestic life demanding — home may feel like another place to lead — yet it supports property through government or authority; nurturing emotional ease at home is key.',
                5 => 'The Sun here sharpens intelligence and ambition, favours leadership in creative or political fields, and asks for patience and warmth in matters of children and speculation.',
                6 => 'The Sun here is excellent for overcoming rivals and illness: it brings success in service, administration, medicine or government and a strong competitive edge.',
                7 => 'The Sun here draws a strong-willed partner and authority-linked business; mutual respect and room for both egos keep partnerships harmonious.',
                8 => 'The Sun here turns attention to research, the hidden and inherited matters; regular care of eyes, heart and vitality is advised.',
                9 => 'The Sun here gives principled convictions, a dignified father figure and fortune through government or leadership, along with a strong sense of duty.',
                10 => 'The Sun here is among its best placements (it gains directional strength): authority, recognition, government favour and a commanding career are indicated.',
                11 => 'The Sun here brings gains through authority, government and influential friends, and fulfils ambitions through steady leadership.',
                12 => 'The Sun here favours foreign connections, spiritual leadership and work in institutions; expenses and eyesight deserve attention.',
            ],
            'Moon' => [
                1 => 'The Moon here makes you sensitive, receptive and appealing to others, with a changeable mood and a strong connection to the public.',
                2 => 'The Moon here gives a pleasant voice, emotional attachment to family and wealth that grows through public-facing or nurturing work.',
                3 => 'The Moon here makes the mind active and curious, supports good relations with siblings and brings frequent short journeys.',
                4 => 'The Moon here is at its strongest by direction: emotional security, a loving mother, comfortable homes and vehicles are indicated.',
                5 => 'The Moon here gives an imaginative, emotionally intelligent mind, creative talent and a nurturing bond with children.',
                6 => 'The Moon here can bring emotional stress from work or rivals; digestion and routine benefit from care, and service-oriented work suits you.',
                7 => 'The Moon here draws an attractive, caring partner and success with the public, though emotional fluctuations in partnership need honest communication.',
                8 => 'The Moon here deepens intuition and interest in the mystical; emotional ups and downs are possible, and calm routines are protective.',
                9 => 'The Moon here makes you devotional, fortunate and fond of travel, with blessings from mother and teachers.',
                10 => 'The Moon here brings public recognition, a career that deals with people, and popularity that rises and falls with the tides of life.',
                11 => 'The Moon here favours gains through the public, a wide circle of friends and the fulfilment of wishes.',
                12 => 'The Moon here makes you sensitive and spiritually inclined; restful sleep, solitude and foreign connections are themes, along with careful spending.',
            ],
            'Mars' => [
                1 => 'Mars here gives energy, courage and a competitive spirit; impulsiveness and minor injuries are the shadow side (this is one of the classical Manglik positions).',
                2 => 'Mars here makes speech direct and forceful and links wealth to property, engineering or technical work — tact in family discussions avoids friction.',
                3 => 'Mars here is excellent: courage, stamina and initiative are strong, making you bold in ventures, sports and competition.',
                4 => 'Mars here supports property gained through effort, but can bring tension at home — patience with family keeps your foundation calm (a classical Manglik position).',
                5 => 'Mars here gives a sharp, quick and strategic mind; impulsive speculation should be avoided and children may need extra patience.',
                6 => 'Mars here is excellent for defeating rivals and illness: strong immunity, competitive success and suitability for sport, surgery, defence or law.',
                7 => 'Mars here brings passion and drive into partnership but can create friction (a classical Manglik position) — shared goals and a dynamic partner help.',
                8 => 'Mars here gives courage in crisis and talent for research or surgery; caution with accidents, risky activities and sudden decisions is advised (a classical Manglik position).',
                9 => 'Mars here gives strong convictions and an adventurous spirit; differences of opinion with father figures or teachers are possible.',
                10 => 'Mars here gains directional strength: a powerful drive for achievement suits engineering, defence, sports, management or entrepreneurship.',
                11 => 'Mars here brings gains through effort, property, technical skill and an energetic network, and helps you achieve goals quickly.',
                12 => 'Mars here directs energy toward foreign ventures or behind-the-scenes work; expenses, restless sleep and suppressed anger need conscious care (a classical Manglik position).',
            ],
            'Mercury' => [
                1 => 'Mercury here makes you witty, youthful, articulate and quick to learn, with a talent for communication and analysis.',
                2 => 'Mercury here gives eloquent speech and wealth through business, trade, writing, accounting or teaching.',
                3 => 'Mercury here favours writing, media, communication and commerce, along with clever siblings and frequent travel.',
                4 => 'Mercury here supports a good education, an intellectually lively home and comfort through learning.',
                5 => 'Mercury here gives sharp intelligence, a creative intellect and aptitude for advisory, teaching or analytical work.',
                6 => 'Mercury here makes you an excellent problem-solver in service, health, law or analytics; nervous tension benefits from good routines.',
                7 => 'Mercury here draws an intelligent, youthful and communicative partner and favours business partnerships and trade.',
                8 => 'Mercury here gives a research-oriented, analytical mind with interest in hidden subjects, and classically supports longevity.',
                9 => 'Mercury here makes you learned and philosophical, with good fortune through education, publishing and teaching.',
                10 => 'Mercury here favours a career in business, communication, technology, finance or consulting, with recognition for your intellect.',
                11 => 'Mercury here brings gains through intellect, commerce and networks, and multiple income streams.',
                12 => 'Mercury here makes you introspective, drawn to foreign languages or study, and asks for care with expenses and overthinking.',
            ],
            'Jupiter' => [
                1 => 'Jupiter here is a great blessing: wisdom, optimism, good health, respect and the protection of mentors are classically promised.',
                2 => 'Jupiter here gives eloquence, family prosperity and steady accumulation of wealth, along with sound values.',
                3 => 'Jupiter here makes you courteous and wise in communication, though courage can lean toward caution; siblings tend to be supportive.',
                4 => 'Jupiter here brings happiness, a good home, property, vehicles and a strong educational foundation.',
                5 => 'Jupiter here is excellent for wisdom, children, creativity and good judgment — a strong mark of past-life merit.',
                6 => 'Jupiter here helps you overcome rivals through ethics and patience, though weight, liver and lifestyle health deserve attention.',
                7 => 'Jupiter here draws a wise, principled and supportive partner and favours ethical business partnerships.',
                8 => 'Jupiter here classically gives long life, interest in deep philosophy or occult wisdom, and protection in crises.',
                9 => 'Jupiter here is among its finest placements: fortune, faith, teachers, higher learning and a blessed father figure.',
                10 => 'Jupiter here brings a respected career rooted in ethics, teaching, law, finance or advisory work.',
                11 => 'Jupiter here brings abundant gains, generous friends and the fulfilment of aspirations.',
                12 => 'Jupiter here favours spirituality, charity and foreign connections, with expenses directed toward meaningful causes.',
            ],
            'Venus' => [
                1 => 'Venus here makes you charming, artistic and comfort-loving, with natural grace and a talent for harmony.',
                2 => 'Venus here brings wealth, sweet speech, a harmonious family and an eye for beauty and fine things.',
                3 => 'Venus here favours artistic communication, music and design and affectionate siblings, though motivation can need a push.',
                4 => 'Venus here brings beautiful homes, vehicles, comforts and a warm domestic atmosphere.',
                5 => 'Venus here gives creative talent, romance and joy through children and the arts.',
                6 => 'Venus here can complicate relationships and calls for care with diet and sugar balance, though it favours service in creative or health fields.',
                7 => 'Venus here brings an attractive, affectionate partner and pleasure in partnership; balance in indulgence keeps things healthy.',
                8 => 'Venus here favours inheritance, a partner’s wealth and long life, with a deep and private emotional nature.',
                9 => 'Venus here brings fortune, devotion, artistic refinement and pleasant long journeys.',
                10 => 'Venus here favours a career in the arts, fashion, beauty, hospitality, luxury or diplomacy, and a pleasant public image.',
                11 => 'Venus here brings gains, luxuries, pleasant friendships and success through charm and networking.',
                12 => 'Venus here is classically well placed: comforts, restful sleep, foreign pleasures and spending on beautiful things.',
            ],
            'Saturn' => [
                1 => 'Saturn here gives seriousness, discipline and endurance; early life may feel slow or effortful, but maturity brings lasting authority — care for bones and joints is wise.',
                2 => 'Saturn here makes wealth slow but durable and speech measured; frugality and patience build long-term security.',
                3 => 'Saturn here is classically strong: perseverance, steady courage and success through sustained effort.',
                4 => 'Saturn here brings domestic responsibilities and may delay home comforts, but property gained later in life is durable.',
                5 => 'Saturn here gives a serious, structured intellect and may delay or add responsibility around children; patience is rewarded.',
                6 => 'Saturn here is excellent for endurance and defeating rivals, with success in service, law, administration or labour-intensive fields.',
                7 => 'Saturn here favours a mature, loyal and dependable partner, often with marriage arriving after some delay.',
                8 => 'Saturn here classically gives long life and endurance through hardships; chronic health needs consistent care.',
                9 => 'Saturn here gives independent, sometimes unorthodox convictions and fortune that ripens slowly but steadily.',
                10 => 'Saturn here builds a career through hard work, persistence and responsibility, with authority that grows with age.',
                11 => 'Saturn here is excellent for steady, long-term gains and loyal friendships built over time.',
                12 => 'Saturn here brings expenses, a need for solitude and a disciplined spiritual life; foreign residence is possible.',
            ],
            'Rahu' => [
                1 => 'Rahu here gives an unconventional, magnetic personality and big ambitions; restlessness and identity shifts are part of the journey.',
                2 => 'Rahu here can make wealth fluctuate and speech unusual or persuasive, and may create some distance from family traditions.',
                3 => 'Rahu here is classically strong: boldness, media or technology skills and success through daring initiative.',
                4 => 'Rahu here may make home life restless or bring property abroad; cultivating inner peace is an ongoing theme.',
                5 => 'Rahu here gives an unconventional, inventive mind; speculation should be approached with care.',
                6 => 'Rahu here is excellent for defeating rivals and overcoming obstacles through strategy.',
                7 => 'Rahu here draws an unconventional or foreign partner and unusual partnerships.',
                8 => 'Rahu here brings sudden events and a fascination with the occult, research or hidden resources.',
                9 => 'Rahu here gives unorthodox beliefs, foreign travel and fortune through unconventional paths.',
                10 => 'Rahu here fuels strong ambition and rise in technology, media, politics or foreign-linked careers.',
                11 => 'Rahu here is excellent for large gains, influential networks and the fulfilment of ambitious goals.',
                12 => 'Rahu here draws you toward foreign lands, spending and spiritual seeking; restful sleep needs protecting.',
            ],
            'Ketu' => [
                1 => 'Ketu here gives an introspective, intuitive and spiritually inclined nature, sometimes with uncertainty about personal direction.',
                2 => 'Ketu here brings detachment from material wealth and family conventions; mindful speech is helpful.',
                3 => 'Ketu here is classically strong: courage, spiritual effort and success in focused, solitary work.',
                4 => 'Ketu here can bring detachment from home or changes of residence and calls for nurturing inner peace.',
                5 => 'Ketu here gives intuitive intelligence and interest in spiritual or esoteric knowledge.',
                6 => 'Ketu here is excellent for defeating rivals and recovering from illness.',
                7 => 'Ketu here can bring detachment or spiritual depth to partnerships; conscious engagement keeps bonds warm.',
                8 => 'Ketu here gives deep intuition, research ability and interest in the occult.',
                9 => 'Ketu here gives an unconventional spiritual path and independent beliefs.',
                10 => 'Ketu here can bring career changes or detours toward meaningful, technical or spiritual work.',
                11 => 'Ketu here is classically good for gains, though attachment to outcomes lessens over time.',
                12 => 'Ketu here is classically the placement of liberation: spiritual insight, meditation and detachment from worldly loss.',
            ],
        ],
        'hi' => [],
        'gu' => [],
    ];
}
