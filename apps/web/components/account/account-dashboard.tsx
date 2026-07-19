"use client";

import type { FormEvent } from "react";
import { useCallback, useEffect, useMemo, useState, useSyncExternalStore } from "react";
import { useLocale, useTranslations } from "next-intl";
import { LiveSessionTime } from "@/components/courses/live-session-time";
import { Link } from "@/i18n/navigation";
import { api, ApiError, type Booking, type Client, type Enrollment, type SavedChart } from "@/lib/api";

const AUTH_TOKEN_KEY = "lara-astro-auth-token";
const authListeners = new Set<() => void>();

type DashboardData = { client: Client; bookings: Booking[]; enrollments: Enrollment[]; charts: SavedChart[] };
type Section = "courses" | "bookings" | "charts";

function progressKey(clientId: number) { return `lara-astro-progress-${clientId}`; }
function subscribeAuth(listener: () => void) { authListeners.add(listener); return () => authListeners.delete(listener); }
function getAuthToken() { return window.localStorage.getItem(AUTH_TOKEN_KEY); }
function storeAuthToken(token: string | null) { if (token) window.localStorage.setItem(AUTH_TOKEN_KEY, token); else window.localStorage.removeItem(AUTH_TOKEN_KEY); authListeners.forEach((listener) => listener()); }
function readProgress(clientId: number): Set<number> {
  try { return new Set(JSON.parse(window.localStorage.getItem(progressKey(clientId)) ?? "[]") as number[]); } catch { return new Set(); }
}
function safeExternalUrl(value?: string): string | null {
  if (!value) return null;
  try { const url = new URL(value); return url.protocol === "https:" || url.protocol === "http:" ? url.toString() : null; } catch { return null; }
}

