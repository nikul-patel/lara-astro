import type { Metadata } from "next";
import { Geist, Geist_Mono } from "next/font/google";
import { hasLocale, NextIntlClientProvider } from "next-intl";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { notFound } from "next/navigation";
import { getSiteUrl } from "@/i18n/metadata";
import { routing } from "@/i18n/routing";
import { CurrencyProvider } from "@/components/site/currency-provider";
import { SiteFooter } from "@/components/site/site-footer";
import { SiteHeader } from "@/components/site/site-header";
import { getSiteSettings } from "@/lib/site-settings";
import "../globals.css";

const geistSans = Geist({
  variable: "--font-geist-sans",
  subsets: ["latin"],
});

const geistMono = Geist_Mono({
  variable: "--font-geist-mono",
  subsets: ["latin"],
});

export function generateStaticParams() {
  return routing.locales.map((locale) => ({ locale }));
}

export const dynamicParams = false;

type LocaleLayoutProps = {
  children: React.ReactNode;
  params: Promise<{ locale: string }>;
};

export async function generateMetadata({ params }: Pick<LocaleLayoutProps, "params">): Promise<Metadata> {
  const { locale } = await params;
  if (!hasLocale(routing.locales, locale)) notFound();
  const settings = await getSiteSettings();
  return {
    metadataBase: getSiteUrl(),
    title: { default: settings.seo?.default_meta_title || settings.site_name, template: `%s | ${settings.site_name}` },
    description: settings.seo?.default_meta_description,
    verification: settings.seo?.search_console_verification ? { google: settings.seo.search_console_verification } : undefined,
  };
}

export default async function LocaleLayout({
  children,
  params,
}: LocaleLayoutProps) {
  const { locale } = await params;

  if (!hasLocale(routing.locales, locale)) {
    notFound();
  }

  setRequestLocale(locale);
  const [settings, tNavigation] = await Promise.all([
    getSiteSettings(),
    getTranslations("Navigation"),
  ]);
  const businessJsonLd = {
    "@context": "https://schema.org",
    "@type": settings.seo?.schema_business_type || "LocalBusiness",
    name: settings.seo?.schema_business_name || settings.site_name,
    url: new URL(`/${locale}`, getSiteUrl()).toString(),
    logo: settings.logo_url || undefined,
    email: settings.contact?.email,
    telephone: settings.contact?.phone,
    address: settings.contact?.address ? { "@type": "PostalAddress", streetAddress: settings.contact.address } : undefined,
  };

  return (
    <html
      lang={locale}
      className={`${geistSans.variable} ${geistMono.variable} h-full antialiased`}
    >
      <body className="flex min-h-full flex-col">
        <a
          href="#main-content"
          className="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-amber-800 focus:px-4 focus:py-2 focus:text-sm focus:font-bold focus:text-white focus:outline-2 focus:outline-offset-2 focus:outline-amber-950"
        >
          {tNavigation("skipToContent")}
        </a>
        <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(businessJsonLd).replace(/</g, "\\u003c") }} />
        <NextIntlClientProvider>
          <CurrencyProvider defaultCurrency={settings.default_currency}>
            <SiteHeader settings={settings} />
            {children}
            <SiteFooter settings={settings} />
          </CurrencyProvider>
        </NextIntlClientProvider>
      </body>
    </html>
  );
}
