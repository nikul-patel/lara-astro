<?php

namespace App\Services\Astrology\Predictions\Templates;

/**
 * Per-house life-area framing for the detailed reading: a reader-facing
 * title, what the house governs (fuller than HouseSignifications' short
 * noun phrase, which other predictors interpolate mid-sentence), its
 * Parashari natural significator (karaka — BPHS's one-planet-per-house
 * list: Sun, Jupiter, Mars, Moon, Jupiter, Mars, Venus, Saturn, Jupiter,
 * Mercury, Jupiter, Saturn), and practical guidance for each of the three
 * strength bands LifeAreaAnalyzer scores a house into.
 *
 * See MarriageTemplates' docblock for the multi-locale shape rationale.
 */
class LifeAreaTemplates
{
    public const AREAS = [
        'en' => [
            1 => [
                'title' => 'Self, Personality & Health',
                'intro' => 'The 1st house (Lagna) is the foundation of the whole chart: your body, temperament, vitality, self-image and the way you meet the world. Its strength colours every other area of life.',
                'karaka' => 'Sun',
                'karaka_topic' => 'vitality and self-confidence',
                'guidance' => [
                    'strong' => 'Lead from your strengths: your constitution and confidence are real assets, so invest them in long-term goals and in people who rely on you.',
                    'moderate' => 'Steady routines — sleep, movement, sunlight and a clear daily purpose — amplify the good in this house and smooth its uneven phases.',
                    'care' => 'Treat your health and self-belief as projects worth daily attention; strengthening the ruler of this house through discipline and the remedies in this report pays off across every area of life.',
                ],
            ],
            2 => [
                'title' => 'Wealth, Family & Speech',
                'intro' => 'The 2nd house governs accumulated wealth, savings, family of origin, speech, food habits and the values you hold dear.',
                'karaka' => 'Jupiter',
                'karaka_topic' => 'wealth and wisdom',
                'guidance' => [
                    'strong' => 'Your capacity to build wealth is well supported — think long-term, diversify sensibly and let your words open doors.',
                    'moderate' => 'Consistent saving and mindful speech are the keys here; small, regular deposits outperform sudden bursts.',
                    'care' => 'Prioritise an emergency fund, avoid lending to family without clear terms, and speak gently in tense conversations — the 2nd house rewards restraint.',
                ],
            ],
            3 => [
                'title' => 'Courage, Siblings & Communication',
                'intro' => 'The 3rd house governs courage, initiative, self-effort, siblings, neighbours, short journeys, writing, media and skills of the hands.',
                'karaka' => 'Mars',
                'karaka_topic' => 'courage and drive',
                'guidance' => [
                    'strong' => 'Back yourself: initiatives, writing, sales and bold moves are favoured, and siblings or peers are valuable allies.',
                    'moderate' => 'Build courage through small, consistent actions and keep communication with siblings open and practical.',
                    'care' => 'Plan before you leap, protect yourself on the road, and invest in skill-building — confidence grows from competence here.',
                ],
            ],
            4 => [
                'title' => 'Home, Mother, Property & Inner Peace',
                'intro' => 'The 4th house governs the home, mother, emotional security, property, land, vehicles, early education and the contentment of the heart.',
                'karaka' => 'Moon',
                'karaka_topic' => 'emotional well-being and the mother',
                'guidance' => [
                    'strong' => 'A secure home base is one of your great assets — property and vehicles are favoured, and time with family recharges you.',
                    'moderate' => 'Create a calm, uncluttered home, check property documents carefully and make regular time for your mother or family elders.',
                    'care' => 'Nurture inner peace deliberately through meditation, nature and boundaries between work and home, and take extra care over property decisions.',
                ],
            ],
            5 => [
                'title' => 'Children, Intelligence & Creativity',
                'intro' => 'The 5th house governs intelligence, learning, creativity, romance, children, speculation, mantra and the merit carried over from past lives (purva punya).',
                'karaka' => 'Jupiter',
                'karaka_topic' => 'children and wisdom',
                'guidance' => [
                    'strong' => 'Your creative and intellectual gifts deserve an outlet — teaching, writing, art or strategic investing can flourish.',
                    'moderate' => 'Regular study, creative practice and patient attention to children bring out the best of this house.',
                    'care' => 'Avoid speculative risk, give children and creative projects extra patience, and support your mind with learning and devotional practice.',
                ],
            ],
            6 => [
                'title' => 'Health, Service, Debts & Competition',
                'intro' => 'The 6th house governs daily health, immunity, service, employment, routine work, debts, disputes, competitors and the obstacles you learn to overcome.',
                'karaka' => 'Mars',
                'karaka_topic' => 'fighting spirit and immunity',
                'guidance' => [
                    'strong' => 'You outlast competition and recover well — service, healthcare, law and analytical roles can bring you distinction.',
                    'moderate' => 'Healthy routines, prompt resolution of disputes and careful handling of loans keep this house working for you.',
                    'care' => 'Make preventive health care non-negotiable, avoid unnecessary debts and conflicts, and resolve disputes early and on paper.',
                ],
            ],
            7 => [
                'title' => 'Marriage, Partnerships & Public Dealings',
                'intro' => 'The 7th house governs marriage, the spouse, committed partnerships, business associates, contracts, clients and how you deal with the public.',
                'karaka' => 'Venus',
                'karaka_topic' => 'love, harmony and marriage',
                'guidance' => [
                    'strong' => 'Partnerships are a source of strength and growth — invest in them and you will find collaboration multiplies your results.',
                    'moderate' => 'Clear communication, shared goals and patience during adjustment phases keep partnerships thriving.',
                    'care' => 'Take time before committing, put business agreements in writing, and treat compatibility and mutual respect as the foundation of any partnership.',
                ],
            ],
            8 => [
                'title' => 'Longevity, Transformation & Hidden Matters',
                'intro' => 'The 8th house governs longevity, sudden events, transformation, inheritance, insurance, joint finances, research, the occult and deep psychological change.',
                'karaka' => 'Saturn',
                'karaka_topic' => 'longevity and endurance',
                'guidance' => [
                    'strong' => 'Your resilience is exceptional — research, healing and crisis management suit you, and change tends to leave you stronger.',
                    'moderate' => 'Keep insurance, savings and health check-ups in order so that life’s surprises become opportunities rather than setbacks.',
                    'care' => 'Prioritise preventive health, avoid unnecessary risks, keep finances transparent, and use spiritual or contemplative practice to navigate change.',
                ],
            ],
            9 => [
                'title' => 'Fortune, Father, Teachers & Higher Learning',
                'intro' => 'The 9th house governs fortune, faith, principles, the father, teachers and gurus, higher education, long-distance travel and pilgrimage.',
                'karaka' => 'Jupiter',
                'karaka_topic' => 'fortune and guidance',
                'guidance' => [
                    'strong' => 'Luck tends to meet you halfway — follow your principles, seek mentors and pursue higher learning or travel.',
                    'moderate' => 'Respect for teachers and elders, charitable acts and steady pursuit of learning strengthen your fortune over time.',
                    'care' => 'Build fortune through character and persistence; honour your father and teachers, and practise generosity, which classical texts consistently say strengthens this house.',
                ],
            ],
            10 => [
                'title' => 'Career, Status & Reputation',
                'intro' => 'The 10th house governs career, profession, status, authority, reputation, government and the actions (karma) by which you are known in the world.',
                'karaka' => 'Mercury',
                'karaka_topic' => 'skill and professional capability',
                'guidance' => [
                    'strong' => 'Aim high: leadership, entrepreneurship and public roles are well supported, and your reputation is an asset to protect.',
                    'moderate' => 'Steady skill-building, reliable delivery and good relationships with superiors build a career that rises consistently.',
                    'care' => 'Choose work that fits your strengths, document your achievements, avoid shortcuts, and build your reputation step by step — persistence is rewarded.',
                ],
            ],
            11 => [
                'title' => 'Gains, Income, Friends & Aspirations',
                'intro' => 'The 11th house governs gains, income, profits, elder siblings, friends, networks and the fulfilment of desires and ambitions.',
                'karaka' => 'Jupiter',
                'karaka_topic' => 'gains and abundance',
                'guidance' => [
                    'strong' => 'Your network and ambitions are well supported — set big goals and let collaborators help you reach them.',
                    'moderate' => 'Nurture friendships, diversify income and keep clear goals so gains accumulate steadily.',
                    'care' => 'Choose friends and partners carefully, avoid get-rich-quick schemes and focus on one or two dependable income streams.',
                ],
            ],
            12 => [
                'title' => 'Expenses, Foreign Lands & Spirituality',
                'intro' => 'The 12th house governs expenses, losses, foreign lands, isolation, hospitals, sleep, charity, meditation and spiritual liberation (moksha).',
                'karaka' => 'Saturn',
                'karaka_topic' => 'detachment and endurance',
                'guidance' => [
                    'strong' => 'Foreign opportunities, spiritual practice and generous giving are favoured — spending tends to go toward meaningful ends.',
                    'moderate' => 'Budget consciously, protect your sleep and give regularly; retreat and reflection renew you.',
                    'care' => 'Track expenses closely, protect your rest, avoid isolation and channel this house’s energy into meditation, service or charity.',
                ],
            ],
        ],
        'hi' => [],
        'gu' => [],
    ];
}
