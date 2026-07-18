"use client";

import { useLocale } from "next-intl";
import { usePathname, useRouter } from "@/i18n/navigation";
import { routing, type AppLocale } from "@/i18n/routing";

const localeNames: Record<AppLocale, string> = {
  en: "EN",
  hi: "हिं",
  gu: "ગુ",
};

export function LanguageSwitcher({ label }: { label: string }) {
  const activeLocale = useLocale() as AppLocale;
  const pathname = usePathname();
  const router = useRouter();

  return (
    <label className="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-stone-500">
      <span className="sr-only lg:not-sr-only">{label}</span>
      <select
        value={activeLocale}
        aria-label={label}
        onChange={(event) => {
          router.replace(pathname, {
            locale: event.target.value as AppLocale,
          });
        }}
        className="rounded-full border border-stone-300 bg-white px-3 py-2 text-sm font-bold normal-case tracking-normal text-stone-700 shadow-sm outline-none transition focus:border-amber-600 focus:ring-2 focus:ring-amber-200"
      >
        {routing.locales.map((locale) => (
          <option key={locale} value={locale}>
            {localeNames[locale]}
          </option>
        ))}
      </select>
    </label>
  );
}
