"use client";

import type { FormEvent } from "react";
import { useState, useSyncExternalStore } from "react";
import { useTranslations } from "next-intl";
import { ChartSvg } from "@/components/birth-chart/chart-svg";
import {
  api,
  ApiError,
  type AstrologySystem,
  type ChartInput,
  type ChartResult,
  type ChartStyle,
} from "@/lib/api";
import {
  createDemoChart,
  getHouses,
  getPlanetaryPositions,
} from "@/lib/chart-display";

const PREFERENCE_KEY = "lara-astro-chart-preference";
const PREFERENCE_EVENT = "lara-astro-chart-preference-change";
const AUTH_TOKEN_KEY = "lara-astro-auth-token";
const PENDING_CHART_KEY = "lara-astro-pending-chart";

type ChartPreference = {
  system: AstrologySystem;
  chartStyle: ChartStyle;
};

type CompletedChart = {
  input: ChartInput;
  result: ChartResult;
  isDemo: boolean;
};

const placeSuggestions = [
  "Ahmedabad, Gujarat, India",
  "Surat, Gujarat, India",
  "Vadodara, Gujarat, India",
  "Rajkot, Gujarat, India",
  "New Delhi, India",
  "Mumbai, Maharashtra, India",
  "Bengaluru, Karnataka, India",
  "Chennai, Tamil Nadu, India",
  "Hyderabad, Telangana, India",
  "Kolkata, West Bengal, India",
  "London, United Kingdom",
  "New York, United States",
];

function subscribeToPreference(listener: () => void) {
  window.addEventListener("storage", listener);
  window.addEventListener(PREFERENCE_EVENT, listener);

  return () => {
    window.removeEventListener("storage", listener);
    window.removeEventListener(PREFERENCE_EVENT, listener);
  };
}

function parsePreference(value: string): ChartPreference {
  try {
    const parsed = JSON.parse(value) as Partial<ChartPreference>;
    return {
      system: parsed.system === "western" ? "western" : "vedic",
      chartStyle: ["north_indian", "south_indian", "east_indian"].includes(
        parsed.chartStyle ?? "",
      )
        ? (parsed.chartStyle as ChartStyle)
        : "north_indian",
    };
  } catch {
    return { system: "vedic", chartStyle: "north_indian" };
  }
}

