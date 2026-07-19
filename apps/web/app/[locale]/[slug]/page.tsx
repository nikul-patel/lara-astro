import type { Metadata } from "next";
import { hasLocale } from "next-intl";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { notFound } from "next/navigation";
import { ContactForm } from "@/components/content/contact-form";
import { ContentBody } from "@/components/content/content-body";
import { FaqBody, getFaqEntries } from "@/components/content/faq-body";
import { getLocalizedAlternates } from "@/i18n/metadata";
import { routing, type AppLocale } from "@/i18n/routing";
import { cmsPageSlugs, getCmsPage } from "@/lib/content-data";
import { getSiteSettings } from "@/lib/site-settings";

export const revalidate = 3600;
type CmsPageProps = { params: Promise<{ locale: string; slug: string }> };
export function generateStaticParams() { return cmsPageSlugs.map((slug) => ({ slug })); }
async function getRouteParams(params: CmsPageProps["params"]) { const { locale, slug } = await params; if (!hasLocale(routing.locales, locale)) notFound(); return { locale, slug } as { locale: AppLocale; slug: string }; }

export async function generateMetadata({ params }: CmsPageProps): Promise<Metadata> {
  const { locale, slug } = await getRouteParams(params);
  const page = await getCmsPage(locale, slug);
  if (!page) notFound();
  return { title: page.meta_title || page.title, description: page.meta_description, alternates: getLocalizedAlternates(locale, slug), openGraph: { title: page.meta_title || page.title, description: page.meta_description, type: "website" } };
}

export default async function CmsPage({ params }: CmsPageProps) {
  const { locale, slug } = await getRouteParams(params);
  setRequestLocale(locale);
  const page = await getCmsPage(locale, slug);
  if (!page) notFound();
  if (slug === "contact") {
    const [settings, t] = await Promise.all([getSiteSettings(), getTranslations("Contact")]);
    const contact = settings.contact;
    const whatsappUrl = safeHttpUrl(contact?.whatsapp_url) || whatsappFromPhone(contact?.phone);
    const mapUrl = contact?.address ? `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(contact.address)}` : undefined;
    const contactJsonLd = {
      "@context": "https://schema.org",
      "@type": "ContactPage",
      name: page.title,
      mainEntity: {
        "@type": settings.seo?.schema_business_type || "LocalBusiness",
        name: settings.seo?.schema_business_name || settings.site_name,
        email: contact?.email,
        telephone: contact?.phone,
        address: contact?.address,
      },
    };

    return <PageShell title={page.title} description={page.meta_description}>
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(contactJsonLd).replace(/</g, "\\u003c") }} />
      <div className="grid gap-10 lg:grid-cols-[0.8fr_1.2fr]">
        <div>
          <ContentBody content={page.content} />
          <address className="mt-8 space-y-4 rounded-3xl bg-amber-50 p-6 not-italic text-stone-700">
            {contact?.email && <p><a className="font-bold text-amber-900 underline-offset-4 hover:underline" href={`mailto:${contact.email}`}>{contact.email}</a></p>}
            {contact?.phone && <p><a className="font-bold text-amber-900 underline-offset-4 hover:underline" href={`tel:${contact.phone}`}>{contact.phone}</a></p>}
            {contact?.address && <p>{contact.address}</p>}
            <div className="flex flex-wrap gap-3 pt-2">
              {whatsappUrl && <a href={whatsappUrl} target="_blank" rel="noopener noreferrer" className="rounded-full bg-emerald-700 px-5 py-3 text-sm font-bold text-white">{t("whatsapp")} ↗</a>}
              {mapUrl && <a href={mapUrl} target="_blank" rel="noopener noreferrer" className="rounded-full border border-amber-800 px-5 py-3 text-sm font-bold text-amber-900">{t("map")} ↗</a>}
            </div>
          </address>
        </div>
        {contact?.email ? <ContactForm recipient={contact.email} /> : <p className="rounded-3xl border border-stone-200 bg-white p-8 text-stone-600">{t("emailUnavailable")}</p>}
      </div>
    </PageShell>;
  }

  if (slug === "faq") {
    const entries = getFaqEntries(page.content);
    const faqJsonLd = { "@context": "https://schema.org", "@type": "FAQPage", mainEntity: entries.map((entry) => ({ "@type": "Question", name: entry.question, acceptedAnswer: { "@type": "Answer", text: entry.answer } })) };
    return <PageShell title={page.title} description={page.meta_description}>
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(faqJsonLd).replace(/</g, "\\u003c") }} />
      {entries.length > 0 ? <FaqBody entries={entries} /> : <ContentBody content={page.content} />}
    </PageShell>;
  }
  return <main className="flex-1 bg-[#fffcf7] text-stone-900"><header className="border-b border-amber-900/10 bg-amber-50"><div className="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 lg:py-24"><h1 className="text-4xl font-bold tracking-tight text-amber-950 sm:text-5xl">{page.title}</h1>{page.meta_description && <p className="mx-auto mt-5 max-w-2xl text-lg leading-8 text-stone-600">{page.meta_description}</p>}</div></header><article className="mx-auto max-w-3xl px-4 py-14 sm:px-6 lg:py-20"><ContentBody content={page.content} /></article></main>;
}

function PageShell({ title, description, children }: { title: string; description?: string; children: React.ReactNode }) {
  return <main className="flex-1 bg-[#fffcf7] text-stone-900"><header className="border-b border-amber-900/10 bg-amber-50"><div className="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 lg:py-24"><h1 className="text-4xl font-bold tracking-tight text-amber-950 sm:text-5xl">{title}</h1>{description && <p className="mx-auto mt-5 max-w-2xl text-lg leading-8 text-stone-600">{description}</p>}</div></header><article className="mx-auto max-w-5xl px-4 py-14 sm:px-6 lg:py-20">{children}</article></main>;
}

function safeHttpUrl(value?: string): string | undefined {
  if (!value) return undefined;
  try {
    const url = new URL(value);
    return url.protocol === "https:" || url.protocol === "http:" ? url.toString() : undefined;
  } catch {
    return undefined;
  }
}

function whatsappFromPhone(phone?: string): string | undefined {
  const digits = phone?.replace(/\D/g, "");
  return digits && digits.length >= 8 ? `https://wa.me/${digits}` : undefined;
}
