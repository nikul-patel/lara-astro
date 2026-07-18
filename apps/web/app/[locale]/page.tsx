import type { Metadata } from "next";
import { hasLocale } from "next-intl";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { notFound } from "next/navigation";
import { ServiceCard } from "@/components/home/service-card";
import { getLocalizedAlternates } from "@/i18n/metadata";
import { Link } from "@/i18n/navigation";
import { routing, type AppLocale } from "@/i18n/routing";
import { getHomeData } from "@/lib/home-data";

export const revalidate = 3600;

type HomePageProps = {
  params: Promise<{ locale: string }>;
};

async function getLocale(params: HomePageProps["params"]): Promise<AppLocale> {
  const { locale } = await params;

  if (!hasLocale(routing.locales, locale)) {
    notFound();
  }

  return locale;
}

export async function generateMetadata({
  params,
}: HomePageProps): Promise<Metadata> {
  const locale = await getLocale(params);
  const t = await getTranslations({ locale, namespace: "Metadata" });

  return {
    title: t("title"),
    description: t("description"),
    alternates: getLocalizedAlternates(locale),
  };
}

export default async function HomePage({ params }: HomePageProps) {
  const locale = await getLocale(params);
  setRequestLocale(locale);

  const [t, data] = await Promise.all([
    getTranslations("HomePage"),
    getHomeData(locale),
  ]);

  return (
    <main className="flex-1 bg-[#fffcf7] text-stone-900">
      <section className="relative isolate overflow-hidden border-b border-amber-900/10 bg-amber-50">
        <div
          aria-hidden="true"
          className="absolute -right-32 -top-32 size-[34rem] rounded-full bg-amber-300/25 blur-3xl"
        />
        <div
          aria-hidden="true"
          className="absolute -bottom-48 -left-32 size-[30rem] rounded-full bg-orange-200/30 blur-3xl"
        />
        <div className="relative mx-auto grid max-w-7xl items-center gap-12 px-4 py-20 sm:px-6 sm:py-28 lg:grid-cols-[1.2fr_0.8fr] lg:px-8 lg:py-32">
          <div>
            <p className="text-sm font-bold uppercase tracking-[0.24em] text-amber-700">
              {t("eyebrow")}
            </p>
            <h1 className="mt-5 max-w-4xl text-5xl font-bold leading-[1.08] tracking-tight text-amber-950 sm:text-6xl lg:text-7xl">
              {t("title")}
            </h1>
            <p className="mt-7 max-w-2xl text-lg leading-8 text-stone-600 sm:text-xl">
              {t("description")}
            </p>
            <div className="mt-9 flex flex-wrap gap-4">
              <a
                href="#services"
                className="rounded-full bg-amber-800 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-amber-900/20 transition hover:bg-amber-900 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-amber-800"
              >
                {t("primaryCta")}
              </a>
              <a
                href="#astrologers"
                className="rounded-full border border-amber-900/20 bg-white/70 px-6 py-3.5 text-sm font-bold text-amber-950 transition hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-amber-800"
              >
                {t("secondaryCta")}
              </a>
            </div>
          </div>

          <div className="relative mx-auto aspect-square w-full max-w-md">
            <div className="absolute inset-0 rounded-full border border-amber-800/15" />
            <div className="absolute inset-[12%] rounded-full border border-amber-800/20" />
            <div className="absolute inset-[24%] rotate-45 rounded-[2.5rem] border border-amber-800/25 bg-white/60 shadow-2xl shadow-amber-950/10" />
            <div className="absolute inset-0 grid place-items-center text-center">
              <div>
                <span className="text-7xl text-amber-700" aria-hidden="true">
                  ☉
                </span>
                <p className="mt-4 max-w-48 text-sm font-semibold leading-6 text-amber-950">
                  {t("heroCard")}
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section aria-label={t("trustLabel")} className="border-b border-stone-200 bg-white">
        <div className="mx-auto grid max-w-7xl grid-cols-2 gap-px bg-stone-200 md:grid-cols-4">
          {["languages", "systems", "pricing", "privacy"].map((key) => (
            <div key={key} className="bg-white px-5 py-7 text-center">
              <p className="text-2xl font-bold text-amber-800">
                {t(`trust.${key}.value`)}
              </p>
              <p className="mt-1 text-xs font-semibold uppercase tracking-wider text-stone-500">
                {t(`trust.${key}.label`)}
              </p>
            </div>
          ))}
        </div>
      </section>

      <section className="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
        <div className="mx-auto max-w-3xl text-center">
          <p className="text-sm font-bold uppercase tracking-[0.2em] text-amber-700">
            {t("value.eyebrow")}
          </p>
          <h2 className="mt-4 text-3xl font-bold tracking-tight text-stone-950 sm:text-4xl">
            {t("value.title")}
          </h2>
        </div>
        <div className="mt-12 grid gap-6 md:grid-cols-3">
          {["personal", "practical", "accessible"].map((key, index) => (
            <article key={key} className="rounded-3xl border border-stone-200 bg-white p-7">
              <span className="text-sm font-black text-amber-700">0{index + 1}</span>
              <h3 className="mt-5 text-xl font-bold text-stone-950">
                {t(`value.items.${key}.title`)}
              </h3>
              <p className="mt-3 text-sm leading-6 text-stone-600">
                {t(`value.items.${key}.description`)}
              </p>
            </article>
          ))}
        </div>
      </section>

      <section id="services" className="scroll-mt-32 bg-amber-950 py-20 text-white lg:py-28">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div className="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
            <div>
              <p className="text-sm font-bold uppercase tracking-[0.2em] text-amber-300">
                {t("services.eyebrow")}
              </p>
              <h2 className="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">
                {t("services.title")}
              </h2>
            </div>
            <p className="max-w-xl text-sm leading-6 text-amber-100/70">
              {t("services.description")}
            </p>
          </div>
          <div className="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            {data.services.map((service) => (
              <ServiceCard
                key={service.id}
                service={service}
                fromLabel={t("services.from")}
                durationLabel={t("services.minutes")}
              />
            ))}
          </div>
        </div>
      </section>

      <section id="astrologers" className="scroll-mt-32 py-20 lg:py-28">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div className="mx-auto max-w-3xl text-center">
            <p className="text-sm font-bold uppercase tracking-[0.2em] text-amber-700">
              {t("astrologers.eyebrow")}
            </p>
            <h2 className="mt-4 text-3xl font-bold tracking-tight text-stone-950 sm:text-4xl">
              {t("astrologers.title")}
            </h2>
            <p className="mt-4 text-stone-600">{t("astrologers.description")}</p>
          </div>
          <div className="mx-auto mt-12 grid max-w-5xl gap-7 md:grid-cols-2">
            {data.astrologers.map((astrologer) => (
              <article
                key={astrologer.id}
                className="flex flex-col gap-6 rounded-3xl border border-stone-200 bg-white p-7 shadow-sm sm:flex-row"
              >
                <div className="grid size-24 shrink-0 place-items-center overflow-hidden rounded-3xl bg-amber-100 text-3xl font-bold text-amber-800">
                  {astrologer.photo_url ? (
                    // Images are CMS-managed and served from the Laravel media URL.
                    // eslint-disable-next-line @next/next/no-img-element
                    <img
                      src={astrologer.photo_url}
                      alt=""
                      width={96}
                      height={96}
                      className="size-full object-cover"
                    />
                  ) : (
                    astrologer.name.charAt(0)
                  )}
                </div>
                <div>
                  <h3 className="text-xl font-bold text-stone-950">
                    <Link
                      href={`/astrologers/${astrologer.slug}`}
                      className="hover:text-amber-800"
                    >
                      {astrologer.name}
                    </Link>
                  </h3>
                  <p className="mt-2 text-sm leading-6 text-stone-600">{astrologer.bio}</p>
                  <ul className="mt-4 flex flex-wrap gap-2">
                    {(astrologer.specialties ?? []).map((specialty) => (
                      <li
                        key={specialty}
                        className="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800"
                      >
                        {specialty}
                      </li>
                    ))}
                  </ul>
                  <p className="mt-4 text-xs font-medium text-stone-500">
                    {(astrologer.languages ?? []).join(" · ")}
                  </p>
                  <Link
                    href={`/astrologers/${astrologer.slug}`}
                    className="mt-5 inline-flex text-sm font-bold text-amber-800 hover:text-amber-950"
                  >
                    {t("astrologers.viewProfile")} →
                  </Link>
                </div>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section className="bg-orange-50 py-20 lg:py-28">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div className="mx-auto max-w-3xl text-center">
            <p className="text-sm font-bold uppercase tracking-[0.2em] text-amber-700">
              {t("testimonials.eyebrow")}
            </p>
            <h2 className="mt-4 text-3xl font-bold tracking-tight text-stone-950 sm:text-4xl">
              {t("testimonials.title")}
            </h2>
          </div>
          <div className="mt-12 grid gap-6 md:grid-cols-3">
            {data.testimonials.map((testimonial) => (
              <figure key={testimonial.id} className="rounded-3xl bg-white p-7 shadow-sm">
                <div
                  className="text-sm tracking-widest text-amber-500"
                  aria-label={`${testimonial.rating ?? 5} / 5`}
                >
                  {"★".repeat(testimonial.rating ?? 5)}
                </div>
                <blockquote className="mt-5 text-base leading-7 text-stone-700">
                  “{testimonial.quote}”
                </blockquote>
                <figcaption className="mt-6 text-sm font-bold text-stone-950">
                  {testimonial.name}
                </figcaption>
              </figure>
            ))}
          </div>
        </div>
      </section>

      <section id="insights" className="scroll-mt-32 py-20 lg:py-28">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div className="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
            <div>
              <p className="text-sm font-bold uppercase tracking-[0.2em] text-amber-700">
                {t("insights.eyebrow")}
              </p>
              <h2 className="mt-3 text-3xl font-bold tracking-tight text-stone-950 sm:text-4xl">
                {t("insights.title")}
              </h2>
            </div>
            <p className="max-w-lg text-sm leading-6 text-stone-600">
              {t("insights.description")}
            </p>
          </div>
          <div className="mt-12 grid gap-6 md:grid-cols-3">
            {data.posts.map((post, index) => (
              <article
                key={post.id}
                className="overflow-hidden rounded-3xl border border-stone-200 bg-white"
              >
                <div
                  className={`h-2 ${["bg-amber-600", "bg-orange-500", "bg-rose-700"][index % 3]}`}
                />
                <div className="p-7">
                  {post.published_at && (
                    <time className="text-xs font-semibold uppercase tracking-wider text-stone-500">
                      {new Intl.DateTimeFormat(locale, {
                        day: "numeric",
                        month: "long",
                        year: "numeric",
                      }).format(new Date(`${post.published_at.slice(0, 10)}T12:00:00Z`))}
                    </time>
                  )}
                  <h3 className="mt-4 text-xl font-bold leading-7 text-stone-950">
                    {post.title}
                  </h3>
                  {post.excerpt && (
                    <p className="mt-3 text-sm leading-6 text-stone-600">
                      {post.excerpt}
                    </p>
                  )}
                </div>
              </article>
            ))}
          </div>
        </div>
      </section>

      <section className="px-4 pb-20 sm:px-6 lg:px-8 lg:pb-28">
        <div className="mx-auto flex max-w-7xl flex-col items-center justify-between gap-8 rounded-[2rem] bg-amber-700 px-7 py-12 text-center text-white sm:px-12 lg:flex-row lg:text-left">
          <div>
            <h2 className="text-3xl font-bold tracking-tight">{t("closing.title")}</h2>
            <p className="mt-3 max-w-2xl text-amber-100">{t("closing.description")}</p>
          </div>
          <a
            href="#services"
            className="shrink-0 rounded-full bg-white px-6 py-3.5 text-sm font-bold text-amber-900 transition hover:bg-amber-50"
          >
            {t("closing.cta")}
          </a>
        </div>
      </section>
    </main>
  );
}
