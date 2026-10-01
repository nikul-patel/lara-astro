"use client";

import type { FormEvent } from "react";
import { useState } from "react";
import { useTranslations } from "next-intl";
import {
  BirthDetailsFields,
  type BirthDetailsValue,
} from "@/components/shared/birth-details-fields";
import { api, ApiError, type DoshaResult } from "@/lib/api";

type Status = "idle" | "loading" | "error";

export function DoshasTool() {
  const t = useTranslations("Doshas");
  const [details, setDetails] = useState<BirthDetailsValue>({
    name: "",
    dob: "",
    time: "",
    place: "",
  });
  const [result, setResult] = useState<DoshaResult | null>(null);
  const [status, setStatus] = useState<Status>("idle");

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setStatus("loading");

    try {
      const response = await api.doshas.calculate({
        name: details.name ?? "",
        dob: details.dob ?? "",
        time: details.time ?? "",
        place: details.place ?? "",
      });
      setResult(response);
      setStatus("idle");
    } catch (error) {
      setResult(null);
      setStatus("error");
      if (!(error instanceof ApiError)) {
        throw error;
      }
    }
  }

  const manglikBadge = result
    ? result.manglik.is_manglik
      ? t("manglikPresent")
      : result.manglik.cancelled
        ? t("manglikCancelled")
        : t("manglikAbsent")
    : null;

  const kaalSarpBadge = result
    ? result.kaal_sarp.present
      ? t("kaalSarpPresent")
      : t("kaalSarpAbsent")
    : null;

  return (
    <div className="grid gap-10 lg:grid-cols-[0.85fr_1.15fr] lg:items-start">
      <form
        onSubmit={handleSubmit}
        className="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-sm sm:p-8"
      >
        <BirthDetailsFields
          fields={["name", "dob", "time", "place"]}
          value={details}
          onChange={setDetails}
          t={t}
        />

        <button
          type="submit"
          disabled={status === "loading"}
          className="mt-7 w-full rounded-full bg-amber-800 px-6 py-4 text-sm font-bold text-white transition hover:bg-amber-900 disabled:cursor-wait disabled:opacity-60"
        >
          {status === "loading" ? t("calculating") : t("calculate")}
        </button>
        <p className="mt-3 text-center text-xs leading-5 text-stone-500">
          {t("privacy")}
        </p>
        {status === "error" && (
          <p className="mt-4 rounded-2xl border border-blue-200 bg-blue-50 px-5 py-4 text-sm leading-6 text-blue-900">
            {t("detailsError")}
          </p>
        )}
      </form>

      <section aria-live="polite" className="min-w-0">
        {result ? (
          <div className="space-y-6">
            <p className="text-xs font-bold uppercase tracking-[0.18em] text-amber-700">
              {t("resultEyebrow")}
            </p>

            <div className="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
              <div className="flex flex-wrap items-center justify-between gap-3">
                <h2 className="text-xl font-bold text-stone-950">
                  {t("manglikTitle")}
                </h2>
                <span
                  className={`rounded-full px-4 py-1.5 text-xs font-bold ${
                    result.manglik.is_manglik
                      ? "bg-red-100 text-red-900"
                      : "bg-emerald-100 text-emerald-900"
                  }`}
                >
                  {manglikBadge}
                </span>
              </div>
              <p className="mt-4 text-sm leading-6 text-stone-700">
                {result.manglik.description}
              </p>
              {result.manglik.cancellation_reason && (
                <p className="mt-3 rounded-2xl bg-amber-50 px-5 py-4 text-sm leading-6 text-amber-950">
                  {result.manglik.cancellation_reason}
                </p>
              )}
            </div>

            <div className="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
              <div className="flex flex-wrap items-center justify-between gap-3">
                <h2 className="text-xl font-bold text-stone-950">
                  {t("kaalSarpTitle")}
                </h2>
                <span
                  className={`rounded-full px-4 py-1.5 text-xs font-bold ${
                    result.kaal_sarp.present
                      ? "bg-red-100 text-red-900"
                      : "bg-emerald-100 text-emerald-900"
                  }`}
                >
                  {kaalSarpBadge}
                </span>
              </div>
              {result.kaal_sarp.type && (
                <p className="mt-2 text-sm font-semibold text-amber-800">
                  {t("kaalSarpType", { type: result.kaal_sarp.type })}
                </p>
              )}
              <p className="mt-4 text-sm leading-6 text-stone-700">
                {result.kaal_sarp.description}
              </p>
            </div>
          </div>
        ) : (
          <div className="grid min-h-[24rem] place-items-center rounded-[2rem] border border-dashed border-amber-800/25 bg-amber-50 p-8 text-center">
            <div>
              <span aria-hidden="true" className="text-7xl text-amber-700">
                ✦
              </span>
              <h2 className="mt-5 text-2xl font-bold text-amber-950">
                {t("emptyTitle")}
              </h2>
              <p className="mx-auto mt-3 max-w-md text-sm leading-6 text-stone-600">
                {t("emptyDescription")}
              </p>
            </div>
          </div>
        )}
      </section>
    </div>
  );
}
