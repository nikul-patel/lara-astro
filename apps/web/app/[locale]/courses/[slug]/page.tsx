import type { Metadata } from "next";
import { hasLocale } from "next-intl";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { notFound } from "next/navigation";
import { CoursePrice } from "@/components/courses/course-price";
import { EnrollmentForm } from "@/components/courses/enrollment-form";
import { LiveSessionTime } from "@/components/courses/live-session-time";
import { getLocalizedAlternates, getSiteUrl } from "@/i18n/metadata";
import { Link } from "@/i18n/navigation";
import { routing, type AppLocale } from "@/i18n/routing";
import { demoCourseSlugs, getCourse } from "@/lib/course-data";
import { getSiteSettings } from "@/lib/site-settings";

export const revalidate = 3600;

type CoursePageProps = { params: Promise<{ locale: string; slug: string }> };

export function generateStaticParams() { return demoCourseSlugs.map((slug) => ({ slug })); }

async function getRouteParams(params: CoursePageProps["params"]) {
  const { locale, slug } = await params;
  if (!hasLocale(routing.locales, locale)) notFound();
  return { locale, slug } as { locale: AppLocale; slug: string };
}

export async function generateMetadata({ params }: CoursePageProps): Promise<Metadata> {
  const { locale, slug } = await getRouteParams(params);
  const course = await getCourse(locale, slug);
  if (!course) notFound();
  return { title: course.title, description: course.description, alternates: getLocalizedAlternates(locale, `courses/${slug}`) };
}

export default async function CoursePage({ params }: CoursePageProps) {
  const { locale, slug } = await getRouteParams(params);
  setRequestLocale(locale);
  const [t, course, settings] = await Promise.all([getTranslations("CourseDetail"), getCourse(locale, slug), getSiteSettings()]);
  if (!course) notFound();
  const jsonLd = { "@context": "https://schema.org", "@type": "Course", name: course.title, description: course.description, url: new URL(`/${locale}/courses/${slug}`, getSiteUrl()).toString(), provider: { "@type": "Organization", name: settings.site_name }, offers: [{ "@type": "Offer", price: course.price_inr, priceCurrency: "INR" }, { "@type": "Offer", price: course.price_usd, priceCurrency: "USD" }] };

  return (
    <main className="flex-1 bg-[#fffcf7] text-stone-900">
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd).replace(/</g, "\\u003c") }} />
      <section className="border-b border-amber-900/10 bg-amber-50"><div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8"><Link href="/courses" className="text-sm font-bold text-amber-800">← {t("allCourses")}</Link><div className="mt-9 grid gap-10 lg:grid-cols-[1fr_24rem] lg:items-end"><div><span className="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold uppercase tracking-wider text-amber-900">{t(`types.${course.type}`)}</span><h1 className="mt-5 text-4xl font-bold tracking-tight text-amber-950 sm:text-5xl">{course.title}</h1><p className="mt-5 max-w-3xl text-lg leading-8 text-stone-600">{course.description}</p>{course.instructor && <p className="mt-5 font-bold text-stone-800">{t("taughtBy", { name: course.instructor.name })}</p>}</div><div className="rounded-3xl bg-white p-6 shadow-sm"><p className="text-sm font-semibold text-stone-500">{t("coursePrice")}</p><p className="mt-2 text-3xl font-bold text-amber-800"><CoursePrice inr={course.price_inr} usd={course.price_usd} /></p><a href="#enroll" className="mt-5 inline-flex w-full justify-center rounded-full bg-amber-800 px-5 py-3 text-sm font-bold text-white">{t("enrollNow")}</a></div></div></div></section>
      <div className="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 lg:grid-cols-[1fr_24rem] lg:px-8 lg:py-20">
        <div className="space-y-14">
          <section><p className="text-sm font-bold uppercase tracking-[0.18em] text-amber-700">{t("curriculumEyebrow")}</p><h2 className="mt-2 text-3xl font-bold">{t("curriculumTitle")}</h2>{course.modules?.length ? <ol className="mt-7 space-y-4">{course.modules.map((module, index) => <li key={module.id} className="rounded-2xl border border-stone-200 bg-white p-6"><h3 className="font-bold text-stone-950">{index + 1}. {module.title}</h3>{module.lessons?.length ? <ul className="mt-4 space-y-2 text-sm text-stone-600">{module.lessons.map((lesson) => <li key={lesson.id} className="flex justify-between gap-4"><span>{lesson.title}</span>{lesson.duration_minutes && <span>{lesson.duration_minutes} {t("minutes")}</span>}</li>)}</ul> : null}</li>)}</ol> : <p className="mt-6 rounded-2xl bg-white p-6 text-stone-600">{t("curriculumPending")}</p>}</section>
          {course.type === "live" && <section><h2 className="text-3xl font-bold">{t("liveSchedule")}</h2><p className="mt-2 text-sm text-stone-500">{t("localTimezone")}</p>{course.live_sessions?.length ? <ul className="mt-6 grid gap-3 sm:grid-cols-2">{course.live_sessions.map((session) => <li key={session.id} className="rounded-2xl border border-stone-200 bg-white p-5 font-semibold"><LiveSessionTime startsAt={session.starts_at} locale={locale} /></li>)}</ul> : <p className="mt-5 text-stone-600">{t("schedulePending")}</p>}</section>}
          <section><h2 className="text-3xl font-bold">{t("reviewsTitle")}</h2>{course.reviews.length ? <div className="mt-6 grid gap-4 sm:grid-cols-2">{course.reviews.map((review) => <blockquote key={review.id} className="rounded-2xl bg-white p-6 shadow-sm"><p aria-label={t("rating", { rating: review.rating })} className="text-amber-600">{"★".repeat(review.rating)}</p><p className="mt-4 leading-7 text-stone-700">“{review.quote}”</p><footer className="mt-4 text-sm font-bold text-stone-950">{review.name}</footer></blockquote>)}</div> : <p className="mt-5 rounded-2xl bg-white p-6 text-stone-600">{t("noReviews")}</p>}</section>
        </div>
        <aside id="enroll" className="scroll-mt-28 lg:sticky lg:top-28 lg:self-start"><EnrollmentForm course={course} upiId={settings.upi_id ?? null} upiQrUrl={settings.upi_qr_url ?? null} /></aside>
      </div>
    </main>
  );
}
