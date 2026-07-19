import type { Metadata } from "next";
import { hasLocale } from "next-intl";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { notFound } from "next/navigation";
import { ContactForm } from "@/components/contact/contact-form";
import { ContentBody } from "@/components/content/content-body";
import { getLocalizedAlternates } from "@/i18n/metadata";
import { routing, type AppLocale } from "@/i18n/routing";
import { getCmsPage } from "@/lib/content-data";
import { getSiteSettings } from "@/lib/site-settings";

export const revalidate = 3600;
type ContactPageProps = { params: Promise<{ locale: string }> };

async function getLocale(params: ContactPageProps["params"]): Promise<AppLocale> {
  const { locale } = await params;
  if (!hasLocale(routing.locales, locale)) notFound();
  return locale;
}

function toWhatsappUrl(whatsappUrl?: string, phone?: string): string | undefined {
  if (whatsappUrl) return whatsappUrl;
  const digits = phone?.replace(/[^\d]/g, "");
  return digits && digits.length >= 8 ? `https://wa.me/${digits}` : undefined;
}

export async function generateMetadata({ params }: ContactPageProps): Promise<Metadata> {
  const locale = await getLocale(params);
  const page = await getCmsPage(locale, "contact");
  if (!page) notFound();
  return {
    title: page.meta_title || page.title,
    description: page.meta_description,
    alternates: getLocalizedAlternates(locale, "contact"),
    openGraph: { title: page.meta_title || page.title, description: page.meta_description, type: "website" },
  };
}

export default async function ContactPage({ params }: ContactPageProps) {
  const locale = await getLocale(params);
  setRequestLocale(locale);
  const [t, page, settings] = await Promise.all([
    getTranslations("Contact"),
    getCmsPage(locale, "contact"),
    getSiteSettings(),
  ]);
  if (!page) notFound();

  const contact = settings.contact ?? {};
  const whatsappUrl = toWhatsappUrl(contact.whatsapp_url, contact.phone);
  const mapQuery = contact.address ? encodeURIComponent(contact.address) : null;
  const mapEmbedUrl = mapQuery ? `https://www.google.com/maps?q=${mapQuery}&output=embed` : null;
  const mapLinkUrl = mapQuery ? `https://www.google.com/maps/search/?api=1&query=${mapQuery}` : null;

  return (
    <main id="main-content" tabIndex={-1} className="flex-1 bg-[#fffcf7] text-stone-900">
      <header className="border-b border-amber-900/10 bg-amber-50">
        <div className="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 lg:py-24">
          <h1 className="text-4xl font-bold tracking-tight text-amber-950 sm:text-5xl">{page.title}</h1>
          {page.meta_description && <p className="mx-auto mt-5 max-w-2xl text-lg leading-8 text-stone-600">{page.meta_description}</p>}
        </div>
      </header>

      <div className="mx-auto grid max-w-6xl gap-10 px-4 py-14 sm:px-6 lg:grid-cols-2 lg:py-20">
        <section aria-labelledby="contact-details-heading" className="space-y-8">
          <h2 id="contact-details-heading" className="sr-only">{t("detailsHeading")}</h2>
          <ContentBody content={page.content} />

          <dl className="space-y-5 rounded-[2rem] border border-stone-200 bg-white p-7 shadow-sm">
            {contact.email && (
              <div>
                <dt className="text-xs font-bold uppercase tracking-wider text-stone-500">{t("emailLabel")}</dt>
                <dd className="mt-1"><a className="font-semibold text-amber-800 hover:text-amber-950 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-700" href={`mailto:${contact.email}`}>{contact.email}</a></dd>
              </div>
            )}
            {contact.phone && (
              <div>
                <dt className="text-xs font-bold uppercase tracking-wider text-stone-500">{t("phoneLabel")}</dt>
                <dd className="mt-1"><a className="font-semibold text-amber-800 hover:text-amber-950 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-700" href={`tel:${contact.phone.replace(/\s+/g, "")}`}>{contact.phone}</a></dd>
              </div>
            )}
            {contact.address && (
              <div>
                <dt className="text-xs font-bold uppercase tracking-wider text-stone-500">{t("addressLabel")}</dt>
                <dd className="mt-1 not-italic text-stone-700">{contact.address}</dd>
              </div>
            )}
          </dl>

          {whatsappUrl && (
            <a
              href={whatsappUrl}
              target="_blank"
              rel="noreferrer"
              className="inline-flex items-center gap-3 rounded-full bg-emerald-600 px-6 py-3.5 text-sm font-bold text-white transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
            >
              <span aria-hidden="true">💬</span>
              {t("whatsapp")}
            </a>
          )}

          {mapEmbedUrl && (
            <div>
              <h2 className="text-sm font-bold uppercase tracking-wider text-stone-500">{t("mapHeading")}</h2>
              <div className="mt-3 overflow-hidden rounded-[2rem] border border-stone-200 shadow-sm">
                <iframe
                  title={t("mapTitle")}
                  src={mapEmbedUrl}
                  loading="lazy"
                  referrerPolicy="no-referrer-when-downgrade"
                  className="aspect-[4/3] w-full border-0"
                />
              </div>
              {mapLinkUrl && (
                <a href={mapLinkUrl} target="_blank" rel="noreferrer" className="mt-3 inline-flex text-sm font-bold text-amber-800 hover:text-amber-950 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-700">
                  {t("mapLink")} →
                </a>
              )}
            </div>
          )}
        </section>

        <section aria-labelledby="contact-form-heading">
          <h2 id="contact-form-heading" className="sr-only">{t("formTitle")}</h2>
          <ContactForm toEmail={contact.email} whatsappUrl={whatsappUrl} />
        </section>
      </div>
    </main>
  );
}
