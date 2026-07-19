import { cache } from "react";
import type { AppLocale } from "@/i18n/routing";
import { apiForLocale, type CmsPage, type PaginatedResponse, type Post } from "@/lib/api";

const postCopy: Record<AppLocale, Array<Omit<Post, "id">>> = {
  en: [
    { slug: "understanding-your-lagna", title: "Understanding Your Lagna: The Starting Point of a Birth Chart", excerpt: "Learn why the ascendant shapes the houses, chart ruler and the way your life story unfolds.", content: "Your lagna, or ascendant, is the zodiac sign rising on the eastern horizon at your birth. It sets the first house and gives every other house its place in the chart.\n\nAstrologers study the lagna, its ruling planet and influences on the first house together. This combination describes how you meet life, use your energy and respond to new circumstances.\n\nThe lagna is a starting point, not a complete verdict. A useful reading always considers it alongside the Moon, Sun, planetary periods and the questions you bring.", meta_title: "What Is Lagna in Vedic Astrology?", meta_description: "Understand lagna, the ascendant and its role as the starting point of a Vedic birth chart.", published_at: "2026-07-12T06:30:00Z" },
    { slug: "questions-before-consultation", title: "Five Questions to Prepare Before an Astrology Consultation", excerpt: "A little preparation helps turn a broad reading into a useful, focused conversation.", content: "Begin with the decisions or patterns you genuinely want to understand. A focused question gives the astrologer useful context without limiting the chart.\n\nConsider asking about timing, strengths you can use, recurring obstacles, realistic options and the next practical step. Bring accurate birth details and note any uncertainty in the recorded time.\n\nAstrology is best used as reflective guidance. You remain responsible for medical, legal, financial and personal decisions.", meta_title: "How to Prepare for an Astrology Consultation", meta_description: "Prepare focused questions and accurate birth details for a practical astrology consultation.", published_at: "2026-07-06T06:30:00Z" },
    { slug: "vedic-and-western-astrology", title: "Vedic and Western Astrology: A Clear, Simple Comparison", excerpt: "Understand the key differences between sidereal and tropical systems without the jargon.", content: "Vedic astrology usually uses the sidereal zodiac and places strong emphasis on lunar mansions, planetary periods and practical timing. Western astrology commonly uses the tropical zodiac and often emphasizes psychological patterns and personal development.\n\nThe systems use different reference points, so a planet may appear in different signs. That does not make one chart a corrected version of the other; each belongs to a distinct interpretive tradition.\n\nChoose the system that matches your questions and practitioner. Consistency matters more than switching systems to seek a preferred answer.", meta_title: "Vedic vs Western Astrology", meta_description: "Compare Vedic sidereal and Western tropical astrology in clear, practical language.", published_at: "2026-06-28T06:30:00Z" },
  ],
  hi: [
    { slug: "understanding-your-lagna", title: "अपने लग्न को समझें: जन्म कुंडली का आरंभिक बिंदु", excerpt: "जानें कि लग्न भावों, लग्नेश और जीवन की दिशा को कैसे प्रभावित करता है।", content: "लग्न वह राशि है जो आपके जन्म के समय पूर्वी क्षितिज पर उदित हो रही थी। यही प्रथम भाव तय करता है और पूरी कुंडली के भावों को क्रम देता है।\n\nज्योतिषी लग्न, उसके स्वामी और प्रथम भाव पर पड़ने वाले प्रभावों को साथ में देखते हैं। इससे जीवन के प्रति आपका दृष्टिकोण और नई परिस्थितियों में आपकी प्रतिक्रिया समझी जाती है।\n\nलग्न शुरुआत है, अंतिम निष्कर्ष नहीं। उपयोगी विश्लेषण में चंद्रमा, सूर्य, दशाएँ और आपके प्रश्न भी शामिल होते हैं।", meta_title: "वैदिक ज्योतिष में लग्न क्या है?", meta_description: "जन्म कुंडली के आरंभिक बिंदु लग्न और लग्नेश की भूमिका समझें।", published_at: "2026-07-12T06:30:00Z" },
    { slug: "questions-before-consultation", title: "ज्योतिष परामर्श से पहले तैयार करने योग्य पाँच प्रश्न", excerpt: "थोड़ी तैयारी परामर्श को अधिक केंद्रित और उपयोगी बनाती है।", content: "उन निर्णयों या पैटर्न से शुरुआत करें जिन्हें आप सच में समझना चाहते हैं। स्पष्ट प्रश्न ज्योतिषी को उपयोगी संदर्भ देता है।\n\nसमय, अपनी शक्तियों, बार-बार आने वाली बाधाओं, वास्तविक विकल्पों और अगले व्यावहारिक कदम पर प्रश्न तैयार करें। सही जन्म विवरण साथ रखें।\n\nज्योतिष को चिंतनशील मार्गदर्शन की तरह उपयोग करें। चिकित्सा, कानूनी, वित्तीय और निजी निर्णय आपकी जिम्मेदारी हैं।", meta_title: "ज्योतिष परामर्श की तैयारी कैसे करें", meta_description: "उपयोगी ज्योतिष परामर्श के लिए सही जन्म विवरण और केंद्रित प्रश्न तैयार करें।", published_at: "2026-07-06T06:30:00Z" },
    { slug: "vedic-and-western-astrology", title: "वैदिक और पश्चिमी ज्योतिष: एक सरल तुलना", excerpt: "साइडेरियल और ट्रॉपिकल प्रणालियों के मुख्य अंतर सरल भाषा में समझें।", content: "वैदिक ज्योतिष सामान्यतः साइडेरियल राशि-चक्र, नक्षत्र, दशा और व्यावहारिक समय पर बल देता है। पश्चिमी ज्योतिष सामान्यतः ट्रॉपिकल राशि-चक्र और मनोवैज्ञानिक पैटर्न पर बल देता है।\n\nदोनों अलग संदर्भ बिंदु उपयोग करते हैं, इसलिए ग्रह अलग राशियों में दिख सकते हैं। प्रत्येक अपनी अलग व्याख्यात्मक परंपरा है।\n\nअपने प्रश्न और ज्योतिषी के अनुरूप प्रणाली चुनें। पसंदीदा उत्तर पाने के लिए बार-बार प्रणाली बदलने से अधिक महत्वपूर्ण निरंतरता है।", meta_title: "वैदिक बनाम पश्चिमी ज्योतिष", meta_description: "वैदिक साइडेरियल और पश्चिमी ट्रॉपिकल ज्योतिष की सरल तुलना पढ़ें।", published_at: "2026-06-28T06:30:00Z" },
  ],
  gu: [
    { slug: "understanding-your-lagna", title: "તમારું લગ્ન સમજો: જન્મકુંડળીનો પ્રારંભિક બિંદુ", excerpt: "જાણો કે લગ્ન ભાવો, લગ્નેશ અને જીવનની દિશાને કેવી રીતે અસર કરે છે.", content: "લગ્ન એ રાશિ છે જે તમારા જન્મ સમયે પૂર્વ ક્ષિતિજ પર ઉગતી હતી. તે પ્રથમ ભાવ નક્કી કરે છે અને કુંડળીના બાકીના ભાવોને સ્થાન આપે છે.\n\nજ્યોતિષી લગ્ન, તેના સ્વામી અને પ્રથમ ભાવ પરના પ્રભાવોને સાથે જુએ છે. આ સંયોજન જીવન પ્રત્યેનો તમારો અભિગમ અને નવી પરિસ્થિતિમાં પ્રતિભાવ સમજવામાં મદદ કરે છે.\n\nલગ્ન શરૂઆત છે, અંતિમ ચુકાદો નહીં. ઉપયોગી વાંચનમાં ચંદ્ર, સૂર્ય, દશા અને તમારા પ્રશ્નો પણ સામેલ થાય છે.", meta_title: "વૈદિક જ્યોતિષમાં લગ્ન શું છે?", meta_description: "જન્મકુંડળીના પ્રારંભિક બિંદુ લગ્ન અને લગ્નેશની ભૂમિકા સમજો.", published_at: "2026-07-12T06:30:00Z" },
    { slug: "questions-before-consultation", title: "જ્યોતિષ પરામર્શ પહેલાં તૈયાર કરવાના પાંચ પ્રશ્નો", excerpt: "થોડી તૈયારી પરામર્શને વધુ કેન્દ્રિત અને ઉપયોગી બનાવે છે.", content: "તમે ખરેખર સમજવા માંગતા નિર્ણયો અથવા પેટર્નથી શરૂઆત કરો. કેન્દ્રિત પ્રશ્ન જ્યોતિષીને ઉપયોગી સંદર્ભ આપે છે.\n\nસમય, તમારી શક્તિઓ, વારંવાર આવતાં અવરોધો, વાસ્તવિક વિકલ્પો અને આગળના વ્યવહારુ પગલા વિશે પ્રશ્નો તૈયાર કરો. સાચી જન્મ વિગતો સાથે રાખો.\n\nજ્યોતિષનો ઉપયોગ વિચારશીલ માર્ગદર્શન તરીકે કરો. તબીબી, કાનૂની, નાણાકીય અને વ્યક્તિગત નિર્ણયો તમારી જવાબદારી છે.", meta_title: "જ્યોતિષ પરામર્શ માટે કેવી રીતે તૈયારી કરવી", meta_description: "ઉપયોગી જ્યોતિષ પરામર્શ માટે સાચી જન્મ વિગતો અને કેન્દ્રિત પ્રશ્નો તૈયાર કરો.", published_at: "2026-07-06T06:30:00Z" },
    { slug: "vedic-and-western-astrology", title: "વૈદિક અને પશ્ચિમી જ્યોતિષ: સરળ સરખામણી", excerpt: "સાઇડેરિયલ અને ટ્રોપિકલ પદ્ધતિઓના મુખ્ય તફાવતો સરળ ભાષામાં સમજો.", content: "વૈદિક જ્યોતિષ સામાન્ય રીતે સાઇડેરિયલ રાશિચક્ર, નક્ષત્ર, દશા અને વ્યવહારુ સમય પર ભાર મૂકે છે. પશ્ચિમી જ્યોતિષ સામાન્ય રીતે ટ્રોપિકલ રાશિચક્ર અને માનસિક પેટર્ન પર ભાર મૂકે છે.\n\nબંને અલગ સંદર્ભ બિંદુ વાપરે છે, તેથી ગ્રહ અલગ રાશિમાં દેખાઈ શકે છે. દરેક પોતાની અલગ અર્થઘટન પરંપરા છે.\n\nતમારા પ્રશ્ન અને જ્યોતિષીને અનુરૂપ પદ્ધતિ પસંદ કરો. ગમતો જવાબ મેળવવા પદ્ધતિ બદલવા કરતાં સાતત્ય વધુ મહત્વનું છે.", meta_title: "વૈદિક સામે પશ્ચિમી જ્યોતિષ", meta_description: "વૈદિક સાઇડેરિયલ અને પશ્ચિમી ટ્રોપિકલ જ્યોતિષની સરળ સરખામણી વાંચો.", published_at: "2026-06-28T06:30:00Z" },
  ],
};

