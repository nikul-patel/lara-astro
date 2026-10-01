"use client";

import type { FormEvent } from "react";
import { useState } from "react";
import { useTranslations } from "next-intl";
import {
  BirthDetailsFields,
  type BirthDetailsValue,
} from "@/components/shared/birth-details-fields";
import { api, ApiError, type PanchangResult } from "@/lib/api";

type Status = "idle" | "loading" | "error";

export function PanchangTool() {
  const t = useTranslations("Panchang");
  const [details, setDetails] = useState<BirthDetailsValue>({
    date: "",
    place: "",
  });
  const [result, setResult] = useState<PanchangResult | null>(null);
  const [status, setStatus] = useState<Status>("idle");

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setStatus("loading");

    try {
      const response = await api.panchang.calculate({
        date: details.date || undefined,
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

  const limbs = result
    ? ([
        {
          key: "tithi",
          value: result.tithi.name,
          detail: result.tithi.paksha,
        },
        {
          key: "nakshatra",
          value: result.nakshatra.name,
          detail: t("pada", { pada: result.nakshatra.pada }),
        },
        { key: "yoga", value: result.yoga.name, detail: null },
        { key: "karana", value: result.karana, detail: null },
        {
          key: "vaar",
          value: result.vaar.name,
          detail: t("lord", { lord: result.vaar.lord }),
        },
      ] as const)
    : [];

  return (
    <div className="grid gap-10 lg:grid-cols-[0.85fr_1.15fr] lg:items-start">
      <form
        onSubmit={handleSubmit}
        className="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-sm sm:p-8"
      >
        <BirthDetailsFields
          fields={["date", "place"]}
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
            <h2 className="mt-2 text-3xl font-bold text-stone-950">
              {result.date}
            </h2>

            {!result.location_matched && (
              <p className="mt-4 rounded-2xl border border-blue-200 bg-blue-50 px-5 py-4 text-sm leading-6 text-blue-900">
                {t("locationApprox")}
              </p>
            )}

            <div className="mt-7 grid gap-5 sm:grid-cols-2">
              {limbs.map(({ key, value, detail }) => (
                <div key={key} className="rounded-3xl bg-amber-50 p-5">
                  <p className="text-sm font-bold text-amber-950">{t(key)}</p>
                  <p className="mt-2 text-xl font-bold text-amber-800">
                    {value}
                  </p>
                  {detail && (
                    <p className="mt-1 text-sm text-stone-600">{detail}</p>
                  )}
                </div>
              ))}
            </div>

            <div className="mt-5 grid gap-5 sm:grid-cols-2">
              <div className="rounded-3xl border border-stone-200 p-5">
                <p className="text-sm font-bold text-stone-800">
                  {t("sunrise")}
                </p>
                <p className="mt-2 text-xl font-bold text-stone-950">
                  {result.sunrise}
                </p>
              </div>
              <div className="rounded-3xl border border-stone-200 p-5">
                <p className="text-sm font-bold text-stone-800">
                  {t("sunset")}
                </p>
                <p className="mt-2 text-xl font-bold text-stone-950">
                  {result.sunset}
                </p>
              </div>
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
