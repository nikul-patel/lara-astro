import type { Metadata } from "next";
import { hasLocale } from "next-intl";
import { setRequestLocale } from "next-intl/server";
import { notFound } from "next/navigation";
import { ContentBody } from "@/components/content/content-body";
import { getLocalizedAlternates } from "@/i18n/metadata";
import { routing, type AppLocale } from "@/i18n/routing";
import { cmsPageSlugs, getCmsPage } from "@/lib/content-data";

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
  return <main className="flex-1 bg-[#fffcf7] text-stone-900"><header className="border-b border-amber-900/10 bg-amber-50"><div className="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 lg:py-24"><h1 className="text-4xl font-bold tracking-tight text-amber-950 sm:text-5xl">{page.title}</h1>{page.meta_description && <p className="mx-auto mt-5 max-w-2xl text-lg leading-8 text-stone-600">{page.meta_description}</p>}</div></header><article className="mx-auto max-w-3xl px-4 py-14 sm:px-6 lg:py-20"><ContentBody content={page.content} /></article></main>;
}
