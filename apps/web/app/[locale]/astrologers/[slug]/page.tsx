import type { Metadata } from "next";
import { hasLocale } from "next-intl";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { notFound } from "next/navigation";
import { AstrologerPhoto } from "@/components/astrologers/astrologer-photo";
import { ProfileServiceCard } from "@/components/astrologers/profile-service-card";
import { getLocalizedAlternates } from "@/i18n/metadata";
import { Link } from "@/i18n/navigation";
import { routing, type AppLocale } from "@/i18n/routing";
import {
  demoAstrologerSlugs,
  getAstrologerProfile,
} from "@/lib/astrologer-data";

export const revalidate = 3600;

type AstrologerProfilePageProps = {
  params: Promise<{ locale: string; slug: string }>;
};

export function generateStaticParams() {
  return demoAstrologerSlugs.map((slug) => ({ slug }));
}

async function getRouteParams(params: AstrologerProfilePageProps["params"]) {
  const { locale, slug } = await params;

  if (!hasLocale(routing.locales, locale)) {
    notFound();
  }

  return { locale, slug } as { locale: AppLocale; slug: string };
}

export async function generateMetadata({
  params,
}: AstrologerProfilePageProps): Promise<Metadata> {
  const { locale, slug } = await getRouteParams(params);
  const astrologer = await getAstrologerProfile(locale, slug);

  if (!astrologer) {
    notFound();
  }

  return {
    title: astrologer.name,
    description: astrologer.bio ?? undefined,
    alternates: getLocalizedAlternates(locale, `astrologers/${slug}`),
  };
}

export default async function AstrologerProfilePage({
  params,
}: AstrologerProfilePageProps) {
  const { locale, slug } = await getRouteParams(params);
  setRequestLocale(locale);

  const [t, astrologer] = await Promise.all([
    getTranslations("AstrologerProfile"),
    getAstrologerProfile(locale, slug),
  ]);

  if (!astrologer) {
    notFound();
  }

  return (
    <main className="flex-1 bg-[#fffcf7] text-stone-900">
      <section className="border-b border-amber-900/10 bg-amber-50">
        <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
          <Link
            href="/astrologers"
            className="text-sm font-bold text-amber-800 hover:text-amber-950"
          >
            ← {t("allAstrologers")}
          </Link>

          <div className="mt-8 grid items-center gap-10 pb-10 lg:grid-cols-[0.75fr_1.25fr] lg:pb-16">
            <div className="mx-auto grid aspect-[4/5] w-full max-w-md place-items-center overflow-hidden rounded-[2.5rem] bg-amber-100 shadow-xl shadow-amber-950/10">
              <AstrologerPhoto
                astrologer={astrologer}
                alt={t("photoAlt", { name: astrologer.name })}
              />
            </div>

            <div>
              <p className="text-sm font-bold uppercase tracking-[0.22em] text-amber-700">
                {t("eyebrow")}
              </p>
              <h1 className="mt-4 text-4xl font-bold tracking-tight text-amber-950 sm:text-5xl lg:text-6xl">
                {astrologer.name}
              </h1>
              <ul className="mt-6 flex flex-wrap gap-2">
                {(astrologer.specialties ?? []).map((specialty) => (
                  <li
                    key={specialty}
                    className="rounded-full border border-amber-800/15 bg-white/70 px-4 py-2 text-sm font-semibold text-amber-900"
                  >
                    {specialty}
                  </li>
                ))}
              </ul>
              <div className="mt-8 grid max-w-xl gap-4 sm:grid-cols-2">
                {astrologer.experience_years && (
                  <div className="rounded-2xl bg-white p-5 shadow-sm">
                    <p className="text-2xl font-bold text-amber-800">
                      {astrologer.experience_years}+
                    </p>
                    <p className="mt-1 text-xs font-semibold uppercase tracking-wider text-stone-500">
                      {t("yearsExperience")}
                    </p>
                  </div>
                )}
                <div className="rounded-2xl bg-white p-5 shadow-sm">
                  <p className="font-bold text-amber-900">
                    {(astrologer.languages ?? []).join(" · ")}
                  </p>
                  <p className="mt-1 text-xs font-semibold uppercase tracking-wider text-stone-500">
                    {t("languages")}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section className="mx-auto grid max-w-7xl gap-12 px-4 py-16 sm:px-6 lg:grid-cols-[0.7fr_1.3fr] lg:px-8 lg:py-24">
        <div>
          <p className="text-sm font-bold uppercase tracking-[0.2em] text-amber-700">
            {t("aboutEyebrow")}
          </p>
          <h2 className="mt-3 text-3xl font-bold tracking-tight text-stone-950">
            {t("aboutTitle")}
          </h2>
        </div>
        <p className="text-lg leading-8 text-stone-600">{astrologer.bio}</p>
      </section>

      <section className="bg-stone-950 py-16 text-white lg:py-24">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div className="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
            <div>
              <p className="text-sm font-bold uppercase tracking-[0.2em] text-amber-400">
                {t("servicesEyebrow")}
              </p>
              <h2 className="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">
                {t("servicesTitle")}
              </h2>
            </div>
            <p className="max-w-xl text-sm leading-6 text-stone-400">
              {t("servicesDescription", { name: astrologer.name })}
            </p>
          </div>

          {astrologer.services.length > 0 ? (
            <div className="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
              {astrologer.services.map((service) => (
                <ProfileServiceCard
                  key={service.id}
                  astrologerSlug={astrologer.slug}
                  service={service}
                  bookLabel={t("bookService")}
                  durationLabel={t("minutes")}
                  fromLabel={t("from")}
                />
              ))}
            </div>
          ) : (
            <p className="mt-10 rounded-2xl border border-stone-800 p-6 text-stone-400">
              {t("noServices")}
            </p>
          )}
        </div>
      </section>
    </main>
  );
}
