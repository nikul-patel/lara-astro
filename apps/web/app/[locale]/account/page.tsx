import type { Metadata } from "next";
import { hasLocale } from "next-intl";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { notFound } from "next/navigation";
import { AccountDashboard } from "@/components/account/account-dashboard";
import { getLocalizedAlternates } from "@/i18n/metadata";
import { routing, type AppLocale } from "@/i18n/routing";

type AccountPageProps = { params: Promise<{ locale: string }> };

async function getLocale(params: AccountPageProps["params"]): Promise<AppLocale> {
  const { locale } = await params;
  if (!hasLocale(routing.locales, locale)) notFound();
  return locale;
}

export async function generateMetadata({ params }: AccountPageProps): Promise<Metadata> {
  const locale = await getLocale(params);
  const t = await getTranslations({ locale, namespace: "Account" });
  return { title: t("metaTitle"), description: t("metaDescription"), alternates: getLocalizedAlternates(locale, "account"), robots: { index: false, follow: false } };
}

export default async function AccountPage({ params }: AccountPageProps) {
  const locale = await getLocale(params);
  setRequestLocale(locale);
  const t = await getTranslations("Account");
  return (
    <main className="flex-1 bg-[#fffcf7] text-stone-900">
      <section className="border-b border-amber-900/10 bg-amber-50"><div className="mx-auto max-w-7xl px-4 py-12 text-center sm:px-6 lg:px-8"><p className="text-sm font-bold uppercase tracking-[0.22em] text-amber-700">{t("eyebrow")}</p><h1 className="mt-4 text-4xl font-bold tracking-tight text-amber-950 sm:text-5xl">{t("title")}</h1><p className="mx-auto mt-5 max-w-2xl text-lg leading-8 text-stone-600">{t("description")}</p></div></section>
      <section className="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-16"><AccountDashboard /></section>
    </main>
  );
}
