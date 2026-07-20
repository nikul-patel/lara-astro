import type { Metadata } from "next";
/* eslint-disable @next/next/no-img-element -- CMS media hosts vary by tenant at runtime. */
import { hasLocale } from "next-intl";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { notFound } from "next/navigation";
import { getLocalizedAlternates } from "@/i18n/metadata";
import { Link } from "@/i18n/navigation";
import { routing, type AppLocale } from "@/i18n/routing";
import { getPosts } from "@/lib/content-data";

export const revalidate = 3600;
type BlogPageProps = { params: Promise<{ locale: string }>; searchParams: Promise<{ page?: string }> };

async function getLocale(params: BlogPageProps["params"]): Promise<AppLocale> {
  const { locale } = await params;
  if (!hasLocale(routing.locales, locale)) notFound();
  return locale;
}

export async function generateMetadata({ params, searchParams }: BlogPageProps): Promise<Metadata> {
  const locale = await getLocale(params);
  const query = await searchParams;
  const requestedPage = Number.parseInt(query.page ?? "1", 10);
  const page = Number.isFinite(requestedPage) && requestedPage > 1 ? requestedPage : 1;
  const t = await getTranslations({ locale, namespace: "Blog" });
  return { title: t("metaTitle"), description: t("metaDescription"), alternates: getLocalizedAlternates(locale, page > 1 ? `blog?page=${page}` : "blog"), openGraph: { title: t("metaTitle"), description: t("metaDescription"), type: "website" } };
}

export default async function BlogPage({ params, searchParams }: BlogPageProps) {
  const locale = await getLocale(params);
  setRequestLocale(locale);
  const query = await searchParams;
  const requestedPage = Number.parseInt(query.page ?? "1", 10);
  const page = Number.isFinite(requestedPage) && requestedPage > 0 ? requestedPage : 1;
  const [t, posts] = await Promise.all([getTranslations("Blog"), getPosts(locale, page)]);

  return (
    <main id="main-content" tabIndex={-1} className="flex-1 bg-[#fffcf7] text-stone-900">
      <section className="border-b border-amber-900/10 bg-amber-50"><div className="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 lg:py-24"><p className="text-sm font-bold uppercase tracking-[0.22em] text-amber-700">{t("eyebrow")}</p><h1 className="mt-4 text-4xl font-bold tracking-tight text-amber-950 sm:text-5xl">{t("title")}</h1><p className="mx-auto mt-5 max-w-2xl text-lg leading-8 text-stone-600">{t("description")}</p></div></section>
      <section className="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
        {posts.data.length ? <div className="grid gap-7 md:grid-cols-2 lg:grid-cols-3">{posts.data.map((post) => <article key={post.id} className="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">{post.featured_image_url && <>{/* CMS media hosts vary per tenant. */}<img src={post.featured_image_url} alt="" width={1200} height={675} loading="lazy" decoding="async" className="aspect-[16/9] w-full object-cover" /></>}<div className="p-7">{post.published_at && <time dateTime={post.published_at} className="text-xs font-bold uppercase tracking-wider text-stone-500">{new Intl.DateTimeFormat(locale, { dateStyle: "long" }).format(new Date(post.published_at))}</time>}<h2 className="mt-4 text-xl font-bold leading-7"><Link href={`/blog/${post.slug}`} className="hover:text-amber-800">{post.title}</Link></h2>{post.excerpt && <p className="mt-3 text-sm leading-6 text-stone-600">{post.excerpt}</p>}<Link href={`/blog/${post.slug}`} className="mt-6 inline-flex text-sm font-bold text-amber-800">{t("readArticle")} →</Link></div></article>)}</div> : <p className="rounded-3xl bg-white p-8 text-center text-stone-600">{t("empty")}</p>}
        {posts.meta.last_page > 1 && <nav aria-label={t("pagination")} className="mt-12 flex items-center justify-center gap-4">{page > 1 && <Link href={`/blog?page=${page - 1}`} className="rounded-full border border-stone-300 px-5 py-3 text-sm font-bold">← {t("previous")}</Link>}<span className="text-sm text-stone-500">{t("page", { current: posts.meta.current_page, total: posts.meta.last_page })}</span>{page < posts.meta.last_page && <Link href={`/blog?page=${page + 1}`} className="rounded-full border border-stone-300 px-5 py-3 text-sm font-bold">{t("next")} →</Link>}</nav>}
      </section>
    </main>
  );
}