export function AccountDashboard() {
  const t = useTranslations("Account");
  const locale = useLocale();
  const [authMode, setAuthMode] = useState<"login" | "register">("login");
  const [section, setSection] = useState<Section>("courses");
  const [credentials, setCredentials] = useState({ name: "", email: "", phone: "", password: "", passwordConfirmation: "" });
  const [data, setData] = useState<DashboardData | null>(null);
  const [completedLessons, setCompletedLessons] = useState<Set<number>>(new Set());
  const [pending, setPending] = useState(false);
  const [notice, setNotice] = useState<string | null>(null);
  const [loadFailed, setLoadFailed] = useState(false);
  const token = useSyncExternalStore(subscribeAuth, getAuthToken, () => null);

  const loadDashboard = useCallback(async (token: string) => {
    const [me, bookings, enrollments, charts] = await Promise.all([api.auth.me(token), api.bookings.mine(token), api.enrollments.mine(token), api.charts.mine(token)]);
    setData({ client: me.client, bookings, enrollments, charts });
    setCompletedLessons(readProgress(me.client.id));
  }, []);

  useEffect(() => {
    if (!token || data || loadFailed) return;
    // Synchronize authenticated API data when the external token store changes.
    // eslint-disable-next-line react-hooks/set-state-in-effect
    loadDashboard(token).catch((error) => { if (error instanceof ApiError && error.status === 401) storeAuthToken(null); else setLoadFailed(true); });
  }, [data, loadDashboard, loadFailed, token]);

  async function submitAuth(event: FormEvent<HTMLFormElement>) {
    event.preventDefault(); setPending(true); setNotice(null);
    try {
      const result = authMode === "login"
        ? await api.auth.login({ email: credentials.email, password: credentials.password })
        : await api.auth.register({ name: credentials.name, email: credentials.email, phone: credentials.phone, password: credentials.password, password_confirmation: credentials.passwordConfirmation });
      await loadDashboard(result.token);
      storeAuthToken(result.token);
    } catch (error) {
      setNotice(error instanceof ApiError ? error.errors ? Object.values(error.errors).flat()[0] ?? error.message : error.message : t("authError"));
    } finally { setPending(false); }
  }

  async function logout() {
    if (token) await api.auth.logout(token).catch(() => undefined);
    storeAuthToken(null); setData(null); setCompletedLessons(new Set()); setLoadFailed(false);
  }

  function toggleLesson(id: number) {
    if (!data) return;
    const next = new Set(completedLessons);
    if (next.has(id)) next.delete(id); else next.add(id);
    window.localStorage.setItem(progressKey(data.client.id), JSON.stringify([...next]));
    setCompletedLessons(next);
  }

  const confirmedEnrollments = useMemo(() => data?.enrollments.filter((item) => item.status === "confirmed") ?? [], [data]);

  if (token && !data && loadFailed) return <div className="rounded-3xl bg-white p-8 text-center"><p className="text-stone-600" role="alert">{t("loadError")}</p><button type="button" onClick={() => setLoadFailed(false)} className="mt-5 rounded-full bg-amber-800 px-5 py-3 text-sm font-bold text-white">{t("retry")}</button></div>;
  if (token && !data) return <p className="rounded-3xl bg-white p-8 text-center text-stone-600" aria-live="polite">{t("loading")}</p>;

  if (!data) return (
    <div className="mx-auto max-w-2xl rounded-[2rem] border border-stone-200 bg-white p-7 shadow-sm sm:p-10">
      <div className="grid grid-cols-2 rounded-full bg-stone-100 p-1"><button type="button" onClick={() => { setAuthMode("login"); setNotice(null); }} className={`rounded-full px-4 py-3 text-sm font-bold ${authMode === "login" ? "bg-amber-800 text-white" : "text-stone-600"}`}>{t("login")}</button><button type="button" onClick={() => { setAuthMode("register"); setNotice(null); }} className={`rounded-full px-4 py-3 text-sm font-bold ${authMode === "register" ? "bg-amber-800 text-white" : "text-stone-600"}`}>{t("register")}</button></div>
      <h2 className="mt-7 text-2xl font-bold text-stone-950">{authMode === "login" ? t("loginTitle") : t("registerTitle")}</h2><p className="mt-2 text-sm leading-6 text-stone-600">{t("optionalAccount")}</p>
      <form onSubmit={submitAuth} className="mt-6 space-y-4">
        {authMode === "register" && <><label className="block"><span className="text-sm font-bold">{t("name")}</span><input required autoComplete="name" value={credentials.name} onChange={(e) => setCredentials({ ...credentials, name: e.target.value })} className="mt-2 w-full rounded-xl border border-stone-300 px-4 py-3" /></label><label className="block"><span className="text-sm font-bold">{t("phone")}</span><input type="tel" autoComplete="tel" value={credentials.phone} onChange={(e) => setCredentials({ ...credentials, phone: e.target.value })} className="mt-2 w-full rounded-xl border border-stone-300 px-4 py-3" /></label></>}
        <label className="block"><span className="text-sm font-bold">{t("email")}</span><input required type="email" autoComplete="email" value={credentials.email} onChange={(e) => setCredentials({ ...credentials, email: e.target.value })} className="mt-2 w-full rounded-xl border border-stone-300 px-4 py-3" /></label>
        <label className="block"><span className="text-sm font-bold">{t("password")}</span><input required minLength={8} type="password" autoComplete={authMode === "login" ? "current-password" : "new-password"} value={credentials.password} onChange={(e) => setCredentials({ ...credentials, password: e.target.value })} className="mt-2 w-full rounded-xl border border-stone-300 px-4 py-3" /></label>
        {authMode === "register" && <label className="block"><span className="text-sm font-bold">{t("confirmPassword")}</span><input required minLength={8} type="password" autoComplete="new-password" value={credentials.passwordConfirmation} onChange={(e) => setCredentials({ ...credentials, passwordConfirmation: e.target.value })} className="mt-2 w-full rounded-xl border border-stone-300 px-4 py-3" /></label>}
        {notice && <p className="rounded-xl bg-red-50 p-4 text-sm text-red-900" role="alert">{notice}</p>}
        <button disabled={pending} className="w-full rounded-full bg-amber-800 px-6 py-4 text-sm font-bold text-white disabled:opacity-50">{pending ? t("submitting") : authMode === "login" ? t("login") : t("register")}</button>
      </form>
    </div>
  );

  return (
    <div className="grid gap-8 lg:grid-cols-[17rem_1fr] lg:items-start">
      <aside className="rounded-3xl border border-stone-200 bg-white p-5 lg:sticky lg:top-28"><p className="text-xs font-bold uppercase tracking-wider text-stone-500">{t("signedInAs")}</p><p className="mt-2 text-lg font-bold text-stone-950">{data.client.name}</p><p className="text-sm text-stone-500">{data.client.email}</p><nav className="mt-6 space-y-2" aria-label={t("accountNavigation")}>{(["courses", "bookings", "charts"] as Section[]).map((item) => <button key={item} type="button" onClick={() => setSection(item)} aria-current={section === item ? "page" : undefined} className={`w-full rounded-xl px-4 py-3 text-left text-sm font-bold ${section === item ? "bg-amber-800 text-white" : "text-stone-700 hover:bg-amber-50"}`}>{t(`sections.${item}`)}</button>)}</nav><button type="button" onClick={logout} className="mt-6 w-full border-t border-stone-200 pt-5 text-left text-sm font-bold text-red-700">{t("logout")}</button></aside>
      <div>
        <div className="grid gap-4 sm:grid-cols-3"><Summary value={confirmedEnrollments.length} label={t("summary.activeCourses")} /><Summary value={data.bookings.length} label={t("summary.bookings")} /><Summary value={data.charts.length} label={t("summary.charts")} /></div>
        {section === "courses" && <Courses enrollments={data.enrollments} completed={completedLessons} onToggle={toggleLesson} locale={locale} />}
        {section === "bookings" && <Bookings bookings={data.bookings} locale={locale} />}
        {section === "charts" && <Charts charts={data.charts} />}
      </div>
    </div>
  );
}

