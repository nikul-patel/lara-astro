"use client";

import type { FormEvent } from "react";
import { useTranslations } from "next-intl";

export function ContactForm({ recipient }: { recipient: string }) {
  const t = useTranslations("Contact");

  function openEmail(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    const subject = t("emailSubject", { topic: String(form.get("topic") || "") });
    const body = [
      `${t("name")}: ${form.get("name") || ""}`,
      `${t("email")}: ${form.get("email") || ""}`,
      `${t("phone")}: ${form.get("phone") || ""}`,
      `${t("reference")}: ${form.get("reference") || "—"}`,
      "",
      String(form.get("message") || ""),
    ].join("\n");

    window.location.href = `mailto:${recipient}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
  }

  const inputClass = "mt-2 w-full rounded-xl border border-stone-300 bg-white px-4 py-3 text-stone-950 outline-none focus:border-amber-700 focus:ring-2 focus:ring-amber-100";

  return (
    <form onSubmit={openEmail} className="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
      <h2 className="text-2xl font-bold text-stone-950">{t("formTitle")}</h2>
      <p className="mt-2 text-sm leading-6 text-stone-600">{t("formDescription")}</p>
      <div className="mt-7 grid gap-5 sm:grid-cols-2">
        <label className="text-sm font-bold text-stone-800">
          {t("name")}
          <input name="name" required autoComplete="name" className={inputClass} />
        </label>
        <label className="text-sm font-bold text-stone-800">
          {t("email")}
          <input name="email" required type="email" autoComplete="email" className={inputClass} />
        </label>
        <label className="text-sm font-bold text-stone-800">
          {t("phone")}
          <input name="phone" type="tel" autoComplete="tel" className={inputClass} />
        </label>
        <label className="text-sm font-bold text-stone-800">
          {t("reference")}
          <input name="reference" className={inputClass} />
        </label>
        <label className="text-sm font-bold text-stone-800 sm:col-span-2">
          {t("topic")}
          <input name="topic" required className={inputClass} />
        </label>
        <label className="text-sm font-bold text-stone-800 sm:col-span-2">
          {t("message")}
          <textarea name="message" required rows={6} className={inputClass} />
        </label>
      </div>
      <p className="mt-5 text-xs leading-5 text-stone-500">{t("privacy")}</p>
      <button type="submit" className="mt-6 rounded-full bg-amber-800 px-6 py-3 text-sm font-bold text-white transition hover:bg-amber-900 focus:outline-none focus:ring-2 focus:ring-amber-700 focus:ring-offset-2">
        {t("submit")}
      </button>
    </form>
  );
}
