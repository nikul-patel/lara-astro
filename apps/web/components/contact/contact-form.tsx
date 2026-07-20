"use client";

import type { FormEvent } from "react";
import { useId, useState } from "react";
import { useTranslations } from "next-intl";

type ContactFormProps = {
  toEmail?: string;
  whatsappUrl?: string;
};

type FieldErrors = Partial<Record<"name" | "email" | "message", string>>;

const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

export function ContactForm({ toEmail, whatsappUrl }: ContactFormProps) {
  const t = useTranslations("Contact");
  const baseId = useId();
  const [values, setValues] = useState({ name: "", email: "", topic: "", reference: "", message: "" });
  const [errors, setErrors] = useState<FieldErrors>({});
  const [submitted, setSubmitted] = useState(false);

  function validate(): FieldErrors {
    const next: FieldErrors = {};
    if (!values.name.trim()) next.name = t("errors.name");
    if (!values.email.trim()) next.email = t("errors.emailRequired");
    else if (!EMAIL_PATTERN.test(values.email.trim())) next.email = t("errors.emailInvalid");
    if (values.message.trim().length < 10) next.message = t("errors.message");
    return next;
  }

  function submit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const nextErrors = validate();
    setErrors(nextErrors);
    if (Object.keys(nextErrors).length > 0) return;

    // No contact API endpoint exists yet, so the message is handed off to the
    // visitor's email client as a graceful fallback (see note below the form).
    if (toEmail) {
      const name = values.name.trim();
      const topic = values.topic.trim();
      const reference = values.reference.trim();
      const subject = encodeURIComponent(
        topic ? t("mailtoSubjectTopic", { name, topic }) : t("mailtoSubject", { name }),
      );
      const bodyLines = [values.message.trim(), "", `— ${name} (${values.email.trim()})`];
      if (reference) bodyLines.push(`${t("reference")}: ${reference}`);
      const body = encodeURIComponent(bodyLines.join("\n"));
      window.location.href = `mailto:${toEmail}?subject=${subject}&body=${body}`;
    }
    setSubmitted(true);
  }

  const fieldClass = (invalid: boolean) =>
    `mt-2 w-full rounded-xl border px-4 py-3 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-700 ${invalid ? "border-red-500" : "border-stone-300"}`;

  if (!toEmail) {
    return (
      <div className="rounded-[2rem] border border-stone-200 bg-white p-7 shadow-sm">
        <h2 className="text-2xl font-bold text-stone-950">{t("formTitle")}</h2>
        <p className="mt-3 text-sm leading-6 text-stone-600">{t("formUnavailable")}</p>
        {whatsappUrl && (
          <a href={whatsappUrl} target="_blank" rel="noreferrer" className="mt-6 inline-flex rounded-full bg-emerald-600 px-6 py-3.5 text-sm font-bold text-white hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700">
            {t("whatsapp")}
          </a>
        )}
      </div>
    );
  }

  return (
    <form onSubmit={submit} noValidate aria-describedby={`${baseId}-note`} className="rounded-[2rem] border border-stone-200 bg-white p-7 shadow-sm">
      <h2 className="text-2xl font-bold text-stone-950">{t("formTitle")}</h2>
      <p className="mt-3 text-sm leading-6 text-stone-600">{t("formDescription")}</p>

      <div className="mt-6 space-y-4">
        <div>
          <label htmlFor={`${baseId}-name`} className="block text-sm font-bold text-stone-800">{t("name")}</label>
          <input
            id={`${baseId}-name`}
            name="name"
            type="text"
            autoComplete="name"
            required
            value={values.name}
            onChange={(e) => setValues({ ...values, name: e.target.value })}
            aria-invalid={errors.name ? true : undefined}
            aria-describedby={errors.name ? `${baseId}-name-error` : undefined}
            className={fieldClass(Boolean(errors.name))}
          />
          {errors.name && <p id={`${baseId}-name-error`} className="mt-1.5 text-sm text-red-700">{errors.name}</p>}
        </div>

        <div>
          <label htmlFor={`${baseId}-email`} className="block text-sm font-bold text-stone-800">{t("email")}</label>
          <input
            id={`${baseId}-email`}
            name="email"
            type="email"
            autoComplete="email"
            required
            value={values.email}
            onChange={(e) => setValues({ ...values, email: e.target.value })}
            aria-invalid={errors.email ? true : undefined}
            aria-describedby={errors.email ? `${baseId}-email-error` : undefined}
            className={fieldClass(Boolean(errors.email))}
          />
          {errors.email && <p id={`${baseId}-email-error`} className="mt-1.5 text-sm text-red-700">{errors.email}</p>}
        </div>

        <div>
          <label htmlFor={`${baseId}-topic`} className="block text-sm font-bold text-stone-800">{t("topic")}</label>
          <input
            id={`${baseId}-topic`}
            name="topic"
            type="text"
            value={values.topic}
            onChange={(e) => setValues({ ...values, topic: e.target.value })}
            placeholder={t("topicPlaceholder")}
            className={fieldClass(false)}
          />
        </div>

        <div>
          <label htmlFor={`${baseId}-reference`} className="block text-sm font-bold text-stone-800">{t("reference")}</label>
          <input
            id={`${baseId}-reference`}
            name="reference"
            type="text"
            inputMode="text"
            autoComplete="off"
            value={values.reference}
            onChange={(e) => setValues({ ...values, reference: e.target.value })}
            aria-describedby={`${baseId}-reference-help`}
            placeholder={t("referencePlaceholder")}
            className={fieldClass(false)}
          />
          <p id={`${baseId}-reference-help`} className="mt-1.5 text-xs leading-5 text-stone-500">{t("referenceHelp")}</p>
        </div>

        <div>
          <label htmlFor={`${baseId}-message`} className="block text-sm font-bold text-stone-800">{t("message")}</label>
          <textarea
            id={`${baseId}-message`}
            name="message"
            rows={5}
            required
            value={values.message}
            onChange={(e) => setValues({ ...values, message: e.target.value })}
            aria-invalid={errors.message ? true : undefined}
            aria-describedby={errors.message ? `${baseId}-message-error` : undefined}
            className={fieldClass(Boolean(errors.message))}
          />
          {errors.message && <p id={`${baseId}-message-error`} className="mt-1.5 text-sm text-red-700">{errors.message}</p>}
        </div>
      </div>

      <p className="mt-5 text-xs leading-5 text-stone-500">{t("privacy")}</p>

      <div aria-live="polite">
        {submitted && (
          <p className="mt-5 rounded-xl bg-emerald-50 p-4 text-sm leading-6 text-emerald-900">
            {t("success")}
            {whatsappUrl && (
              <>
                {" "}
                <a href={whatsappUrl} target="_blank" rel="noreferrer" className="font-bold underline hover:text-emerald-950">
                  {t("successWhatsapp")}
                </a>
              </>
            )}
          </p>
        )}
      </div>

      <button
        type="submit"
        className="mt-6 w-full rounded-full bg-amber-800 px-6 py-4 text-sm font-bold text-white transition hover:bg-amber-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-800"
      >
        {t("submit")}
      </button>

      <p id={`${baseId}-note`} className="mt-4 text-xs leading-5 text-stone-500">{t("fallbackNote")}</p>
    </form>
  );
}