const pageCopy: Record<AppLocale, Record<string, Omit<CmsPage, "id" | "slug">>> = {
  en: {
    about: { title: "About our practice", content: "Jyotish Path is a fictional demonstration practice built to show how thoughtful astrology services can be presented online.\n\nOur approach combines traditional study with clear, respectful communication. Guidance is framed as perspective for reflection—not a substitute for professional medical, legal or financial advice.", meta_description: "Learn about our practical, respectful approach to Vedic astrology." },
    contact: { title: "Contact us", content: "Have a question about a consultation, course or existing request? Use the contact details in the footer and include your booking or enrollment reference when applicable.\n\nPlease do not send sensitive birth details through public social channels." },
    faq: { title: "Frequently asked questions", content: "Do I need an account?\nNo. Guest booking and course enrollment are supported.\n\nWhen is access confirmed?\nAfter the practice manually verifies the UPI payment linked to your reference number.\n\nIs astrology professional advice?\nNo. Astrology is offered as reflective guidance and does not replace qualified professional advice." },
    "privacy-policy": { title: "Privacy policy", content: "We collect information you submit to provide consultations, course access and saved charts. Only provide information necessary for the service you request.\n\nA production deployment must replace this demonstration policy with client-approved legal text describing retention, processors, rights and contact details." },
    "terms-and-conditions": { title: "Terms and conditions", content: "Services and educational content are provided for personal reflection and learning. They are not medical, legal, financial or emergency advice.\n\nA production deployment must replace these demonstration terms with client-approved terms covering service delivery, acceptable use and applicable law." },
    "refund-cancellation-policy": { title: "Refund and cancellation policy", content: "Contact the practice promptly if you need to reschedule or cancel. Eligibility depends on the service, notice period and whether course access has begun.\n\nA production deployment must replace this demonstration policy with the business's approved cancellation and refund rules." },
  },
  hi: {
    about: { title: "हमारे बारे में", content: "ज्योतिष पथ एक काल्पनिक डेमो संस्था है जो दिखाती है कि विचारशील ज्योतिष सेवाएँ ऑनलाइन कैसे प्रस्तुत की जा सकती हैं।\n\nहम पारंपरिक अध्ययन को स्पष्ट और सम्मानजनक संवाद के साथ जोड़ते हैं।" },
    contact: { title: "संपर्क करें", content: "परामर्श, पाठ्यक्रम या मौजूदा अनुरोध के बारे में प्रश्न के लिए फुटर में दिए संपर्क विवरण का उपयोग करें। जहाँ लागू हो वहाँ अपना संदर्भ नंबर लिखें।" },
    faq: { title: "अक्सर पूछे जाने वाले प्रश्न", content: "क्या खाता आवश्यक है?\nनहीं। अतिथि बुकिंग और नामांकन उपलब्ध हैं।\n\nप्रवेश कब मिलता है?\nसंदर्भ से जुड़े UPI भुगतान की मैनुअल पुष्टि के बाद।" },
    "privacy-policy": { title: "गोपनीयता नीति", content: "हम परामर्श, पाठ्यक्रम और सहेजी कुंडली देने के लिए आपके द्वारा भेजी जानकारी का उपयोग करते हैं। उत्पादन से पहले इस डेमो नीति को स्वीकृत कानूनी पाठ से बदलना होगा।" },
    "terms-and-conditions": { title: "नियम और शर्तें", content: "सेवाएँ व्यक्तिगत चिंतन और शिक्षा के लिए हैं। वे चिकित्सा, कानूनी या वित्तीय सलाह नहीं हैं। उत्पादन से पहले स्वीकृत शर्तें जोड़ें।" },
    "refund-cancellation-policy": { title: "रिफंड और रद्दीकरण नीति", content: "समय बदलने या रद्द करने के लिए संस्था से शीघ्र संपर्क करें। उत्पादन से पहले व्यवसाय की स्वीकृत नीति जोड़ें।" },
  },
  gu: {
    about: { title: "અમારા વિશે", content: "જ્યોતિષ પથ એક કાલ્પનિક ડેમો સંસ્થા છે જે વિચારશીલ જ્યોતિષ સેવાઓ ઑનલાઇન કેવી રીતે રજૂ કરી શકાય તે બતાવે છે.\n\nઅમે પરંપરાગત અભ્યાસને સ્પષ્ટ અને સન્માનજનક સંવાદ સાથે જોડીએ છીએ." },
    contact: { title: "સંપર્ક કરો", content: "પરામર્શ, અભ્યાસક્રમ અથવા હાલની વિનંતી અંગે પ્રશ્ન માટે ફૂટરમાંની સંપર્ક વિગતોનો ઉપયોગ કરો. લાગુ પડે ત્યાં સંદર્ભ નંબર લખો." },
    faq: { title: "વારંવાર પૂછાતા પ્રશ્નો", content: "શું ખાતું જરૂરી છે?\nના. મહેમાન બુકિંગ અને નોંધણી ઉપલબ્ધ છે.\n\nપ્રવેશ ક્યારે મળે છે?\nસંદર્ભ સાથે જોડાયેલી UPI ચુકવણીની મેન્યુઅલ પુષ્ટિ પછી." },
    "privacy-policy": { title: "ગોપનીયતા નીતિ", content: "પરામર્શ, અભ્યાસક્રમ અને સાચવેલી કુંડળી આપવા માટે તમે મોકલેલી માહિતીનો ઉપયોગ થાય છે. પ્રોડક્શન પહેલાં આ ડેમો નીતિને મંજૂર કાનૂની લખાણથી બદલો." },
    "terms-and-conditions": { title: "નિયમો અને શરતો", content: "સેવાઓ વ્યક્તિગત વિચાર અને શિક્ષણ માટે છે. તે તબીબી, કાનૂની અથવા નાણાકીય સલાહ નથી. પ્રોડક્શન પહેલાં મંજૂર શરતો ઉમેરો." },
    "refund-cancellation-policy": { title: "રિફંડ અને રદ કરવાની નીતિ", content: "સમય બદલવા અથવા રદ કરવા માટે સંસ્થાનો વહેલો સંપર્ક કરો. પ્રોડક્શન પહેલાં વ્યવસાયની મંજૂર નીતિ ઉમેરો." },
  },
};

