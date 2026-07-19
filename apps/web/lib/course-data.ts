import type { AppLocale } from "@/i18n/routing";
import {
  ApiError,
  apiForLocale,
  type Course,
  type PaginatedResponse,
} from "@/lib/api";

export type CourseReview = {
  id: number;
  name: string;
  rating: number;
  quote: string;
};

export type CourseDetail = Course & { reviews: CourseReview[] };

const demoCourses: Record<AppLocale, CourseDetail[]> = {
  en: [
    {
      id: -1,
      slug: "vedic-astrology-foundations",
      title: "Vedic Astrology Foundations",
      description: "Learn signs, planets, houses and the practical steps for reading a birth chart with confidence.",
      type: "recorded",
      price_inr: 4999,
      price_usd: 79,
      instructor: { id: -1, slug: "acharya-anaya", name: "Acharya Anaya", bio: "Vedic astrologer and teacher", photo_url: null, specialties: ["Vedic astrology"], languages: ["English", "Hindi"] },
      modules: [
        { id: -1, title: "The astrological alphabet", lessons: [{ id: -1, title: "Planets and their roles", duration_minutes: 28 }, { id: -2, title: "Signs, elements and qualities", duration_minutes: 34 }] },
        { id: -2, title: "Reading the chart", lessons: [{ id: -3, title: "The twelve houses", duration_minutes: 42 }, { id: -4, title: "A guided chart interpretation", duration_minutes: 51 }] },
      ],
      reviews: [{ id: -1, name: "Meera S.", rating: 5, quote: "A calm, structured introduction that made chart reading feel approachable." }],
    },
    {
      id: -2,
      slug: "live-chart-reading-intensive",
      title: "Live Chart Reading Intensive",
      description: "Practice synthesis and interpretation in a small live cohort with instructor feedback.",
      type: "live",
      price_inr: 8999,
      price_usd: 139,
      instructor: { id: -2, slug: "pandit-rohan", name: "Pandit Rohan", bio: "Jyotish consultant and mentor", photo_url: null, specialties: ["Chart interpretation"], languages: ["English", "Gujarati"] },
      modules: [{ id: -3, title: "Cohort preparation", lessons: [{ id: -5, title: "Chart synthesis workbook", duration_minutes: 25 }] }],
      live_sessions: [
        { id: -1, starts_at: "2026-08-08T10:30:00+05:30", ends_at: "2026-08-08T12:00:00+05:30" },
        { id: -2, starts_at: "2026-08-15T10:30:00+05:30", ends_at: "2026-08-15T12:00:00+05:30" },
      ],
      reviews: [{ id: -2, name: "Kunal P.", rating: 5, quote: "The live examples and direct feedback sharpened my interpretation process." }],
    },
  ],
  hi: [],
  gu: [],
};

demoCourses.hi = demoCourses.en.map((course, index) => ({
  ...course,
  title: index === 0 ? "वैदिक ज्योतिष की बुनियाद" : "लाइव कुंडली अध्ययन गहन पाठ्यक्रम",
  description: index === 0 ? "राशियों, ग्रहों, भावों और आत्मविश्वास से कुंडली पढ़ने की व्यावहारिक प्रक्रिया सीखें।" : "छोटे लाइव समूह में शिक्षक की प्रतिक्रिया के साथ कुंडली विश्लेषण का अभ्यास करें।",
  modules: course.modules?.map((module, moduleIndex) => ({ ...module, title: moduleIndex === 0 ? "ज्योतिष की मूल भाषा" : "कुंडली पढ़ना" })),
  reviews: course.reviews.map((review) => ({ ...review, quote: index === 0 ? "शांत और क्रमबद्ध शिक्षा ने कुंडली पढ़ना सरल बना दिया।" : "लाइव उदाहरणों और सीधी प्रतिक्रिया से मेरी विश्लेषण प्रक्रिया बेहतर हुई।" })),
}));

demoCourses.gu = demoCourses.en.map((course, index) => ({
  ...course,
  title: index === 0 ? "વૈદિક જ્યોતિષના પાયા" : "લાઇવ કુંડળી વાંચન ઇન્ટેન્સિવ",
  description: index === 0 ? "રાશિ, ગ્રહ, ભાવ અને આત્મવિશ્વાસથી કુંડળી વાંચવાની વ્યવહારુ રીત શીખો." : "નાના લાઇવ સમૂહમાં શિક્ષકના પ્રતિસાદ સાથે કુંડળી વિશ્લેષણનો અભ્યાસ કરો.",
  modules: course.modules?.map((module, moduleIndex) => ({ ...module, title: moduleIndex === 0 ? "જ્યોતિષની મૂળ ભાષા" : "કુંડળી વાંચન" })),
  reviews: course.reviews.map((review) => ({ ...review, quote: index === 0 ? "શાંત અને સુવ્યવસ્થિત પાઠોથી કુંડળી વાંચવું સરળ બન્યું." : "લાઇવ ઉદાહરણો અને સીધા પ્રતિસાદથી મારી વિશ્લેષણ પદ્ધતિ સુધરી." })),
}));

async function getAllPages<T>(getPage: (page: number) => Promise<PaginatedResponse<T>>): Promise<T[]> {
  const items: T[] = [];
  let page = 1;
  let lastPage = 1;
  do {
    const response = await getPage(page);
    items.push(...response.data);
    lastPage = response.meta.last_page;
    page += 1;
  } while (page <= lastPage);
  return items;
}

export const demoCourseSlugs = demoCourses.en.map((course) => course.slug);

export async function getCourses(locale: AppLocale): Promise<CourseDetail[]> {
  try {
    const courses = await getAllPages((page) => apiForLocale(locale).courses.list({ page }));
    return courses.length ? courses.map((course) => ({ ...course, reviews: [] })) : demoCourses[locale];
  } catch {
    return demoCourses[locale];
  }
}

export async function getCourse(locale: AppLocale, slug: string): Promise<CourseDetail | null> {
  const fallback = demoCourses[locale].find((course) => course.slug === slug);
  try {
    const course = await apiForLocale(locale).courses.get(slug);
    return { ...course, reviews: [] };
  } catch (error) {
    if (fallback) return fallback;
    if (error instanceof ApiError && error.status === 404) return null;
    throw error;
  }
}
