"use client";

/* eslint-disable @next/next/no-img-element -- QR host is tenant-configured at runtime. */

import type { FormEvent } from "react";
import { useState } from "react";
import { useTranslations } from "next-intl";
import { CoursePrice } from "@/components/courses/course-price";
import { api, ApiError, type Course, type Enrollment } from "@/lib/api";

const AUTH_TOKEN_KEY = "lara-astro-auth-token";

export function EnrollmentForm({ course, upiId, upiQrUrl }: { course: Course; upiId: string | null; upiQrUrl: string | null }) {
  const t = useTranslations("CourseEnrollment");
  const [contact, setContact] = useState({ name: "", email: "", phone: "" });
  const [pending, setPending] = useState(false);
  const [notice, setNotice] = useState<string | null>(null);
  const [enrollment, setEnrollment] = useState<Enrollment | null>(null);
  const [paymentSettings, setPaymentSettings] = useState({ upiId, upiQrUrl });
  const isDemo = course.id < 0;

  async function submit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setPending(true);
    setNotice(null);
    if (isDemo) {
      setEnrollment({ id: -1, course_id: course.id, status: "pending_payment", reference_number: `DEMO-${new Date().getFullYear()}-0001`, client: { id: -1, ...contact } });
      setPending(false);
      return;
    }
    const token = window.localStorage.getItem(AUTH_TOKEN_KEY) ?? undefined;
    try {
      const result = await api.enrollments.create({ course_id: course.id, client: contact, guest: !token }, token);
      const currentSettings = await api.settings({ cache: "no-store" }).catch(() => null);
      setPaymentSettings({
        upiId: result.upi_id ?? currentSettings?.upi_id ?? upiId,
        upiQrUrl: result.upi_qr_url ?? currentSettings?.upi_qr_url ?? upiQrUrl,
      });
      setEnrollment(result);
    } catch (error) {
      setNotice(error instanceof ApiError && error.status === 422 ? error.message : t("error"));
    } finally {
      setPending(false);
    }
  }

  if (enrollment) {
    const confirmationUpiId = enrollment.upi_id ?? paymentSettings.upiId;
    const confirmationQrUrl = enrollment.upi_qr_url ?? paymentSettings.upiQrUrl;
    return (
      <section className="rounded-[2rem] border border-stone-200 bg-white p-7 shadow-sm" aria-live="polite">
        <div className="grid size-12 place-items-center rounded-full bg-emerald-100 text-emerald-800">✓</div>
        <p className="mt-5 text-sm font-bold uppercase tracking-[0.18em] text-amber-700">{t("confirmationEyebrow")}</p>
        <h2 className="mt-2 text-2xl font-bold text-stone-950">{isDemo ? t("demoTitle") : t("confirmationTitle")}</h2>
        <p className="mt-3 text-sm leading-6 text-stone-600">{isDemo ? t("demoDescription") : t("confirmationDescription")}</p>
        <dl className="mt-6 grid gap-4 rounded-2xl bg-stone-50 p-5 sm:grid-cols-2">
          <div><dt className="text-xs font-bold uppercase text-stone-500">{t("reference")}</dt><dd className="mt-1 font-bold">{enrollment.reference_number}</dd></div>
          <div><dt className="text-xs font-bold uppercase text-stone-500">{t("status")}</dt><dd className="mt-1 font-bold text-amber-800">{t("pendingPayment")}</dd></div>
        </dl>
        {!isDemo && confirmationUpiId ? <div className="mt-6 grid gap-5 rounded-2xl border border-amber-200 bg-amber-50 p-5 sm:grid-cols-[1fr_auto] sm:items-center"><div><h3 className="font-bold text-amber-950">{t("paymentTitle")}</h3><p className="mt-2 text-sm text-amber-900/75">{t("paymentDescription", { reference: enrollment.reference_number })}</p><p className="mt-3 rounded-xl bg-white px-4 py-3 font-bold text-amber-900">{confirmationUpiId}</p></div>{confirmationQrUrl && <>{/* Tenant-controlled URL from Settings; supported hosts are not known at build time. */}<img src={confirmationQrUrl} alt={t("qrAlt")} className="size-36 rounded-xl bg-white object-contain p-2" /></>}</div> : !isDemo ? <p className="mt-6 rounded-2xl bg-blue-50 p-4 text-sm text-blue-900">{t("paymentUnavailable")}</p> : null}
        <ol className="mt-6 list-decimal space-y-2 pl-5 text-sm text-stone-600"><li>{isDemo ? t("demoNext") : t("nextPay")}</li><li>{t("nextReview")}</li><li>{t("nextAccess")}</li></ol>
      </section>
    );
  }

  return (
    <form onSubmit={submit} className="rounded-[2rem] border border-stone-200 bg-white p-7 shadow-sm">
      <p className="text-sm font-bold uppercase tracking-[0.18em] text-amber-700">{t("eyebrow")}</p>
      <h2 className="mt-2 text-2xl font-bold text-stone-950">{t("title")}</h2>
      <p className="mt-3 text-sm leading-6 text-stone-600">{t("description")}</p>
      <p className="mt-5 text-2xl font-bold text-amber-800"><CoursePrice inr={course.price_inr} usd={course.price_usd} /></p>
      <div className="mt-6 space-y-4">
        <label className="block"><span className="text-sm font-bold">{t("name")}</span><input required autoComplete="name" value={contact.name} onChange={(e) => setContact({ ...contact, name: e.target.value })} className="mt-2 w-full rounded-xl border border-stone-300 px-4 py-3" /></label>
        <label className="block"><span className="text-sm font-bold">{t("email")}</span><input required type="email" autoComplete="email" value={contact.email} onChange={(e) => setContact({ ...contact, email: e.target.value })} className="mt-2 w-full rounded-xl border border-stone-300 px-4 py-3" /></label>
        <label className="block"><span className="text-sm font-bold">{t("phone")}</span><input required type="tel" autoComplete="tel" value={contact.phone} onChange={(e) => setContact({ ...contact, phone: e.target.value })} className="mt-2 w-full rounded-xl border border-stone-300 px-4 py-3" /></label>
      </div>
      <p className="mt-5 text-xs leading-5 text-stone-500">{t("privacy")}</p>
      {notice && <p className="mt-5 rounded-xl bg-red-50 p-4 text-sm text-red-900">{notice}</p>}
      <button disabled={pending} className="mt-6 w-full rounded-full bg-amber-800 px-6 py-4 text-sm font-bold text-white disabled:opacity-50">{pending ? t("submitting") : t("submit")}</button>
    </form>
  );
}
