import { getTranslations } from "next-intl/server";
import { Link } from "@/i18n/navigation";
import type { Settings } from "@/lib/api";
import { CurrencySwitcher } from "./currency-switcher";
import { LanguageSwitcher } from "./language-switcher";

export async function SiteHeader({ settings }: { settings: Settings }) {
  const t = await getTranslations("Navigation");

  const links = [
    { href: "/", label: t("home") },
    { href: "/astrologers", label: t("astrologers") },
    { href: "/birth-chart", label: t("birthChart") },
    { href: "/courses", label: t("courses") },
    { href: "/blog", label: t("blog") },
    { href: "/contact", label: t("contact") },
    { href: "/account", label: t("account") },
  ] as const;

  return (
    <header className="sticky top-0 z-50 border-b border-amber-950/10 bg-amber-50/95 backdrop-blur">
      <div className="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
        <Link
          href="/"
          className="flex items-center gap-3 text-lg font-bold tracking-tight text-amber-950"
        >
          {settings.logo_url ? (
            // Settings controls the tenant logo URL, which may be served by Laravel.
            // eslint-disable-next-line @next/next/no-img-element
            <img
              src={settings.logo_url}
              alt=""
              width={40}
              height={40}
              className="size-10 rounded-full object-contain"
            />
          ) : (
            <span
              aria-hidden="true"
              className="grid size-10 place-items-center rounded-full bg-amber-700 text-xl text-white"
            >
              ✦
            </span>
          )}
          <span>{settings.site_name}</span>
        </Link>

        <div className="order-2 flex items-center gap-2 sm:gap-3 lg:order-3">
          <LanguageSwitcher label={t("language")} />
          <CurrencySwitcher label={t("currency")} />
        </div>

        <nav
          aria-label={t("primaryNavigation")}
          className="order-3 w-full lg:order-2 lg:w-auto"
        >
          <ul className="flex gap-x-5 gap-y-2 overflow-x-auto pb-1 text-sm font-semibold text-stone-700 lg:flex-wrap lg:justify-center lg:pb-0">
            {links.map((link) => (
              <li key={link.href}>
                <Link
                  href={link.href}
                  className="whitespace-nowrap transition-colors hover:text-amber-800 focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-amber-700"
                >
                  {link.label}
                </Link>
              </li>
            ))}
          </ul>
        </nav>
      </div>
    </header>
  );
}
