import type { Metadata } from "next";
import { hasLocale } from "next-intl";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { notFound } from "next/navigation";
import { getLocalizedAlternates } from "@/i18n/metadata";
import { routing, type AppLocale } from "@/i18n/routing";

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
  const t = await getTranslations("HomePage");

  return (
    <main className="flex flex-1 items-center justify-center bg-zinc-50 px-6 py-24 dark:bg-black">
      <section className="w-full max-w-3xl rounded-3xl bg-white p-10 shadow-sm dark:bg-zinc-950 sm:p-16">
        <p className="mb-4 text-sm font-semibold uppercase tracking-[0.2em] text-amber-700 dark:text-amber-400">
          {t("eyebrow")}
        </p>
        <h1 className="text-4xl font-semibold tracking-tight text-zinc-950 dark:text-zinc-50 sm:text-5xl">
          {t("title")}
        </h1>
        <p className="mt-6 max-w-2xl text-lg leading-8 text-zinc-600 dark:text-zinc-400">
          {t("description")}
        </p>
      </section>
    </main>
  );
}
