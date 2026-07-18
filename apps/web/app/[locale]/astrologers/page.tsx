import type { Metadata } from "next";
import { hasLocale } from "next-intl";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { notFound } from "next/navigation";
import { AstrologerPhoto } from "@/components/astrologers/astrologer-photo";
import { getLocalizedAlternates } from "@/i18n/metadata";
import { Link } from "@/i18n/navigation";
import { routing, type AppLocale } from "@/i18n/routing";
import { getAstrologers } from "@/lib/astrologer-data";

export const revalidate = 3600;

type AstrologersPageProps = {
  params: Promise<{ locale: string }>;
};

async function getLocale(
  params: AstrologersPageProps["params"],
): Promise<AppLocale> {
  const { locale } = await params;

  if (!hasLocale(routing.locales, locale)) {
    notFound();
  }

  return locale;
}

export async function generateMetadata({
  params,
}: AstrologersPageProps): Promise<Metadata> {
  const locale = await getLocale(params);
  const t = await getTranslations({ locale, namespace: "AstrologersPage" });

  return {
    title: t("metaTitle"),
    description: t("metaDescription"),
    alternates: getLocalizedAlternates(locale, "astrologers"),
  };
}

export default async function AstrologersPage({
  params,
}: AstrologersPageProps) {
  const locale = await getLocale(params);
  setRequestLocale(locale);

  const [t, astrologers] = await Promise.all([
    getTranslations("AstrologersPage"),
    getAstrologers(locale),
  ]);

  return (
    <main className="flex-1 bg-[#fffcf7] text-stone-900">
      <section className="border-b border-amber-900/10 bg-amber-50">
        <div className="mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 sm:py-20 lg:px-8">
          <p className="text-sm font-bold uppercase tracking-[0.22em] text-amber-700">
            {t("eyebrow")}
          </p>
          <h1 className="mx-auto mt-4 max-w-4xl text-4xl font-bold tracking-tight text-amber-950 sm:text-5xl">
            {t("title")}
          </h1>
          <p className="mx-auto mt-5 max-w-2xl text-lg leading-8 text-stone-600">
            {t("description")}
          </p>
        </div>
      </section>

      <section className="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
        <div className="grid gap-8 lg:grid-cols-2">
          {astrologers.map((astrologer) => (
            <article
              key={astrologer.id}
              className="overflow-hidden rounded-[2rem] border border-stone-200 bg-white shadow-sm"
            >
              <div className="grid sm:grid-cols-[13rem_1fr]">
                <div className="grid min-h-72 place-items-center bg-amber-100 sm:min-h-full">
                  <AstrologerPhoto
                    astrologer={astrologer}
                    alt={t("photoAlt", { name: astrologer.name })}
                  />
                </div>
                <div className="flex flex-col p-7">
                  <h2 className="text-2xl font-bold text-stone-950">
                    {astrologer.name}
                  </h2>
                  {astrologer.experience_years && (
                    <p className="mt-2 text-sm font-bold text-amber-700">
                      {t("yearsExperience", {
                        years: astrologer.experience_years,
                      })}
                    </p>
                  )}
                  <p className="mt-4 line-clamp-4 text-sm leading-6 text-stone-600">
                    {astrologer.bio}
                  </p>
                  <ul className="mt-5 flex flex-wrap gap-2">
                    {(astrologer.specialties ?? []).map((specialty) => (
                      <li
                        key={specialty}
                        className="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800"
                      >
                        {specialty}
                      </li>
                    ))}
                  </ul>
                  <p className="mt-5 text-xs font-semibold text-stone-500">
                    {(astrologer.languages ?? []).join(" · ")}
                  </p>
                  <Link
                    href={`/astrologers/${astrologer.slug}`}
                    className="mt-7 inline-flex w-fit items-center text-sm font-bold text-amber-800 hover:text-amber-950"
                  >
                    {t("viewProfile")} →
                  </Link>
                </div>
              </div>
            </article>
          ))}
        </div>
      </section>
    </main>
  );
}