export function BirthChartTool() {
  const t = useTranslations("BirthChart");
  const defaultPreference = JSON.stringify({
    system: "vedic",
    chartStyle: "north_indian",
  } satisfies ChartPreference);
  const preferenceValue = useSyncExternalStore(
    subscribeToPreference,
    () => window.localStorage.getItem(PREFERENCE_KEY) ?? defaultPreference,
    () => defaultPreference,
  );
  const preference = parsePreference(preferenceValue);
  const [details, setDetails] = useState({
    name: "",
    dob: "",
    time: "",
    place: "",
  });
  const [completedChart, setCompletedChart] =
    useState<CompletedChart | null>(null);
  const [pending, setPending] = useState(false);
  const [notice, setNotice] = useState<string | null>(null);
  const [actionStatus, setActionStatus] = useState<string | null>(null);

  function updatePreference(next: Partial<ChartPreference>) {
    const value = JSON.stringify({ ...preference, ...next });
    window.localStorage.setItem(PREFERENCE_KEY, value);
    window.dispatchEvent(new Event(PREFERENCE_EVENT));
  }

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setPending(true);
    setNotice(null);
    setActionStatus(null);

    const input: ChartInput = {
      ...details,
      system: preference.system,
      chart_style:
        preference.system === "vedic" ? preference.chartStyle : undefined,
    };

    try {
      const result = await api.charts.calculate(input);
      setCompletedChart({ input, result, isDemo: false });
    } catch (error) {
      if (error instanceof ApiError && error.status === 422) {
        setCompletedChart(null);
        setNotice(t("detailsError"));
      } else {
        setCompletedChart({ input, result: createDemoChart(input), isDemo: true });
        setNotice(t("demoNotice"));
      }
    } finally {
      setPending(false);
    }
  }

  async function saveChart() {
    if (!completedChart || completedChart.isDemo) return;
    const token = window.localStorage.getItem(AUTH_TOKEN_KEY);

    if (!token) {
      setActionStatus(t("signInRequired"));
      return;
    }

    try {
      await api.charts.save(
        { ...completedChart.input, result: completedChart.result },
        token,
      );
      setActionStatus(t("saved"));
    } catch {
      setActionStatus(t("saveError"));
    }
  }

  function attachToBooking() {
    if (!completedChart || completedChart.isDemo) return;
    window.sessionStorage.setItem(
      PENDING_CHART_KEY,
      JSON.stringify({
        input: completedChart.input,
        result: completedChart.result,
      }),
    );
    setActionStatus(t("attached"));
  }

  const isOverride =
    preference.system !== "vedic" ||
    (preference.system === "vedic" &&
      preference.chartStyle !== "north_indian");
  const planets = completedChart
    ? getPlanetaryPositions(completedChart.result.planetary_positions)
    : [];
  const houses = completedChart ? getHouses(completedChart.result.houses) : [];
  const apiRecommendation = completedChart?.result.recommendation;
  const apiRecommendationLabel = apiRecommendation
    ? `${t(`systems.${apiRecommendation.system}`)}${
        apiRecommendation.chart_style
          ? ` · ${t(`styles.${apiRecommendation.chart_style}`)}`
          : ""
      }`
    : null;

  return (
    <div className="grid gap-10 lg:grid-cols-[0.85fr_1.15fr] lg:items-start">
      <form
        onSubmit={handleSubmit}
        className="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-sm sm:p-8"
      >
        <div className="grid gap-5 sm:grid-cols-2">
          <label className="sm:col-span-2">
            <span className="text-sm font-bold text-stone-800">{t("name")}</span>
            <input
              required
              autoComplete="name"
              value={details.name}
              onChange={(event) =>
                setDetails({ ...details, name: event.target.value })
              }
              className="mt-2 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-stone-950 outline-none transition focus:border-amber-700 focus:ring-2 focus:ring-amber-100"
            />
          </label>
          <label>
            <span className="text-sm font-bold text-stone-800">{t("dob")}</span>
            <input
              required
              type="date"
              value={details.dob}
              onChange={(event) =>
                setDetails({ ...details, dob: event.target.value })
              }
              className="mt-2 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-stone-950 outline-none transition focus:border-amber-700 focus:ring-2 focus:ring-amber-100"
            />
          </label>
          <label>
            <span className="text-sm font-bold text-stone-800">{t("time")}</span>
            <input
              required
              type="time"
              value={details.time}
              onChange={(event) =>
                setDetails({ ...details, time: event.target.value })
              }
              className="mt-2 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-stone-950 outline-none transition focus:border-amber-700 focus:ring-2 focus:ring-amber-100"
            />
          </label>
          <label className="sm:col-span-2">
            <span className="text-sm font-bold text-stone-800">{t("place")}</span>
            <input
              required
              list="birth-place-suggestions"
              autoComplete="off"
              value={details.place}
              onChange={(event) =>
                setDetails({ ...details, place: event.target.value })
              }
              placeholder={t("placePlaceholder")}
              className="mt-2 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-stone-950 outline-none transition focus:border-amber-700 focus:ring-2 focus:ring-amber-100"
            />
            <datalist id="birth-place-suggestions">
              {placeSuggestions.map((place) => (
                <option key={place} value={place} />
              ))}
            </datalist>
            <span className="mt-2 block text-xs leading-5 text-stone-500">
              {t("placeHelp")}
            </span>
          </label>
        </div>

        <div className="mt-8 rounded-3xl bg-amber-50 p-5">
          <p className="text-sm font-bold text-amber-950">
            {t("recommendation")}
          </p>
          <p className="mt-1 text-sm leading-6 text-amber-900/70">
            {t("recommendationDescription")}
          </p>

          <fieldset className="mt-5">
            <legend className="text-xs font-bold uppercase tracking-wider text-stone-600">
              {t("system")}
            </legend>
            <div className="mt-2 grid grid-cols-2 gap-2">
              {(["vedic", "western"] as const).map((system) => (
                <button
                  key={system}
                  type="button"
                  aria-pressed={preference.system === system}
                  onClick={() => updatePreference({ system })}
                  className={`rounded-xl px-4 py-3 text-sm font-bold transition ${
                    preference.system === system
                      ? "bg-amber-800 text-white"
                      : "bg-white text-stone-700 hover:bg-amber-100"
                  }`}
                >
                  {t(`systems.${system}`)}
                </button>
              ))}
            </div>
          </fieldset>

          {preference.system === "vedic" && (
            <fieldset className="mt-5">
              <legend className="text-xs font-bold uppercase tracking-wider text-stone-600">
                {t("chartStyle")}
              </legend>
              <div className="mt-2 grid gap-2 sm:grid-cols-3">
                {(
                  ["north_indian", "south_indian", "east_indian"] as const
                ).map((chartStyle) => (
                  <button
                    key={chartStyle}
                    type="button"
                    aria-pressed={preference.chartStyle === chartStyle}
                    onClick={() => updatePreference({ chartStyle })}
                    className={`rounded-xl px-3 py-3 text-xs font-bold transition ${
                      preference.chartStyle === chartStyle
                        ? "bg-amber-800 text-white"
                        : "bg-white text-stone-700 hover:bg-amber-100"
                    }`}
                  >
                    {t(`styles.${chartStyle}`)}
                  </button>
                ))}
              </div>
            </fieldset>
          )}

          {isOverride && (
            <p className="mt-4 rounded-xl border border-amber-300 bg-white px-4 py-3 text-xs font-semibold text-amber-900">
              {t("overrideNotice")}
            </p>
          )}
        </div>

        <button
          type="submit"
          disabled={pending}
          className="mt-7 w-full rounded-full bg-amber-800 px-6 py-4 text-sm font-bold text-white transition hover:bg-amber-900 disabled:cursor-wait disabled:opacity-60"
        >
          {pending ? t("calculating") : t("calculate")}
        </button>
        <p className="mt-3 text-center text-xs leading-5 text-stone-500">
          {t("privacy")}
        </p>
        {notice && (
          <p className="mt-4 rounded-2xl border border-blue-200 bg-blue-50 px-5 py-4 text-sm leading-6 text-blue-900">
            {notice}
          </p>
        )}
      </form>

      <section aria-live="polite" className="min-w-0">
        {completedChart ? (
          <div className="space-y-7">
            <div className="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
              <div className="flex flex-wrap items-start justify-between gap-4">
                <div>
                  <p className="text-xs font-bold uppercase tracking-[0.18em] text-amber-700">
                    {t("resultEyebrow")}
                  </p>
                  <h2 className="mt-2 text-3xl font-bold text-stone-950">
                    {t("resultTitle", { name: completedChart.input.name })}
                  </h2>
                </div>
                <div className="rounded-2xl bg-amber-50 px-4 py-3 text-right text-xs text-stone-600">
                  <p className="font-bold text-amber-900">
                    {t(`systems.${completedChart.result.system}`)}
                  </p>
                  <p className="mt-1">
                    {completedChart.result.timezone || t("timezonePending")}
                  </p>
                </div>
              </div>
              <div className="mx-auto mt-7 max-w-xl">
                <ChartSvg
                  result={completedChart.result}
                  title={t("chartTitle")}
                />
              </div>
              {apiRecommendationLabel && (
                <p className="mt-5 rounded-2xl bg-stone-50 px-5 py-4 text-sm text-stone-600">
                  {t("apiRecommendation")}:{" "}
                  <strong className="text-stone-900">
                    {apiRecommendationLabel}
                  </strong>
                </p>
              )}
            </div>

            <div className="grid gap-6 xl:grid-cols-2">
              <div className="overflow-hidden rounded-3xl border border-stone-200 bg-white">
                <h3 className="border-b border-stone-200 px-6 py-4 font-bold text-stone-950">
                  {t("planets")}
                </h3>
                {planets.length > 0 ? (
                  <div className="overflow-x-auto">
                    <table className="w-full text-left text-sm">
                      <thead className="bg-stone-50 text-xs uppercase tracking-wider text-stone-500">
                        <tr>
                          <th className="px-5 py-3">{t("planet")}</th>
                          <th className="px-5 py-3">{t("sign")}</th>
                          <th className="px-5 py-3">{t("degree")}</th>
                        </tr>
                      </thead>
                      <tbody className="divide-y divide-stone-100">
                        {planets.map((planet) => (
                          <tr key={planet.name}>
                            <td className="px-5 py-3 font-semibold text-stone-900">
                              {planet.name}
                            </td>
                            <td className="px-5 py-3 text-stone-600">{planet.sign}</td>
                            <td className="px-5 py-3 text-stone-600">{planet.degree}</td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                ) : (
                  <p className="p-6 text-sm text-stone-500">{t("dataPending")}</p>
                )}
              </div>

              <div className="overflow-hidden rounded-3xl border border-stone-200 bg-white">
                <h3 className="border-b border-stone-200 px-6 py-4 font-bold text-stone-950">
                  {t("houses")}
                </h3>
                {houses.length > 0 ? (
                  <ul className="grid grid-cols-2 divide-x divide-y divide-stone-100 text-sm">
                    {houses.map((house) => (
                      <li key={house.number} className="p-4">
                        <p className="font-bold text-amber-800">
                          {t("house", { number: house.number })}
                        </p>
                        <p className="mt-1 text-stone-600">{house.sign}</p>
                        {house.planets.length > 0 && (
                          <p className="mt-1 text-xs text-stone-500">
                            {house.planets.join(", ")}
                          </p>
                        )}
                      </li>
                    ))}
                  </ul>
                ) : (
                  <p className="p-6 text-sm text-stone-500">{t("dataPending")}</p>
                )}
              </div>
            </div>

            <div className="rounded-3xl border border-stone-200 bg-white p-6">
              <h3 className="text-xl font-bold text-stone-950">{t("nextSteps")}</h3>
              <p className="mt-2 text-sm leading-6 text-stone-600">
                {t("nextStepsDescription")}
              </p>
              <div className="mt-5 flex flex-wrap gap-3">
                <button
                  type="button"
                  onClick={saveChart}
                  disabled={completedChart.isDemo}
                  className="rounded-full bg-amber-800 px-5 py-3 text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-40"
                >
                  {t("save")}
                </button>
                <button
                  type="button"
                  onClick={attachToBooking}
                  disabled={completedChart.isDemo}
                  className="rounded-full border border-amber-800/20 px-5 py-3 text-sm font-bold text-amber-900 disabled:cursor-not-allowed disabled:opacity-40"
                >
                  {t("attach")}
                </button>
              </div>
              {completedChart.isDemo && (
                <p className="mt-3 text-xs text-stone-500">{t("demoActionsDisabled")}</p>
              )}
              {actionStatus && (
                <p className="mt-4 text-sm font-semibold text-amber-800">
                  {actionStatus}
                </p>
              )}
            </div>
          </div>
        ) : (
          <div className="grid min-h-[32rem] place-items-center rounded-[2rem] border border-dashed border-amber-800/25 bg-amber-50 p-8 text-center">
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
