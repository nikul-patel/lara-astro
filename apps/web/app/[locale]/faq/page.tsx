import type { Metadata } from "next";
import { hasLocale } from "next-intl";
import { setRequestLocale } from "next-intl/server";
import { notFound } from "next/navigation";
import { FaqAccordion, parseFaqContent } from "@/components/content/faq-accordion";
import { getLocalizedAlternates } from "@/i18n/metadata";
import { routing, type AppLocale } from "@/i18n/routing";
import { getCmsPage } from "@/lib/content-data";

export const revalidate = 3600;
type FaqPageProps = { params: Promise<{ locale: string }> };

async function getLocale(params: FaqPageProps["params"]): Promise<AppLocale> {
  const { locale } = await params;
  if (!hasLocale(routing.locales, locale)) notFound();
  return locale;
}

export async function generateMetadata({ params }: FaqPageProps): Promise<Metadata> {
  const locale = await getLocale(params);
  const page = await getCmsPage(locale, "faq");
  if (!page) notFound();
  return {
    title: page.meta_title || page.title,
    description: page.meta_description,
    alternates: getLocalizedAlternates(locale, "faq"),
    openGraph: { title: page.meta_title || page.title, description: page.meta_description, type: "website" },
  };
}

export default async function FaqPage({ params }: FaqPageProps) {
  const locale = await getLocale(params);
  setRequestLocale(locale);
  const page = await getCmsPage(locale, "faq");
  if (!page) notFound();

  const { items } = parseFaqContent(page.content);
  const faqJsonLd = items.length
    ? {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        mainEntity: items.map((item) => ({
          "@type": "Question",
          name: item.question,
          acceptedAnswer: { "@type": "Answer", text: item.answer },
        })),
      }
    : null;

  return (
    <main id="main-content" tabIndex={-1} className="flex-1 bg-[#fffcf7] text-stone-900">
      {faqJsonLd && (
        <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(faqJsonLd).replace(/</g, "\\u003c") }} />
      )}
      <header className="border-b border-amber-900/10 bg-amber-50">
        <div className="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 lg:py-24">
          <h1 className="text-4xl font-bold tracking-tight text-amber-950 sm:text-5xl">{page.title}</h1>
          {page.meta_description && <p className="mx-auto mt-5 max-w-2xl text-lg leading-8 text-stone-600">{page.meta_description}</p>}
        </div>
      </header>
      <section className="mx-auto max-w-3xl px-4 py-14 sm:px-6 lg:py-20">
        <FaqAccordion content={page.content} />
      </section>
    </main>
  );
}
