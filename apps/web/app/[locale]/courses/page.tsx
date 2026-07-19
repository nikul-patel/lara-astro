import type { Metadata } from "next";
import { hasLocale } from "next-intl";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { notFound } from "next/navigation";
import { CoursePrice } from "@/components/courses/course-price";
import { getLocalizedAlternates } from "@/i18n/metadata";
import { Link } from "@/i18n/navigation";
import { routing, type AppLocale } from "@/i18n/routing";
import { getCourses } from "@/lib/course-data";
import type { CourseType } from "@/lib/api";

export const revalidate = 3600;

type CoursesPageProps = {
  params: Promise<{ locale: string }>;
  searchParams: Promise<{ type?: string }>;
};

async function getLocale(params: CoursesPageProps["params"]): Promise<AppLocale> {
  const { locale } = await params;
  if (!hasLocale(routing.locales, locale)) notFound();
  return locale;
}

export async function generateMetadata({ params }: CoursesPageProps): Promise<Metadata> {
  const locale = await getLocale(params);
  const t = await getTranslations({ locale, namespace: "CoursesPage" });
  return { title: t("metaTitle"), description: t("metaDescription"), alternates: getLocalizedAlternates(locale, "courses") };
}

export default async function CoursesPage({ params, searchParams }: CoursesPageProps) {
  const locale = await getLocale(params);
  setRequestLocale(locale);
  const [t, courses, query] = await Promise.all([getTranslations("CoursesPage"), getCourses(locale), searchParams]);
  const selectedType: CourseType | "all" = query.type === "recorded" || query.type === "live" ? query.type : "all";
  const filteredCourses = selectedType === "all" ? courses : courses.filter((course) => course.type === selectedType);

  return (
    <main className="flex-1 bg-[#fffcf7] text-stone-900">
      <section className="border-b border-amber-900/10 bg-amber-50"><div className="mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 lg:px-8"><p className="text-sm font-bold uppercase tracking-[0.22em] text-amber-700">{t("eyebrow")}</p><h1 className="mx-auto mt-4 max-w-4xl text-4xl font-bold tracking-tight text-amber-950 sm:text-5xl">{t("title")}</h1><p className="mx-auto mt-5 max-w-2xl text-lg leading-8 text-stone-600">{t("description")}</p></div></section>
      <section className="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
        <nav aria-label={t("filterLabel")} className="flex flex-wrap gap-3">
          {(["all", "recorded", "live"] as const).map((type) => <Link key={type} href={type === "all" ? "/courses" : `/courses?type=${type}`} aria-current={selectedType === type ? "page" : undefined} className={`rounded-full px-5 py-2 text-sm font-bold ${selectedType === type ? "bg-amber-800 text-white" : "border border-stone-300 bg-white text-stone-700 hover:border-amber-600"}`}>{t(`filters.${type}`)}</Link>)}
        </nav>
        {filteredCourses.length ? <div className="mt-10 grid gap-7 md:grid-cols-2">{filteredCourses.map((course) => <article key={course.id} className="flex h-full flex-col rounded-[2rem] border border-stone-200 bg-white p-7 shadow-sm"><div className="flex items-center justify-between gap-4"><span className="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold uppercase tracking-wider text-amber-900">{t(`types.${course.type}`)}</span><span className="text-xl font-bold text-amber-800"><CoursePrice inr={course.price_inr} usd={course.price_usd} /></span></div><h2 className="mt-6 text-2xl font-bold text-stone-950">{course.title}</h2><p className="mt-3 flex-1 text-sm leading-6 text-stone-600">{course.description}</p>{course.instructor && <p className="mt-5 text-sm font-semibold text-stone-700">{t("instructor", { name: course.instructor.name })}</p>}<Link href={`/courses/${course.slug}`} className="mt-7 inline-flex w-fit items-center rounded-full bg-stone-950 px-5 py-3 text-sm font-bold text-white hover:bg-amber-900">{t("viewCourse")} →</Link></article>)}</div> : <p className="mt-10 rounded-2xl bg-white p-7 text-stone-600">{t("empty")}</p>}
      </section>
    </main>
  );
}
