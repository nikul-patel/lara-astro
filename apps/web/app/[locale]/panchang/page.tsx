import type { Metadata } from "next";
import { hasLocale } from "next-intl";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { notFound } from "next/navigation";
import { PanchangTool } from "@/components/panchang/panchang-tool";
import { getLocalizedAlternates } from "@/i18n/metadata";
import { routing, type AppLocale } from "@/i18n/routing";

type PanchangPageProps = {
  params: Promise<{ locale: string }>;
};

async function getLocale(
  params: PanchangPageProps["params"],
): Promise<AppLocale> {
  const { locale } = await params;

  if (!hasLocale(routing.locales, locale)) {
    notFound();
  }

  return locale;
}

export async function generateMetadata({
  params,
}: PanchangPageProps): Promise<Metadata> {
  const locale = await getLocale(params);
  const t = await getTranslations({ locale, namespace: "Panchang" });

  return {
    title: t("metaTitle"),
    description: t("metaDescription"),
    alternates: getLocalizedAlternates(locale, "panchang"),
  };
}

export default async function PanchangPage({ params }: PanchangPageProps) {
  const locale = await getLocale(params);
  setRequestLocale(locale);
  const t = await getTranslations("Panchang");

  return (
    <main
      id="main-content"
      tabIndex={-1}
      className="flex-1 bg-[#fffcf7] text-stone-900"
    >
      <section className="border-b border-amber-900/10 bg-amber-50">
        <div className="mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 sm:py-20 lg:px-8">
          <p className="text-sm font-bold uppercase tracking-[0.22em] text-amber-700">
            {t("eyebrow")}
          </p>
          <h1 className="mx-auto mt-4 max-w-4xl text-4xl font-bold tracking-tight text-amber-950 sm:text-5xl">
            {t("title")}
          </h1>
          <p className="mx-auto mt-5 max-w-3xl text-lg leading-8 text-stone-600">
            {t("description")}
          </p>
        </div>
      </section>

      <section className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-20">
        <PanchangTool />
      </section>
    </main>
  );
}
