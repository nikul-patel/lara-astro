import { getTranslations } from "next-intl/server";
import { Link } from "@/i18n/navigation";
import type { Settings, SocialLink } from "@/lib/api";

function normalizeSocialLinks(
  socialLinks: Settings["social_links"],
): SocialLink[] {
  if (Array.isArray(socialLinks)) {
    return socialLinks;
  }

  return Object.entries(socialLinks ?? {}).map(([label, url]) => ({
    label,
    url,
  }));
}

export async function SiteFooter({ settings }: { settings: Settings }) {
  const t = await getTranslations("Footer");
  const socialLinks = normalizeSocialLinks(settings.social_links);
  const legalLinks =
    settings.legal_links?.length
      ? settings.legal_links
      : [
          { label: t("privacy"), slug: "privacy-policy" },
          { label: t("terms"), slug: "terms-and-conditions" },
          { label: t("refund"), slug: "refund-cancellation-policy" },
        ];

  return (
    <footer className="mt-auto border-t border-stone-200 bg-stone-950 text-stone-300">
      <div className="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-2 lg:grid-cols-4 lg:px-8">
        <div>
          <h2 className="text-xl font-bold text-white">{settings.site_name}</h2>
          <p className="mt-3 max-w-sm text-sm leading-6 text-stone-400">
            {t("description")}
          </p>
        </div>

        <div>
          <h2 className="text-sm font-bold uppercase tracking-wider text-amber-400">
            {t("contact")}
          </h2>
          <address className="mt-4 space-y-2 text-sm not-italic">
            {settings.contact?.email && (
              <p>
                <a className="hover:text-white" href={`mailto:${settings.contact.email}`}>
                  {settings.contact.email}
                </a>
              </p>
            )}
            {settings.contact?.phone && (
              <p>
                <a className="hover:text-white" href={`tel:${settings.contact.phone}`}>
                  {settings.contact.phone}
                </a>
              </p>
            )}
            {settings.contact?.address && <p>{settings.contact.address}</p>}
          </address>
          {socialLinks.length > 0 && (
            <ul className="mt-5 flex flex-wrap gap-4 text-sm font-semibold">
              {socialLinks.map((link) => (
                <li key={`${link.label}-${link.url}`}>
                  <a
                    href={link.url}
                    target="_blank"
                    rel="noreferrer"
                    className="text-amber-300 hover:text-amber-100"
                  >
                    {link.label}
                  </a>
                </li>
              ))}
            </ul>
          )}
        </div>

        <nav aria-label={t("help")}>
          <h2 className="text-sm font-bold uppercase tracking-wider text-amber-400">
            {t("help")}
          </h2>
          <ul className="mt-4 space-y-2 text-sm">
            <li>
              <Link href="/faq" className="rounded hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-400">
                {t("faq")}
              </Link>
            </li>
            <li>
              <Link href="/contact" className="rounded hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-400">
                {t("contactPage")}
              </Link>
            </li>
          </ul>
        </nav>

        <nav aria-label={t("legal")}>
          <h2 className="text-sm font-bold uppercase tracking-wider text-amber-400">
            {t("legal")}
          </h2>
          <ul className="mt-4 space-y-2 text-sm">
            {legalLinks.map((link) => (
              <li key={link.slug}>
                <Link href={`/${link.slug}`} className="rounded hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-400">
                  {link.label}
                </Link>
              </li>
            ))}
          </ul>
        </nav>
      </div>

      <div className="border-t border-stone-800 px-4 py-5 text-center text-xs text-stone-500">
        {t("copyright", {
          year: new Date().getFullYear(),
          siteName: settings.site_name,
        })}
      </div>
    </footer>
  );
}
