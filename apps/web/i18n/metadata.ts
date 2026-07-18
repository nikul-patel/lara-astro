import type { Metadata } from "next";
import type { AppLocale } from "./routing";
import { routing } from "./routing";

const DEFAULT_SITE_URL = "http://localhost:3000";

export function getSiteUrl(): URL {
  return new URL(
    process.env.NEXT_PUBLIC_SITE_URL?.replace(/\/$/, "") ?? DEFAULT_SITE_URL,
  );
}

export function getLocalizedAlternates(
  locale: AppLocale,
  pathname = "",
): NonNullable<Metadata["alternates"]> {
  const normalizedPath = pathname ? `/${pathname.replace(/^\//, "")}` : "";
  const languages = Object.fromEntries(
    routing.locales.map((supportedLocale) => [
      supportedLocale,
      `/${supportedLocale}${normalizedPath}`,
    ]),
  );

  return {
    canonical: `/${locale}${normalizedPath}`,
    languages: {
      ...languages,
      "x-default": `/${routing.defaultLocale}${normalizedPath}`,
    },
  };
}
