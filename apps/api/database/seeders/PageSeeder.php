<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Seeds the fictional "Jyotish Path" static CMS pages — About, Contact, FAQ
 * and the legal pages (Privacy, Terms, Refund/Cancellation) referenced by
 * Settings.legal_links (PRD §4, §5.1). Page title/content/meta_* are
 * translatable (en/hi/gu). Legal copy is clearly marked demonstration text
 * that a real deployment must replace. Idempotent via updateOrCreate on slug.
 */
class PageSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->pages() as $page) {
            Page::updateOrCreate(
                ['slug' => $page['slug']],
                [
                    'title' => $page['title'],
                    'content' => $page['content'],
                    'meta_title' => $page['title'],
                    'meta_description' => $page['meta_description'],
                ],
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function pages(): array
    {
        return [
            [
                'slug' => 'about',
                'title' => [
                    'en' => 'About our practice',
                    'hi' => 'हमारे बारे में',
                    'gu' => 'અમારા વિશે',
                ],
                'content' => [
                    'en' => "Jyotish Path is a fictional demonstration practice built to show how thoughtful astrology services can be presented online.\n\nOur approach combines traditional study with clear, respectful communication. Guidance is framed as perspective for reflection—not a substitute for professional medical, legal or financial advice.",
                    'hi' => "ज्योतिष पथ एक काल्पनिक डेमो संस्था है जो दिखाती है कि विचारशील ज्योतिष सेवाएँ ऑनलाइन कैसे प्रस्तुत की जा सकती हैं।\n\nहम पारंपरिक अध्ययन को स्पष्ट और सम्मानजनक संवाद के साथ जोड़ते हैं। मार्गदर्शन चिंतन के लिए एक दृष्टिकोण है—पेशेवर चिकित्सा, कानूनी या वित्तीय सलाह का विकल्प नहीं।",
                    'gu' => "જ્યોતિષ પથ એક કાલ્પનિક ડેમો સંસ્થા છે જે વિચારશીલ જ્યોતિષ સેવાઓ ઑનલાઇન કેવી રીતે રજૂ કરી શકાય તે બતાવે છે.\n\nઅમે પરંપરાગત અભ્યાસને સ્પષ્ટ અને સન્માનજનક સંવાદ સાથે જોડીએ છીએ. માર્ગદર્શન વિચાર માટેનો દૃષ્ટિકોણ છે—વ્યાવસાયિક તબીબી, કાનૂની અથવા નાણાકીય સલાહનો વિકલ્પ નહીં.",
                ],
                'meta_description' => [
                    'en' => 'Learn about our practical, respectful approach to Vedic astrology.',
                    'hi' => 'वैदिक ज्योतिष के प्रति हमारे व्यावहारिक और सम्मानजनक दृष्टिकोण के बारे में जानें।',
                    'gu' => 'વૈદિક જ્યોતિષ પ્રત્યેના અમારા વ્યવહારુ અને સન્માનજનક અભિગમ વિશે જાણો.',
                ],
            ],
            [
                'slug' => 'contact',
                'title' => [
                    'en' => 'Contact us',
                    'hi' => 'संपर्क करें',
                    'gu' => 'સંપર્ક કરો',
                ],
                'content' => [
                    'en' => "Have a question about a consultation, course or existing request? Use the contact details in the footer and include your booking or enrollment reference when applicable.\n\nPlease do not send sensitive birth details through public social channels.",
                    'hi' => "परामर्श, पाठ्यक्रम या मौजूदा अनुरोध के बारे में प्रश्न है? फुटर में दिए संपर्क विवरण का उपयोग करें और जहाँ लागू हो वहाँ अपना बुकिंग या नामांकन संदर्भ लिखें।\n\nकृपया संवेदनशील जन्म विवरण सार्वजनिक सोशल चैनलों से न भेजें।",
                    'gu' => "પરામર્શ, અભ્યાસક્રમ અથવા હાલની વિનંતી અંગે પ્રશ્ન છે? ફૂટરમાંની સંપર્ક વિગતોનો ઉપયોગ કરો અને લાગુ પડે ત્યાં તમારો બુકિંગ અથવા નોંધણી સંદર્ભ લખો.\n\nકૃપા કરીને સંવેદનશીલ જન્મ વિગતો જાહેર સોશિયલ ચેનલો દ્વારા ન મોકલો.",
                ],
                'meta_description' => [
                    'en' => 'Get in touch about consultations, courses or an existing booking.',
                    'hi' => 'परामर्श, पाठ्यक्रम या मौजूदा बुकिंग के बारे में संपर्क करें।',
                    'gu' => 'પરામર્શ, અભ્યાસક્રમ અથવા હાલના બુકિંગ વિશે સંપર્ક કરો.',
                ],
            ],
            [
                'slug' => 'faq',
                'title' => [
                    'en' => 'Frequently asked questions',
                    'hi' => 'अक्सर पूछे जाने वाले प्रश्न',
                    'gu' => 'વારંવાર પૂછાતા પ્રશ્નો',
                ],
                'content' => [
                    'en' => "Do I need an account?\nNo. Guest booking and course enrollment are supported.\n\nWhen is access confirmed?\nAfter the practice manually verifies the UPI payment linked to your reference number.\n\nIs astrology professional advice?\nNo. Astrology is offered as reflective guidance and does not replace qualified professional advice.",
                    'hi' => "क्या खाता आवश्यक है?\nनहीं। अतिथि बुकिंग और पाठ्यक्रम नामांकन उपलब्ध हैं।\n\nप्रवेश कब मिलता है?\nसंदर्भ नंबर से जुड़े UPI भुगतान की मैनुअल पुष्टि के बाद।\n\nक्या ज्योतिष पेशेवर सलाह है?\nनहीं। ज्योतिष चिंतनशील मार्गदर्शन के रूप में दिया जाता है और योग्य पेशेवर सलाह का विकल्प नहीं है।",
                    'gu' => "શું ખાતું જરૂરી છે?\nના. મહેમાન બુકિંગ અને અભ્યાસક્રમ નોંધણી ઉપલબ્ધ છે.\n\nપ્રવેશ ક્યારે મળે છે?\nસંદર્ભ નંબર સાથે જોડાયેલી UPI ચુકવણીની મેન્યુઅલ પુષ્ટિ પછી.\n\nશું જ્યોતિષ વ્યાવસાયિક સલાહ છે?\nના. જ્યોતિષ વિચારશીલ માર્ગદર્શન તરીકે આપવામાં આવે છે અને લાયક વ્યાવસાયિક સલાહનો વિકલ્પ નથી.",
                ],
                'meta_description' => [
                    'en' => 'Answers about accounts, payment confirmation and how our astrology guidance works.',
                    'hi' => 'खाते, भुगतान पुष्टि और हमारे ज्योतिष मार्गदर्शन के बारे में उत्तर।',
                    'gu' => 'ખાતાં, ચુકવણી પુષ્ટિ અને અમારા જ્યોતિષ માર્ગદર્શન વિશે જવાબો.',
                ],
            ],
            [
                'slug' => 'privacy-policy',
                'title' => [
                    'en' => 'Privacy policy',
                    'hi' => 'गोपनीयता नीति',
                    'gu' => 'ગોપનીયતા નીતિ',
                ],
                'content' => [
                    'en' => "We collect information you submit to provide consultations, course access and saved charts. Only provide information necessary for the service you request.\n\nA production deployment must replace this demonstration policy with client-approved legal text describing retention, processors, rights and contact details.",
                    'hi' => "हम परामर्श, पाठ्यक्रम प्रवेश और सहेजी गई कुंडली देने के लिए आपके द्वारा भेजी गई जानकारी का उपयोग करते हैं। केवल उतनी ही जानकारी दें जितनी आपकी अनुरोधित सेवा के लिए आवश्यक है।\n\nउत्पादन परिनियोजन में इस डेमो नीति को अवधारण, प्रोसेसर, अधिकार और संपर्क विवरण बताने वाले स्वीकृत कानूनी पाठ से बदलना होगा।",
                    'gu' => "પરામર્શ, અભ્યાસક્રમ પ્રવેશ અને સાચવેલી કુંડળી આપવા માટે તમે મોકલેલી માહિતીનો ઉપયોગ થાય છે. તમે વિનંતી કરેલી સેવા માટે જરૂરી હોય તેટલી જ માહિતી આપો.\n\nપ્રોડક્શન ડિપ્લોયમેન્ટમાં આ ડેમો નીતિને જાળવણી, પ્રોસેસર, અધિકારો અને સંપર્ક વિગતો વર્ણવતા મંજૂર કાનૂની લખાણથી બદલવી પડશે.",
                ],
                'meta_description' => [
                    'en' => 'How this demonstration practice handles the information you provide.',
                    'hi' => 'यह डेमो संस्था आपकी दी गई जानकारी को कैसे संभालती है।',
                    'gu' => 'આ ડેમો સંસ્થા તમે આપેલી માહિતી કેવી રીતે સંભાળે છે.',
                ],
            ],
            [
                'slug' => 'terms-and-conditions',
                'title' => [
                    'en' => 'Terms and conditions',
                    'hi' => 'नियम और शर्तें',
                    'gu' => 'નિયમો અને શરતો',
                ],
                'content' => [
                    'en' => "Services and educational content are provided for personal reflection and learning. They are not medical, legal, financial or emergency advice.\n\nA production deployment must replace these demonstration terms with client-approved terms covering service delivery, acceptable use and applicable law.",
                    'hi' => "सेवाएँ और शैक्षिक सामग्री व्यक्तिगत चिंतन और शिक्षा के लिए प्रदान की जाती हैं। ये चिकित्सा, कानूनी, वित्तीय या आपातकालीन सलाह नहीं हैं।\n\nउत्पादन परिनियोजन में इन डेमो शर्तों को सेवा वितरण, स्वीकार्य उपयोग और लागू कानून को कवर करने वाली स्वीकृत शर्तों से बदलना होगा।",
                    'gu' => "સેવાઓ અને શૈક્ષણિક સામગ્રી વ્યક્તિગત વિચાર અને શિક્ષણ માટે પૂરી પાડવામાં આવે છે. તે તબીબી, કાનૂની, નાણાકીય અથવા કટોકટીની સલાહ નથી.\n\nપ્રોડક્શન ડિપ્લોયમેન્ટમાં આ ડેમો શરતોને સેવા વિતરણ, સ્વીકાર્ય ઉપયોગ અને લાગુ કાયદાને આવરી લેતી મંજૂર શરતોથી બદલવી પડશે.",
                ],
                'meta_description' => [
                    'en' => 'The demonstration terms governing use of this practice’s services and content.',
                    'hi' => 'इस संस्था की सेवाओं और सामग्री के उपयोग को नियंत्रित करने वाली डेमो शर्तें।',
                    'gu' => 'આ સંસ્થાની સેવાઓ અને સામગ્રીના ઉપયોગને સંચાલિત કરતી ડેમો શરતો.',
                ],
            ],
            [
                'slug' => 'refund-cancellation-policy',
                'title' => [
                    'en' => 'Refund and cancellation policy',
                    'hi' => 'रिफंड और रद्दीकरण नीति',
                    'gu' => 'રિફંડ અને રદ કરવાની નીતિ',
                ],
                'content' => [
                    'en' => "Contact the practice promptly if you need to reschedule or cancel. Eligibility depends on the service, notice period and whether course access has begun.\n\nA production deployment must replace this demonstration policy with the business's approved cancellation and refund rules.",
                    'hi' => "यदि आपको समय बदलना या रद्द करना है तो संस्था से शीघ्र संपर्क करें। पात्रता सेवा, सूचना अवधि और पाठ्यक्रम प्रवेश शुरू हुआ या नहीं, इस पर निर्भर करती है।\n\nउत्पादन परिनियोजन में इस डेमो नीति को व्यवसाय की स्वीकृत रद्दीकरण और रिफंड नियमों से बदलना होगा।",
                    'gu' => "જો તમારે સમય બદલવો અથવા રદ કરવો હોય તો સંસ્થાનો વહેલો સંપર્ક કરો. પાત્રતા સેવા, સૂચના અવધિ અને અભ્યાસક્રમ પ્રવેશ શરૂ થયો કે નહીં તેના પર આધાર રાખે છે.\n\nપ્રોડક્શન ડિપ્લોયમેન્ટમાં આ ડેમો નીતિને વ્યવસાયના મંજૂર રદ અને રિફંડ નિયમોથી બદલવી પડશે.",
                ],
                'meta_description' => [
                    'en' => 'How rescheduling, cancellations and refunds are handled in this demo.',
                    'hi' => 'इस डेमो में समय बदलाव, रद्दीकरण और रिफंड कैसे संभाले जाते हैं।',
                    'gu' => 'આ ડેમોમાં સમય ફેરફાર, રદ અને રિફંડ કેવી રીતે સંભાળાય છે.',
                ],
            ],
        ];
    }
}