function Summary({ value, label }: { value: number; label: string }) { return <div className="rounded-2xl border border-stone-200 bg-white p-5"><p className="text-3xl font-bold text-amber-800">{value}</p><p className="mt-1 text-sm text-stone-500">{label}</p></div>; }

function Courses({ enrollments, completed, onToggle, locale }: { enrollments: Enrollment[]; completed: Set<number>; onToggle: (id: number) => void; locale: string }) {
  const t = useTranslations("Account");
  if (!enrollments.length) return <Empty title={t("empty.coursesTitle")} body={t("empty.coursesBody")} href="/courses" action={t("empty.browseCourses")} />;
  return <section className="mt-8 space-y-5"><h2 className="text-2xl font-bold">{t("sections.courses")}</h2>{enrollments.map((enrollment) => { const course = enrollment.course; const lessons = course?.modules?.flatMap((module) => module.lessons ?? []) ?? []; const completedCount = lessons.filter((lesson) => completed.has(lesson.id)).length; return <article key={enrollment.id} className="rounded-3xl border border-stone-200 bg-white p-6"><div className="flex flex-wrap items-start justify-between gap-4"><div><h3 className="text-xl font-bold">{course?.title ?? t("courseFallback")}</h3><p className="mt-1 text-sm text-stone-500">{t("reference", { reference: enrollment.reference_number })}</p></div><Status value={enrollment.status} /></div>{enrollment.status !== "confirmed" ? <p className="mt-5 rounded-xl bg-amber-50 p-4 text-sm text-amber-900">{t("accessPending")}</p> : course?.type === "recorded" ? <div className="mt-6"><div className="flex items-center justify-between text-sm"><span className="font-bold">{t("progress")}</span><span>{t("lessonsComplete", { complete: completedCount, total: lessons.length })}</span></div><progress value={completedCount} max={Math.max(lessons.length, 1)} className="mt-2 h-2 w-full accent-amber-800" /><p className="mt-2 text-xs text-stone-500">{t("deviceProgress")}</p><div className="mt-5 space-y-5">{course.modules?.map((module) => <div key={module.id}><h4 className="font-bold">{module.title}</h4><ul className="mt-2 space-y-2">{module.lessons?.map((lesson) => { const videoUrl = safeExternalUrl(lesson.video_url); return <li key={lesson.id} className="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-stone-50 p-4"><label className="flex items-center gap-3 text-sm font-semibold"><input type="checkbox" checked={completed.has(lesson.id)} onChange={() => onToggle(lesson.id)} className="size-4 accent-amber-800" />{lesson.title}</label>{videoUrl && <a href={videoUrl} target="_blank" rel="noopener noreferrer" className="text-sm font-bold text-amber-800">{t("watchLesson")} ↗</a>}</li>; })}</ul></div>)}</div></div> : <div className="mt-6"><h4 className="font-bold">{t("liveClasses")}</h4><ul className="mt-3 space-y-3">{course?.live_sessions?.map((session) => { const meetingUrl = safeExternalUrl(session.meeting_url); return <li key={session.id} className="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-stone-50 p-4"><LiveSessionTime startsAt={session.starts_at} locale={locale} />{meetingUrl && <a href={meetingUrl} target="_blank" rel="noopener noreferrer" className="text-sm font-bold text-amber-800">{t("joinClass")} ↗</a>}</li>; })}</ul></div>}</article>; })}</section>;
}

