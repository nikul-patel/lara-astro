"use client";

import type { FormEvent } from "react";
import { useState } from "react";
import { useTranslations } from "next-intl";
import {
  BirthDetailsFields,
  type BirthDetailsValue,
} from "@/components/shared/birth-details-fields";
import { api, ApiError, type SadeSatiResult } from "@/lib/api";

type Status = "idle" | "loading" | "error";

export function SadeSatiTool() {
  const t = useTranslations("SadeSati");
  const [details, setDetails] = useState<BirthDetailsValue>({
    name: "",
    dob: "",
    time: "",
    place: "",
    date: "",
  });
  const [result, setResult] = useState<SadeSatiResult | null>(null);
  const [status, setStatus] = useState<Status>("idle");

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setStatus("loading");

    try {
      const response = await api.sadeSati.calculate({
        name: details.name ?? "",
        dob: details.dob ?? "",
        time: details.time ?? "",
        place: details.place ?? "",
        reference_date: details.date || undefined,
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

  const timeline = result
    ? ([
        { key: "cycleStart", date: result.cycle_start },
        { key: "peakPhaseStart", date: result.peak_phase_start },
        { key: "settingPhaseStart", date: result.setting_phase_start },
        { key: "cycleEnd", date: result.cycle_end },
      ] as const)
    : [];

  return (
    <div className="grid gap-10 lg:grid-cols-[0.85fr_1.15fr] lg:items-start">
      <form
        onSubmit={handleSubmit}
        className="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-sm sm:p-8"
      >
        <BirthDetailsFields
          fields={["name", "dob", "time", "place", "date"]}
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
          <div className="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
            <p className="text-xs font-bold uppercase tracking-[0.18em] text-amber-700">
              {t("resultEyebrow")}
            </p>
            <h2 className="mt-2 text-2xl font-bold text-stone-950">
              {t("resultTitle", { sign: result.moon_sign })}
            </h2>

            <div
              className={`mt-5 rounded-2xl px-5 py-4 text-sm font-bold ${
                result.is_active
                  ? "bg-red-100 text-red-900"
                  : "bg-emerald-100 text-emerald-900"
              }`}
            >
              {result.is_active ? t("phaseActive") : t("phaseInactive")}
              <span className="mt-1 block text-xs font-semibold opacity-80">
                {t(`phases.${result.phase}`)}
              </span>
            </div>

            <ol className="mt-7 space-y-4">
              {timeline.map(({ key, date }) => (
                <li
                  key={key}
                  className="flex items-center justify-between rounded-2xl bg-amber-50 px-5 py-4"
                >
                  <span className="text-sm font-bold text-amber-950">
                    {t(key)}
                  </span>
                  <span className="text-sm font-semibold text-stone-700">
                    {date}
                  </span>
                </li>
              ))}
            </ol>
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
