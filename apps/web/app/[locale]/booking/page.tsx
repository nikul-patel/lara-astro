import type { Metadata } from "next";
import { hasLocale } from "next-intl";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { notFound } from "next/navigation";
import { BookingFlow } from "@/components/booking/booking-flow";
import { getLocalizedAlternates } from "@/i18n/metadata";
import { routing, type AppLocale } from "@/i18n/routing";
import { getBookingData } from "@/lib/booking-data";

type BookingPageProps = {
  params: Promise<{ locale: string }>;
  searchParams: Promise<{ astrologer?: string; service?: string }>;
};

async function getLocale(params: BookingPageProps["params"]): Promise<AppLocale> {
  const { locale } = await params;

  if (!hasLocale(routing.locales, locale)) {
    notFound();
  }

  return locale;
}

export async function generateMetadata({
  params,
}: BookingPageProps): Promise<Metadata> {
  const locale = await getLocale(params);
  const t = await getTranslations({ locale, namespace: "Booking" });

  return {
    title: t("metaTitle"),
    description: t("metaDescription"),
    alternates: getLocalizedAlternates(locale, "booking"),
  };
}

export default async function BookingPage({
  params,
  searchParams,
}: BookingPageProps) {
  const locale = await getLocale(params);
  setRequestLocale(locale);
  const [t, data, selection] = await Promise.all([
    getTranslations("Booking"),
    getBookingData(locale),
    searchParams,
  ]);
  const initialAstrologer = data.astrologers.find(
    (item) => item.slug === selection.astrologer,
  );
  const initialService = data.services.find(
    (item) =>
      item.astrologer_id === initialAstrologer?.id &&
      item.slug === selection.service,
  );

  return (
    <main id="main-content" tabIndex={-1} className="flex-1 bg-[#fffcf7] text-stone-900">
      <section className="border-b border-amber-900/10 bg-amber-50">
        <div className="mx-auto max-w-7xl px-4 py-14 text-center sm:px-6 sm:py-18 lg:px-8">
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

      <section className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-16">
        <BookingFlow
          {...data}
          initialAstrologerId={initialAstrologer?.id}
          initialServiceId={initialService?.id}
        />
      </section>
    </main>
  );
}
