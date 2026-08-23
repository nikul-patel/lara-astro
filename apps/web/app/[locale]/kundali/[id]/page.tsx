import type { Metadata } from "next";
import { hasLocale } from "next-intl";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { notFound } from "next/navigation";
import { KundaliReport } from "@/components/kundali/kundali-report";
import { getLocalizedAlternates } from "@/i18n/metadata";
import { routing, type AppLocale } from "@/i18n/routing";

type KundaliPageProps = {
  params: Promise<{ locale: string; id: string }>;
};

async function getParams(
  params: KundaliPageProps["params"],
): Promise<{ locale: AppLocale; chartId: number }> {
  const { locale, id } = await params;

  if (!hasLocale(routing.locales, locale)) {
    notFound();
  }

  const chartId = Number(id);
  if (!Number.isInteger(chartId) || chartId <= 0) {
    notFound();
  }

  return { locale, chartId };
}

export async function generateMetadata({
  params,
}: KundaliPageProps): Promise<Metadata> {
  const { locale, chartId } = await getParams(params);
  const t = await getTranslations({ locale, namespace: "Kundali" });

  return {
    title: t("metaTitle"),
    description: t("metaDescription"),
    alternates: getLocalizedAlternates(locale, `kundali/${chartId}`),
    robots: { index: false, follow: false },
  };
}

export default async function KundaliPage({ params }: KundaliPageProps) {
  const { locale, chartId } = await getParams(params);
  setRequestLocale(locale);
  const t = await getTranslations("Kundali");

  return (
    <main id="main-content" tabIndex={-1} className="flex-1 bg-[#fffcf7] text-stone-900">
      <section className="border-b border-amber-900/10 bg-amber-50">
        <div className="mx-auto max-w-7xl px-4 py-12 text-center sm:px-6 lg:px-8">
          <p className="text-sm font-bold uppercase tracking-[0.22em] text-amber-700">
            {t("eyebrow")}
          </p>
          <h1 className="mt-4 text-4xl font-bold tracking-tight text-amber-950 sm:text-5xl">
            {t("title")}
          </h1>
        </div>
      </section>

      <section className="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8 lg:py-16">
        <KundaliReport chartId={chartId} />
      </section>
    </main>
  );
}
