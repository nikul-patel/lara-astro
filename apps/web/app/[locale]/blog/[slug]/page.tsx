import type { Metadata } from "next";
/* eslint-disable @next/next/no-img-element -- CMS media hosts vary by tenant at runtime. */
import { hasLocale } from "next-intl";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { notFound } from "next/navigation";
import { ContentBody } from "@/components/content/content-body";
import { getLocalizedAlternates, getSiteUrl } from "@/i18n/metadata";
import { Link } from "@/i18n/navigation";
import { routing, type AppLocale } from "@/i18n/routing";
import { demoPostSlugs, getPost } from "@/lib/content-data";
import { getSiteSettings } from "@/lib/site-settings";

export const revalidate = 3600;
type PostPageProps = { params: Promise<{ locale: string; slug: string }> };
export function generateStaticParams() { return demoPostSlugs.map((slug) => ({ slug })); }

async function getRouteParams(params: PostPageProps["params"]) {
  const { locale, slug } = await params;
  if (!hasLocale(routing.locales, locale)) notFound();
  return { locale, slug } as { locale: AppLocale; slug: string };
}

export async function generateMetadata({ params }: PostPageProps): Promise<Metadata> {
  const { locale, slug } = await getRouteParams(params);
  const post = await getPost(locale, slug);
  if (!post) notFound();
  const title = post.meta_title || post.title;
  const description = post.meta_description || post.excerpt;
  return { title, description, alternates: getLocalizedAlternates(locale, `blog/${slug}`), openGraph: { type: "article", title, description, publishedTime: post.published_at ?? undefined, images: post.featured_image_url ? [post.featured_image_url] : undefined } };
}

export default async function PostPage({ params }: PostPageProps) {
  const { locale, slug } = await getRouteParams(params);
  setRequestLocale(locale);
  const [t, post, settings] = await Promise.all([getTranslations("Blog"), getPost(locale, slug), getSiteSettings()]);
  if (!post) notFound();
  const url = new URL(`/${locale}/blog/${slug}`, getSiteUrl()).toString();
  const jsonLd = { "@context": "https://schema.org", "@type": "Article", headline: post.title, description: post.meta_description || post.excerpt, image: post.featured_image_url || undefined, datePublished: post.published_at || undefined, mainEntityOfPage: url, author: { "@type": "Organization", name: settings.site_name }, publisher: { "@type": "Organization", name: settings.site_name, logo: settings.logo_url ? { "@type": "ImageObject", url: settings.logo_url } : undefined } };
  return (
    <main id="main-content" tabIndex={-1} className="flex-1 bg-[#fffcf7] text-stone-900">
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd).replace(/</g, "\\u003c") }} />
      <article><header className="border-b border-amber-900/10 bg-amber-50"><div className="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:py-20"><Link href="/blog" className="text-sm font-bold text-amber-800">← {t("allArticles")}</Link>{post.published_at && <time dateTime={post.published_at} className="mt-9 block text-xs font-bold uppercase tracking-wider text-stone-500">{new Intl.DateTimeFormat(locale, { dateStyle: "long" }).format(new Date(post.published_at))}</time>}<h1 className="mt-4 text-4xl font-bold tracking-tight text-amber-950 sm:text-5xl">{post.title}</h1>{post.excerpt && <p className="mt-5 text-lg leading-8 text-stone-600">{post.excerpt}</p>}</div></header>{post.featured_image_url && <div className="mx-auto max-w-5xl px-4 pt-12 sm:px-6"><img src={post.featured_image_url} alt="" width={1200} height={600} loading="lazy" decoding="async" className="aspect-[16/8] w-full rounded-3xl object-cover" /></div>}<div className="mx-auto max-w-3xl px-4 py-14 sm:px-6 lg:py-20"><ContentBody content={post.content} /><aside className="mt-12 rounded-2xl bg-amber-50 p-6 text-sm leading-6 text-amber-950">{t("disclaimer")}</aside></div></article>
    </main>
  );
}
