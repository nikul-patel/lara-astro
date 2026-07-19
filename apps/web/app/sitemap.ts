import type { MetadataRoute } from "next";
import { getSiteUrl } from "@/i18n/metadata";
import { routing } from "@/i18n/routing";
import { getAstrologers } from "@/lib/astrologer-data";
import { getCourses } from "@/lib/course-data";
import { cmsPageSlugs, getPosts } from "@/lib/content-data";
import { getSiteSettings } from "@/lib/site-settings";

export const revalidate = 3600;

function entries(path: string, options: Omit<MetadataRoute.Sitemap[number], "url" | "alternates"> = {}): MetadataRoute.Sitemap {
  const cleanPath = path ? `/${path.replace(/^\//, "")}` : "";
  const languages = {
    ...Object.fromEntries(routing.locales.map((locale) => [locale, new URL(`/${locale}${cleanPath}`, getSiteUrl()).toString()])),
    "x-default": new URL(`/${routing.defaultLocale}${cleanPath}`, getSiteUrl()).toString(),
  };
  return routing.locales.map((locale) => ({ url: new URL(`/${locale}${cleanPath}`, getSiteUrl()).toString(), alternates: { languages }, ...options }));
}

async function allPostSlugs(): Promise<Array<{ slug: string; publishedAt?: string | null; image?: string | null }>> {
  const result: Array<{ slug: string; publishedAt?: string | null; image?: string | null }> = [];
  let page = 1;
  let lastPage = 1;
  do { const response = await getPosts("en", page); result.push(...response.data.map((post) => ({ slug: post.slug, publishedAt: post.published_at, image: post.featured_image_url }))); lastPage = response.meta.last_page; page += 1; } while (page <= lastPage);
  return result;
}

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const [astrologers, courses, posts, settings] = await Promise.all([getAstrologers("en"), getCourses("en"), allPostSlugs(), getSiteSettings()]);
  const pageSlugs = new Set([...cmsPageSlugs, ...(settings.legal_links?.map((link) => link.slug) ?? [])]);
  const staticPaths = ["", "astrologers", "birth-chart", "courses", "blog", ...pageSlugs];
  return [
    ...staticPaths.flatMap((path) => entries(path, { changeFrequency: path === "blog" ? "weekly" : "monthly", priority: path === "" ? 1 : 0.7 })),
    ...astrologers.flatMap((item) => entries(`astrologers/${item.slug}`, { changeFrequency: "monthly", priority: 0.8 })),
    ...courses.flatMap((item) => entries(`courses/${item.slug}`, { changeFrequency: "weekly", priority: 0.8 })),
    ...posts.flatMap((item) => entries(`blog/${item.slug}`, { lastModified: item.publishedAt || undefined, changeFrequency: "monthly", priority: 0.7, images: item.image ? [item.image] : undefined })),
  ];
}