function Bookings({ bookings, locale }: { bookings: Booking[]; locale: string }) { const t = useTranslations("Account"); if (!bookings.length) return <Empty title={t("empty.bookingsTitle")} body={t("empty.bookingsBody")} href="/booking" action={t("empty.bookConsultation")} />; return <section className="mt-8"><h2 className="text-2xl font-bold">{t("sections.bookings")}</h2><div className="mt-5 space-y-3">{bookings.map((booking) => <article key={booking.id} className="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-stone-200 bg-white p-5"><div><p className="font-bold">{new Intl.DateTimeFormat(locale, { dateStyle: "medium", timeStyle: "short" }).format(new Date(booking.slot))}</p><p className="mt-1 text-sm text-stone-500">{t("reference", { reference: booking.reference_number })}</p></div><Status value={booking.status} /></article>)}</div></section>; }

function Charts({ charts }: { charts: SavedChart[] }) { const t = useTranslations("Account"); if (!charts.length) return <Empty title={t("empty.chartsTitle")} body={t("empty.chartsBody")} href="/birth-chart" action={t("empty.createChart")} />; return <section className="mt-8"><h2 className="text-2xl font-bold">{t("sections.charts")}</h2><div className="mt-5 grid gap-4 sm:grid-cols-2">{charts.map((chart) => <article key={chart.id} className="rounded-2xl border border-stone-200 bg-white p-5"><h3 className="font-bold">{chart.name}</h3><dl className="mt-4 space-y-2 text-sm"><div><dt className="text-stone-500">{t("birthDate")}</dt><dd>{chart.input.dob}</dd></div><div><dt className="text-stone-500">{t("birthPlace")}</dt><dd>{chart.input.place}</dd></div><div><dt className="text-stone-500">{t("system")}</dt><dd className="capitalize">{chart.input.system?.replace("_", " ")}</dd></div></dl></article>)}</div><Link href="/birth-chart" className="mt-6 inline-flex rounded-full bg-amber-800 px-5 py-3 text-sm font-bold text-white">{t("empty.createChart")}</Link></section>; }

function Empty({ title, body, href, action }: { title: string; body: string; href: "/courses" | "/booking" | "/birth-chart"; action: string }) { return <section className="mt-8 rounded-3xl border border-stone-200 bg-white p-8 text-center"><h2 className="text-2xl font-bold">{title}</h2><p className="mx-auto mt-3 max-w-lg text-stone-600">{body}</p><Link href={href} className="mt-6 inline-flex rounded-full bg-amber-800 px-5 py-3 text-sm font-bold text-white">{action}</Link></section>; }
function Status({ value }: { value: string }) { const t = useTranslations("Account"); return <span className="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-900">{t.has(`statuses.${value}`) ? t(`statuses.${value}`) : value.replaceAll("_", " ")}</span>; }
