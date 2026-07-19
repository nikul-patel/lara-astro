import type { MetadataRoute } from "next";
import { getSiteUrl } from "@/i18n/metadata";

export default function robots(): MetadataRoute.Robots {
  return {
    rules: { userAgent: "*", allow: "/", disallow: ["/en/account", "/hi/account", "/gu/account"] },
    sitemap: new URL("/sitemap.xml", getSiteUrl()).toString(),
    host: getSiteUrl().origin,
  };
}