const demoPosts = Object.fromEntries(Object.entries(postCopy).map(([locale, posts]) => [locale, posts.map((post, index) => ({ ...post, id: -(index + 1) }))])) as Record<AppLocale, Post[]>;
export const demoPostSlugs = demoPosts.en.map((post) => post.slug);
export const cmsPageSlugs = Object.keys(pageCopy.en);

export async function getPosts(locale: AppLocale, page = 1): Promise<PaginatedResponse<Post>> {
  try {
    const response = await apiForLocale(locale).posts.list({ page });
    if (response.data.length || page > 1) return response;
  } catch { /* Demo content keeps preview and static builds useful. */ }
  const data = page === 1 ? demoPosts[locale] : [];
  return { data, meta: { current_page: page, last_page: 1, per_page: 10, total: demoPosts[locale].length } };
}

export const getPost = cache(async (locale: AppLocale, slug: string): Promise<Post | null> => {
  try { return await apiForLocale(locale).posts.get(slug); } catch { return demoPosts[locale].find((post) => post.slug === slug) ?? null; }
});

export const getCmsPage = cache(async (locale: AppLocale, slug: string): Promise<CmsPage | null> => {
  try { return await apiForLocale(locale).pages.get(slug); } catch {
    const page = pageCopy[locale][slug];
    return page ? { ...page, id: -1, slug } : null;
  }
});
