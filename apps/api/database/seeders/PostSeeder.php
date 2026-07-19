<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;

/**
 * Seeds fictional SEO blog posts for the "Jyotish Path" demo (PRD §5.1, §4
 * "SEO content"). Post title/excerpt/content/meta_* are translatable
 * (en/hi/gu). Idempotent via updateOrCreate keyed on slug.
 */
class PostSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->posts() as $post) {
            Post::updateOrCreate(
                ['slug' => $post['slug']],
                [
                    'title' => $post['title'],
                    'excerpt' => $post['excerpt'],
                    'content' => $post['content'],
                    'meta_title' => $post['meta_title'],
                    'meta_description' => $post['meta_description'],
                    'published_at' => $post['published_at'],
                ],
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function posts(): array
    {
        return [
            [
                'slug' => 'understanding-your-lagna',
                'published_at' => '2026-07-12T06:30:00Z',
                'title' => [
                    'en' => 'Understanding Your Lagna: The Starting Point of a Birth Chart',
                    'hi' => 'अपने लग्न को समझें: जन्म कुंडली का आरंभिक बिंदु',
                    'gu' => 'તમારું લગ્ન સમજો: જન્મકુંડળીનો પ્રારંભિક બિંદુ',
                ],
                'excerpt' => [
                    'en' => 'Learn why the ascendant shapes the houses, chart ruler and the way your life story unfolds.',
                    'hi' => 'जानें कि लग्न भावों, लग्नेश और जीवन की दिशा को कैसे प्रभावित करता है।',
                    'gu' => 'જાણો કે લગ્ન ભાવો, લગ્નેશ અને જીવનની દિશાને કેવી રીતે અસર કરે છે.',
                ],
                'content' => [
                    'en' => "Your lagna, or ascendant, is the zodiac sign rising on the eastern horizon at your birth. It sets the first house and gives every other house its place in the chart.\n\nAstrologers study the lagna, its ruling planet and influences on the first house together. This combination describes how you meet life, use your energy and respond to new circumstances.\n\nThe lagna is a starting point, not a complete verdict. A useful reading always considers it alongside the Moon, Sun, planetary periods and the questions you bring.",
                    'hi' => "लग्न वह राशि है जो आपके जन्म के समय पूर्वी क्षितिज पर उदित हो रही थी। यही प्रथम भाव तय करता है और पूरी कुंडली के भावों को क्रम देता है।\n\nज्योतिषी लग्न, उसके स्वामी और प्रथम भाव पर पड़ने वाले प्रभावों को साथ में देखते हैं। इससे जीवन के प्रति आपका दृष्टिकोण और नई परिस्थितियों में आपकी प्रतिक्रिया समझी जाती है।\n\nलग्न शुरुआत है, अंतिम निष्कर्ष नहीं। उपयोगी विश्लेषण में चंद्रमा, सूर्य, दशाएँ और आपके प्रश्न भी शामिल होते हैं।",
                    'gu' => "લગ્ન એ રાશિ છે જે તમારા જન્મ સમયે પૂર્વ ક્ષિતિજ પર ઉગતી હતી. તે પ્રથમ ભાવ નક્કી કરે છે અને કુંડળીના બાકીના ભાવોને સ્થાન આપે છે.\n\nજ્યોતિષી લગ્ન, તેના સ્વામી અને પ્રથમ ભાવ પરના પ્રભાવોને સાથે જુએ છે. આ સંયોજન જીવન પ્રત્યેનો તમારો અભિગમ અને નવી પરિસ્થિતિમાં પ્રતિભાવ સમજવામાં મદદ કરે છે.\n\nલગ્ન શરૂઆત છે, અંતિમ ચુકાદો નહીં. ઉપયોગી વાંચનમાં ચંદ્ર, સૂર્ય, દશા અને તમારા પ્રશ્નો પણ સામેલ થાય છે.",
                ],
                'meta_title' => [
                    'en' => 'What Is Lagna in Vedic Astrology?',
                    'hi' => 'वैदिक ज्योतिष में लग्न क्या है?',
                    'gu' => 'વૈદિક જ્યોતિષમાં લગ્ન શું છે?',
                ],
                'meta_description' => [
                    'en' => 'Understand lagna, the ascendant and its role as the starting point of a Vedic birth chart.',
                    'hi' => 'जन्म कुंडली के आरंभिक बिंदु लग्न और लग्नेश की भूमिका समझें।',
                    'gu' => 'જન્મકુંડળીના પ્રારંભિક બિંદુ લગ્ન અને લગ્નેશની ભૂમિકા સમજો.',
                ],
            ],
            [
                'slug' => 'questions-before-consultation',
                'published_at' => '2026-07-06T06:30:00Z',
                'title' => [
                    'en' => 'Five Questions to Prepare Before an Astrology Consultation',
                    'hi' => 'ज्योतिष परामर्श से पहले तैयार करने योग्य पाँच प्रश्न',
                    'gu' => 'જ્યોતિષ પરામર્શ પહેલાં તૈયાર કરવાના પાંચ પ્રશ્નો',
                ],
                'excerpt' => [
                    'en' => 'A little preparation helps turn a broad reading into a useful, focused conversation.',
                    'hi' => 'थोड़ी तैयारी परामर्श को अधिक केंद्रित और उपयोगी बनाती है।',
                    'gu' => 'થોડી તૈયારી પરામર્શને વધુ કેન્દ્રિત અને ઉપયોગી બનાવે છે.',
                ],
                'content' => [
                    'en' => "Begin with the decisions or patterns you genuinely want to understand. A focused question gives the astrologer useful context without limiting the chart.\n\nConsider asking about timing, strengths you can use, recurring obstacles, realistic options and the next practical step. Bring accurate birth details and note any uncertainty in the recorded time.\n\nAstrology is best used as reflective guidance. You remain responsible for medical, legal, financial and personal decisions.",
                    'hi' => "उन निर्णयों या पैटर्न से शुरुआत करें जिन्हें आप सच में समझना चाहते हैं। स्पष्ट प्रश्न ज्योतिषी को उपयोगी संदर्भ देता है।\n\nसमय, अपनी शक्तियों, बार-बार आने वाली बाधाओं, वास्तविक विकल्पों और अगले व्यावहारिक कदम पर प्रश्न तैयार करें। सही जन्म विवरण साथ रखें।\n\nज्योतिष को चिंतनशील मार्गदर्शन की तरह उपयोग करें। चिकित्सा, कानूनी, वित्तीय और निजी निर्णय आपकी जिम्मेदारी हैं।",
                    'gu' => "તમે ખરેખર સમજવા માંગતા નિર્ણયો અથવા પેટર્નથી શરૂઆત કરો. કેન્દ્રિત પ્રશ્ન જ્યોતિષીને ઉપયોગી સંદર્ભ આપે છે.\n\nસમય, તમારી શક્તિઓ, વારંવાર આવતાં અવરોધો, વાસ્તવિક વિકલ્પો અને આગળના વ્યવહારુ પગલા વિશે પ્રશ્નો તૈયાર કરો. સાચી જન્મ વિગતો સાથે રાખો.\n\nજ્યોતિષનો ઉપયોગ વિચારશીલ માર્ગદર્શન તરીકે કરો. તબીબી, કાનૂની, નાણાકીય અને વ્યક્તિગત નિર્ણયો તમારી જવાબદારી છે.",
                ],
                'meta_title' => [
                    'en' => 'How to Prepare for an Astrology Consultation',
                    'hi' => 'ज्योतिष परामर्श की तैयारी कैसे करें',
                    'gu' => 'જ્યોતિષ પરામર્શ માટે કેવી રીતે તૈયારી કરવી',
                ],
                'meta_description' => [
                    'en' => 'Prepare focused questions and accurate birth details for a practical astrology consultation.',
                    'hi' => 'उपयोगी ज्योतिष परामर्श के लिए सही जन्म विवरण और केंद्रित प्रश्न तैयार करें।',
                    'gu' => 'ઉપયોગી જ્યોતિષ પરામર્શ માટે સાચી જન્મ વિગતો અને કેન્દ્રિત પ્રશ્નો તૈયાર કરો.',
                ],
            ],
            [
                'slug' => 'vedic-and-western-astrology',
                'published_at' => '2026-06-28T06:30:00Z',
                'title' => [
                    'en' => 'Vedic and Western Astrology: A Clear, Simple Comparison',
                    'hi' => 'वैदिक और पश्चिमी ज्योतिष: एक सरल तुलना',
                    'gu' => 'વૈદિક અને પશ્ચિમી જ્યોતિષ: સરળ સરખામણી',
                ],
                'excerpt' => [
                    'en' => 'Understand the key differences between sidereal and tropical systems without the jargon.',
                    'hi' => 'साइडेरियल और ट्रॉपिकल प्रणालियों के मुख्य अंतर सरल भाषा में समझें।',
                    'gu' => 'સાઇડેરિયલ અને ટ્રોપિકલ પદ્ધતિઓના મુખ્ય તફાવતો સરળ ભાષામાં સમજો.',
                ],
                'content' => [
                    'en' => "Vedic astrology usually uses the sidereal zodiac and places strong emphasis on lunar mansions, planetary periods and practical timing. Western astrology commonly uses the tropical zodiac and often emphasizes psychological patterns and personal development.\n\nThe systems use different reference points, so a planet may appear in different signs. That does not make one chart a corrected version of the other; each belongs to a distinct interpretive tradition.\n\nChoose the system that matches your questions and practitioner. Consistency matters more than switching systems to seek a preferred answer.",
                    'hi' => "वैदिक ज्योतिष सामान्यतः साइडेरियल राशि-चक्र, नक्षत्र, दशा और व्यावहारिक समय पर बल देता है। पश्चिमी ज्योतिष सामान्यतः ट्रॉपिकल राशि-चक्र और मनोवैज्ञानिक पैटर्न पर बल देता है।\n\nदोनों अलग संदर्भ बिंदु उपयोग करते हैं, इसलिए ग्रह अलग राशियों में दिख सकते हैं। प्रत्येक अपनी अलग व्याख्यात्मक परंपरा है।\n\nअपने प्रश्न और ज्योतिषी के अनुरूप प्रणाली चुनें। पसंदीदा उत्तर पाने के लिए बार-बार प्रणाली बदलने से अधिक महत्वपूर्ण निरंतरता है।",
                    'gu' => "વૈદિક જ્યોતિષ સામાન્ય રીતે સાઇડેરિયલ રાશિચક્ર, નક્ષત્ર, દશા અને વ્યવહારુ સમય પર ભાર મૂકે છે. પશ્ચિમી જ્યોતિષ સામાન્ય રીતે ટ્રોપિકલ રાશિચક્ર અને માનસિક પેટર્ન પર ભાર મૂકે છે.\n\nબંને અલગ સંદર્ભ બિંદુ વાપરે છે, તેથી ગ્રહ અલગ રાશિમાં દેખાઈ શકે છે. દરેક પોતાની અલગ અર્થઘટન પરંપરા છે.\n\nતમારા પ્રશ્ન અને જ્યોતિષીને અનુરૂપ પદ્ધતિ પસંદ કરો. ગમતો જવાબ મેળવવા પદ્ધતિ બદલવા કરતાં સાતત્ય વધુ મહત્વનું છે.",
                ],
                'meta_title' => [
                    'en' => 'Vedic vs Western Astrology',
                    'hi' => 'वैदिक बनाम पश्चिमी ज्योतिष',
                    'gu' => 'વૈદિક સામે પશ્ચિમી જ્યોતિષ',
                ],
                'meta_description' => [
                    'en' => 'Compare Vedic sidereal and Western tropical astrology in clear, practical language.',
                    'hi' => 'वैदिक साइडेरियल और पश्चिमी ट्रॉपिकल ज्योतिष की सरल तुलना पढ़ें।',
                    'gu' => 'વૈદિક સાઇડેરિયલ અને પશ્ચિમી ટ્રોપિકલ જ્યોતિષની સરળ સરખામણી વાંચો.',
                ],
            ],
        ];
    }
}
