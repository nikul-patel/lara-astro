import {
  apiForLocale,
  type Astrologer,
  type Post,
  type Service,
  type Testimonial,
} from "@/lib/api";
import type { AppLocale } from "@/i18n/routing";

export type HomeData = {
  services: Service[];
  astrologers: Astrologer[];
  testimonials: Testimonial[];
  posts: Post[];
};

export const demoHomeData: Record<AppLocale, HomeData> = {
  en: {
    services: [
      {
        id: -1,
        slug: "birth-chart-consultation",
        astrologer_id: -1,
        name: "Birth Chart Consultation",
        description:
          "A focused Vedic reading of your chart, current cycles and the questions that matter now.",
        duration_minutes: 45,
        price_inr: 2100,
        price_usd: 35,
      },
      {
        id: -2,
        slug: "career-guidance",
        astrologer_id: -1,
        name: "Career & Business Guidance",
        description:
          "Practical timing and direction for work, leadership, transitions and business decisions.",
        duration_minutes: 60,
        price_inr: 3100,
        price_usd: 49,
      },
      {
        id: -3,
        slug: "relationship-compatibility",
        astrologer_id: -2,
        name: "Relationship Compatibility",
        description:
          "A thoughtful compatibility review that highlights strengths, patterns and shared growth.",
        duration_minutes: 60,
        price_inr: 3500,
        price_usd: 55,
      },
    ],
    astrologers: [
      {
        id: -1,
        slug: "acharya-aarav",
        name: "Acharya Aarav Sharma",
        bio: "A Vedic astrology practitioner known for calm, practical readings rooted in classical Jyotish.",
        photo_url: null,
        specialties: ["Career", "Life direction", "Dashas"],
        languages: ["Hindi", "English"],
        experience_years: 14,
      },
      {
        id: -2,
        slug: "jyotishi-meera",
        name: "Jyotishi Meera Joshi",
        bio: "A compassionate consultant focused on relationships, family patterns and personal clarity.",
        photo_url: null,
        specialties: ["Relationships", "Compatibility", "Wellbeing"],
        languages: ["Gujarati", "Hindi", "English"],
        experience_years: 10,
      },
    ],
    testimonials: [
      {
        id: -1,
        name: "Riya M.",
        quote:
          "The reading was clear and grounded. I left with practical next steps instead of vague predictions.",
        rating: 5,
      },
      {
        id: -2,
        name: "Kunal P.",
        quote:
          "Every question was handled patiently, and the career timing guidance helped me plan with confidence.",
        rating: 5,
      },
      {
        id: -3,
        name: "Ananya S.",
        quote:
          "A warm, respectful consultation that made complex chart details easy to understand.",
        rating: 5,
      },
    ],
    posts: [
      {
        id: -1,
        slug: "understanding-your-lagna",
        title: "Understanding Your Lagna: The Starting Point of a Birth Chart",
        content: "",
        excerpt:
          "Learn why the ascendant shapes the houses, chart ruler and the way your life story unfolds.",
        published_at: "2026-07-12",
      },
      {
        id: -2,
        slug: "questions-before-consultation",
        title: "Five Questions to Prepare Before an Astrology Consultation",
        content: "",
        excerpt:
          "A little preparation helps turn a broad reading into a useful, focused conversation.",
        published_at: "2026-07-06",
      },
      {
        id: -3,
        slug: "vedic-and-western-astrology",
        title: "Vedic and Western Astrology: A Clear, Simple Comparison",
        content: "",
        excerpt:
          "Understand the key differences between sidereal and tropical systems without the jargon.",
        published_at: "2026-06-28",
      },
    ],
  },
  hi: {
    services: [
      {
        id: -1,
        slug: "birth-chart-consultation",
        astrologer_id: -1,
        name: "जन्म कुंडली परामर्श",
        description:
          "आपकी कुंडली, वर्तमान दशाओं और महत्वपूर्ण प्रश्नों का केंद्रित वैदिक विश्लेषण।",
        duration_minutes: 45,
        price_inr: 2100,
        price_usd: 35,
      },
      {
        id: -2,
        slug: "career-guidance",
        astrologer_id: -1,
        name: "करियर और व्यवसाय मार्गदर्शन",
        description:
          "नौकरी, नेतृत्व, बदलाव और व्यावसायिक निर्णयों के लिए व्यावहारिक समय और दिशा।",
        duration_minutes: 60,
        price_inr: 3100,
        price_usd: 49,
      },
      {
        id: -3,
        slug: "relationship-compatibility",
        astrologer_id: -2,
        name: "विवाह और संबंध अनुकूलता",
        description:
          "संबंधों की शक्तियों, प्रवृत्तियों और साझा विकास का संवेदनशील विश्लेषण।",
        duration_minutes: 60,
        price_inr: 3500,
        price_usd: 55,
      },
    ],
    astrologers: [
      {
        id: -1,
        slug: "acharya-aarav",
        name: "आचार्य आरव शर्मा",
        bio: "शास्त्रीय ज्योतिष पर आधारित शांत और व्यावहारिक परामर्श के लिए प्रसिद्ध वैदिक ज्योतिषी।",
        photo_url: null,
        specialties: ["करियर", "जीवन दिशा", "दशा"],
        languages: ["हिंदी", "अंग्रेज़ी"],
        experience_years: 14,
      },
      {
        id: -2,
        slug: "jyotishi-meera",
        name: "ज्योतिषी मीरा जोशी",
        bio: "रिश्तों, पारिवारिक प्रवृत्तियों और व्यक्तिगत स्पष्टता पर केंद्रित संवेदनशील सलाहकार।",
        photo_url: null,
        specialties: ["रिश्ते", "अनुकूलता", "कल्याण"],
        languages: ["गुजराती", "हिंदी", "अंग्रेज़ी"],
        experience_years: 10,
      },
    ],
    testimonials: [
      {
        id: -1,
        name: "रिया एम.",
        quote:
          "परामर्श स्पष्ट और व्यावहारिक था। मुझे अस्पष्ट भविष्यवाणियों के बजाय उपयोगी अगले कदम मिले।",
        rating: 5,
      },
      {
        id: -2,
        name: "कुणाल पी.",
        quote:
          "हर प्रश्न को धैर्य से समझाया गया और करियर संबंधी मार्गदर्शन ने आत्मविश्वास दिया।",
        rating: 5,
      },
    ],
    posts: [
      {
        id: -1,
        slug: "understanding-your-lagna",
        title: "अपने लग्न को समझें: जन्म कुंडली का आरंभिक बिंदु",
        content: "",
        excerpt: "जानें कि लग्न भावों, लग्नेश और जीवन की दिशा को कैसे प्रभावित करता है।",
        published_at: "2026-07-12",
      },
      {
        id: -2,
        slug: "questions-before-consultation",
        title: "ज्योतिष परामर्श से पहले तैयार करने योग्य पाँच प्रश्न",
        content: "",
        excerpt: "थोड़ी तैयारी परामर्श को अधिक केंद्रित और उपयोगी बनाती है।",
        published_at: "2026-07-06",
      },
    ],
  },
  gu: {
    services: [
      {
        id: -1,
        slug: "birth-chart-consultation",
        astrologer_id: -1,
        name: "જન્મકુંડળી પરામર્શ",
        description:
          "તમારી કુંડળી, વર્તમાન દશા અને મહત્વના પ્રશ્નોનું કેન્દ્રિત વૈદિક વિશ્લેષણ.",
        duration_minutes: 45,
        price_inr: 2100,
        price_usd: 35,
      },
      {
        id: -2,
        slug: "career-guidance",
        astrologer_id: -1,
        name: "કારકિર્દી અને વ્યવસાય માર્ગદર્શન",
        description:
          "નોકરી, નેતૃત્વ, પરિવર્તન અને વ્યવસાયિક નિર્ણયો માટે વ્યવહારુ સમય અને દિશા.",
        duration_minutes: 60,
        price_inr: 3100,
        price_usd: 49,
      },
      {
        id: -3,
        slug: "relationship-compatibility",
        astrologer_id: -2,
        name: "લગ્ન અને સંબંધ સુસંગતતા",
        description:
          "સંબંધોની શક્તિઓ, પેટર્ન અને સહિયારા વિકાસનું સંવેદનશીલ વિશ્લેષણ.",
        duration_minutes: 60,
        price_inr: 3500,
        price_usd: 55,
      },
    ],
    astrologers: [
      {
        id: -1,
        slug: "acharya-aarav",
        name: "આચાર્ય આરવ શર્મા",
        bio: "શાસ્ત્રીય જ્યોતિષ પર આધારિત શાંત અને વ્યવહારુ પરામર્શ માટે જાણીતા વૈદિક જ્યોતિષી.",
        photo_url: null,
        specialties: ["કારકિર્દી", "જીવન દિશા", "દશા"],
        languages: ["હિન્દી", "અંગ્રેજી"],
        experience_years: 14,
      },
      {
        id: -2,
        slug: "jyotishi-meera",
        name: "જ્યોતિષી મીરા જોશી",
        bio: "સંબંધો, પારિવારિક પેટર્ન અને વ્યક્તિગત સ્પષ્ટતા પર કેન્દ્રિત સંવેદનશીલ સલાહકાર.",
        photo_url: null,
        specialties: ["સંબંધો", "સુસંગતતા", "સુખાકારી"],
        languages: ["ગુજરાતી", "હિન્દી", "અંગ્રેજી"],
        experience_years: 10,
      },
    ],
    testimonials: [
      {
        id: -1,
        name: "રિયા એમ.",
        quote:
          "પરામર્શ સ્પષ્ટ અને વ્યવહારુ હતો. અસ્પષ્ટ આગાહીઓને બદલે ઉપયોગી આગળનાં પગલાં મળ્યાં.",
        rating: 5,
      },
      {
        id: -2,
        name: "કુણાલ પી.",
        quote:
          "દરેક પ્રશ્ન ધીરજથી સમજાવવામાં આવ્યો અને કારકિર્દી માર્ગદર્શનથી આત્મવિશ્વાસ મળ્યો.",
        rating: 5,
      },
    ],
    posts: [
      {
        id: -1,
        slug: "understanding-your-lagna",
        title: "તમારું લગ્ન સમજો: જન્મકુંડળીનો પ્રારંભિક બિંદુ",
        content: "",
        excerpt: "જાણો કે લગ્ન ભાવો, લગ્નેશ અને જીવનની દિશાને કેવી રીતે અસર કરે છે.",
        published_at: "2026-07-12",
      },
      {
        id: -2,
        slug: "questions-before-consultation",
        title: "જ્યોતિષ પરામર્શ પહેલાં તૈયાર કરવાના પાંચ પ્રશ્નો",
        content: "",
        excerpt: "થોડી તૈયારી પરામર્શને વધુ કેન્દ્રિત અને ઉપયોગી બનાવે છે.",
        published_at: "2026-07-06",
      },
    ],
  },
};

export async function getHomeData(locale: AppLocale): Promise<HomeData> {
  const localizedApi = apiForLocale(locale);
  const fallback = demoHomeData[locale];

  const [services, astrologers, testimonials, posts] = await Promise.all([
    localizedApi.services
      .list()
      .then((response) => response.data.slice(0, 3))
      .then((items) => (items.length > 0 ? items : fallback.services))
      .catch(() => fallback.services),
    localizedApi.astrologers
      .list()
      .then((response) => response.data.slice(0, 2))
      .then((items) => (items.length > 0 ? items : fallback.astrologers))
      .catch(() => fallback.astrologers),
    localizedApi
      .testimonials()
      .then((response) => response.data.slice(0, 3))
      .then((items) => (items.length > 0 ? items : fallback.testimonials))
      .catch(() => fallback.testimonials),
    localizedApi.posts
      .list()
      .then((response) => response.data.slice(0, 3))
      .then((items) => (items.length > 0 ? items : fallback.posts))
      .catch(() => fallback.posts),
  ]);

  return { services, astrologers, testimonials, posts };
}
