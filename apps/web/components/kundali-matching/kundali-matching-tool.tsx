"use client";

import type { FormEvent } from "react";
import { useState } from "react";
import { useTranslations } from "next-intl";
import {
  BirthDetailsFields,
  type BirthDetailsValue,
} from "@/components/shared/birth-details-fields";
import { api, ApiError, type KundaliMatchingResult } from "@/lib/api";

type Status = "idle" | "loading" | "error";

const FULL_FIELDS = ["name", "dob", "time", "place"] as const;

export function KundaliMatchingTool() {
  const t = useTranslations("KundaliMatching");
  const [bride, setBride] = useState<BirthDetailsValue>({
    name: "",
    dob: "",
    time: "",
    place: "",
  });
  const [groom, setGroom] = useState<BirthDetailsValue>({
    name: "",
    dob: "",
    time: "",
    place: "",
  });
  const [result, setResult] = useState<KundaliMatchingResult | null>(null);
  const [status, setStatus] = useState<Status>("idle");

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setStatus("loading");

    try {
      const response = await api.kundaliMatching.calculate({
        bride: {
          name: bride.name ?? "",
          dob: bride.dob ?? "",
          time: bride.time ?? "",
          place: bride.place ?? "",
        },
        groom: {
          name: groom.name ?? "",
          dob: groom.dob ?? "",
          time: groom.time ?? "",
          place: groom.place ?? "",
        },
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

  return (
    <div className="grid gap-10 lg:grid-cols-[0.85fr_1.15fr] lg:items-start">
      <form
        onSubmit={handleSubmit}
        className="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-sm sm:p-8"
      >
        <fieldset>
          <legend className="text-sm font-bold uppercase tracking-wider text-amber-800">
            {t("bride")}
          </legend>
          <div className="mt-3">
            <BirthDetailsFields
              fields={FULL_FIELDS}
              value={bride}
              onChange={setBride}
              t={t}
              idPrefix="bride-"
            />
          </div>
        </fieldset>

        <fieldset className="mt-8">
          <legend className="text-sm font-bold uppercase tracking-wider text-amber-800">
            {t("groom")}
          </legend>
          <div className="mt-3">
            <BirthDetailsFields
              fields={FULL_FIELDS}
              value={groom}
              onChange={setGroom}
              t={t}
              idPrefix="groom-"
            />
          </div>
        </fieldset>

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
            <div className="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
              <p className="text-xs font-bold uppercase tracking-[0.18em] text-amber-700">
                {t("resultEyebrow")}
              </p>
              <h2 className="mt-2 text-4xl font-bold text-stone-950">
                {t("resultTitle", {
                  total: result.total_points,
                  max: result.max_points,
                })}
              </h2>
              <p className="mt-1 text-sm text-stone-500">
                {t("minimumNote", { minimum: result.minimum_recommended })}
              </p>

              <span
                className={`mt-4 inline-block rounded-full px-4 py-1.5 text-xs font-bold ${
                  result.is_recommended
                    ? "bg-emerald-100 text-emerald-900"
                    : "bg-red-100 text-red-900"
                }`}
              >
                {result.is_recommended ? t("recommended") : t("notRecommended")}
              </span>

              {result.has_nadi_dosha && (
                <p className="mt-4 rounded-2xl bg-red-50 px-5 py-4 text-sm leading-6 text-red-950">
                  {t("nadiDoshaWarning")}
                </p>
              )}
              {result.has_bhakoot_dosha && (
                <p className="mt-3 rounded-2xl bg-amber-50 px-5 py-4 text-sm leading-6 text-amber-950">
                  {t("bhakootDoshaWarning")}
                </p>
              )}

              <div className="mt-6 grid gap-3 sm:grid-cols-2">
                <p className="rounded-2xl bg-stone-50 px-5 py-4 text-sm text-stone-700">
                  {t("brideSummary", {
                    rashi: result.bride.rashi,
                    nakshatra: result.bride.nakshatra.name,
                  })}
                </p>
                <p className="rounded-2xl bg-stone-50 px-5 py-4 text-sm text-stone-700">
                  {t("groomSummary", {
                    rashi: result.groom.rashi,
                    nakshatra: result.groom.nakshatra.name,
                  })}
                </p>
              </div>
            </div>

            <div className="overflow-hidden rounded-3xl border border-stone-200 bg-white">
              <table className="w-full text-left text-sm">
                <thead className="bg-stone-50 text-xs uppercase tracking-wider text-stone-500">
                  <tr>
                    <th className="px-5 py-3">Koota</th>
                    <th className="px-5 py-3">Points</th>
                    <th className="px-5 py-3">Description</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-stone-100">
                  {result.kootas.map((koota) => (
                    <tr key={koota.name}>
                      <td className="px-5 py-3 font-semibold text-stone-900">
                        {koota.name}
                      </td>
                      <td className="px-5 py-3 font-bold text-amber-800">
                        {t("kootaPoints", {
                          points: koota.points,
                          max: koota.max_points,
                        })}
                      </td>
                      <td className="px-5 py-3 text-stone-600">
                        {koota.description}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
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
