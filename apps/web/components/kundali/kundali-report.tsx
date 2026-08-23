"use client";

import { useCallback, useEffect, useState, useSyncExternalStore } from "react";
import { useTranslations } from "next-intl";
import { Link } from "@/i18n/navigation";
import {
  api,
  ApiError,
  type Mahadasha,
  type Predictions,
  type Remedy,
  type SavedChart,
  type SimplifiedForecastResult,
  type TransitInfo,
  type VarshphalForecastResult,
  type YearWiseForecast,
  type YearWiseStyle,
  type Yoga,
} from "@/lib/api";

const AUTH_TOKEN_KEY = "lara-astro-auth-token";

function subscribeAuth(listener: () => void) {
  const handleStorage = (event: StorageEvent) => {
    if (event.key === AUTH_TOKEN_KEY) listener();
  };
  window.addEventListener("storage", handleStorage);
  return () => window.removeEventListener("storage", handleStorage);
}

function getAuthToken() {
  return window.localStorage.getItem(AUTH_TOKEN_KEY);
}

export function KundaliReport({ chartId }: { chartId: number }) {
  const t = useTranslations("Kundali");
  const token = useSyncExternalStore(subscribeAuth, getAuthToken, () => null);
  const [chart, setChart] = useState<SavedChart | null>(null);
  const [status, setStatus] = useState<"idle" | "loading" | "error" | "notFound">("idle");

  const loadChart = useCallback(
    async (activeToken: string) => {
      setStatus("loading");
      try {
        const result = await api.charts.get(chartId, activeToken);
        setChart(result);
        setStatus("idle");
      } catch (error) {
        setStatus(error instanceof ApiError && error.status === 404 ? "notFound" : "error");
      }
    },
    [chartId],
  );

  useEffect(() => {
    if (!token) return;
    // Fetch the chart once the auth token becomes available.
    // eslint-disable-next-line react-hooks/set-state-in-effect
    loadChart(token);
  }, [token, loadChart]);

  if (!token) {
    return (
      <Notice
        message={t("signInRequired")}
        actionLabel={t("goToAccount")}
      />
    );
  }

  if (status === "notFound") {
    return <Notice message={t("notFound")} actionLabel={t("goToAccount")} />;
  }

  if (status === "error") {
    return (
      <Notice
        message={t("loadError")}
        actionLabel={t("retry")}
        onAction={() => loadChart(token)}
      />
    );
  }

  if (!chart) {
    return (
      <p className="rounded-3xl bg-white p-8 text-center text-stone-600" aria-live="polite">
        {t("loading")}
      </p>
    );
  }

  const result = chart.result;
  const isVedic = result.system === "vedic";

  return (
    <div className="space-y-10">
      <div className="flex flex-wrap items-center justify-between gap-4">
        <div>
          <h2 className="text-2xl font-bold text-stone-950">{chart.name}</h2>
          <p className="mt-1 text-sm text-stone-500">
            {chart.input.dob} &middot; {chart.input.time} &middot; {chart.input.place}
          </p>
        </div>
        <PdfDownloadButton chartId={chartId} token={token} />
      </div>

      <ChartSummary result={result} />

      {!isVedic && (
        <p className="rounded-2xl bg-amber-50 p-5 text-sm text-amber-900">
          {t("summary.westernNotice")}
        </p>
      )}

      {result.dasha?.mahadasha && <DashaTimeline mahadasha={result.dasha.mahadasha} />}
      {result.yogas && <YogasList yogas={result.yogas} />}
      {result.predictions && <PredictionsSections predictions={result.predictions} />}
      {result.remedies && <RemediesSection remedies={result.remedies} />}
      {isVedic && <YearWiseForecastSection chartId={chartId} token={token} />}
    </div>
  );
}

function Notice({
  message,
  actionLabel,
  onAction,
}: {
  message: string;
  actionLabel: string;
  onAction?: () => void;
}) {
  return (
    <div className="rounded-3xl border border-stone-200 bg-white p-8 text-center">
      <p role="alert" className="text-stone-600">
        {message}
      </p>
      {onAction ? (
        <button
          type="button"
          onClick={onAction}
          className="mt-5 rounded-full bg-amber-800 px-5 py-3 text-sm font-bold text-white"
        >
          {actionLabel}
        </button>
      ) : (
        <Link
          href="/account"
          className="mt-5 inline-flex rounded-full bg-amber-800 px-5 py-3 text-sm font-bold text-white"
        >
          {actionLabel}
        </Link>
      )}
    </div>
  );
}

export function PdfDownloadButton({ chartId, token }: { chartId: number; token: string }) {
  const t = useTranslations("Kundali");
  const [pending, setPending] = useState(false);
  const [error, setError] = useState(false);

  async function handleDownload() {
    setPending(true);
    setError(false);
    try {
      const { blob, filename } = await api.charts.downloadReport(chartId, { token });
      const url = URL.createObjectURL(blob);
      const anchor = document.createElement("a");
      anchor.href = url;
      anchor.download = filename ?? "kundali-report.pdf";
      document.body.appendChild(anchor);
      anchor.click();
      anchor.remove();
      URL.revokeObjectURL(url);
    } catch {
      setError(true);
    } finally {
      setPending(false);
    }
  }

  return (
    <div>
      <button
        type="button"
        onClick={handleDownload}
        disabled={pending}
        className="rounded-full bg-amber-800 px-5 py-3 text-sm font-bold text-white disabled:opacity-50"
      >
        {pending ? t("downloading") : t("downloadPdf")}
      </button>
      {error && (
        <p role="alert" className="mt-2 text-sm text-red-700">
          {t("downloadError")}
        </p>
      )}
    </div>
  );
}

function ChartSummary({ result }: { result: SavedChart["result"] }) {
  const t = useTranslations("Kundali");

  return (
    <section>
      <h2 className="text-xl font-bold text-stone-950">{t("sections.summary")}</h2>
      <dl className="mt-4 grid gap-4 sm:grid-cols-2">
        {result.ascendant && (
          <SummaryItem
            label={t("summary.ascendant")}
            value={`${result.ascendant.sign} (${result.ascendant.degree})`}
          />
        )}
        <SummaryItem label={t("summary.timezone")} value={result.timezone} />
        {result.nakshatra && (
          <>
            <SummaryItem
              label={t("summary.nakshatra")}
              value={`${result.nakshatra.name}, ${t("summary.pada")} ${result.nakshatra.pada}`}
            />
            <SummaryItem label={t("summary.nakshatraLord")} value={result.nakshatra.lord} />
          </>
        )}
      </dl>
    </section>
  );
}

function SummaryItem({ label, value }: { label: string; value: string }) {
  return (
    <div className="rounded-2xl border border-stone-200 bg-white p-4">
      <dt className="text-xs font-bold uppercase tracking-wide text-stone-500">{label}</dt>
      <dd className="mt-1 text-sm font-semibold text-stone-900">{value}</dd>
    </div>
  );
}

function DashaTimeline({ mahadasha }: { mahadasha: Mahadasha[] }) {
  const t = useTranslations("Kundali");
  const [expandedIndex, setExpandedIndex] = useState<number | null>(null);
  const today = new Date().toISOString().slice(0, 10);

  return (
    <section>
      <h2 className="text-xl font-bold text-stone-950">{t("sections.dasha")}</h2>
      <div className="mt-4 space-y-3">
        {mahadasha.map((period, index) => {
          const isCurrent = today >= period.start && today < period.end;
          const isExpanded = expandedIndex === index;

          return (
            <div
              key={`${period.lord}-${index}`}
              className={`rounded-2xl border p-4 ${isCurrent ? "border-amber-600 bg-amber-50" : "border-stone-200 bg-white"}`}
            >
              <div className="flex flex-wrap items-center justify-between gap-3">
                <div>
                  <p className="font-bold text-stone-950">
                    {period.lord} {t("dasha.mahadasha")}
                    {isCurrent && (
                      <span className="ml-2 rounded-full bg-amber-800 px-2 py-0.5 text-xs font-bold text-white">
                        {t("dasha.current")}
                      </span>
                    )}
                  </p>
                  <p className="text-sm text-stone-500">
                    {period.start} &ndash; {period.end}
                  </p>
                </div>
                <button
                  type="button"
                  onClick={() => setExpandedIndex(isExpanded ? null : index)}
                  className="text-sm font-bold text-amber-800"
                >
                  {isExpanded ? t("dasha.hideAntardasha") : t("dasha.viewAntardasha")}
                </button>
              </div>
              {isExpanded && (
                <table className="mt-4 w-full text-left text-sm">
                  <tbody>
                    {period.antardashas.map((antardasha, subIndex) => {
                      const isCurrentSub = today >= antardasha.start && today < antardasha.end;

                      return (
                        <tr
                          key={subIndex}
                          className={isCurrentSub ? "font-bold text-amber-900" : "text-stone-700"}
                        >
                          <td className="py-1 pr-4">{antardasha.lord}</td>
                          <td className="py-1 text-stone-500">
                            {antardasha.start} &ndash; {antardasha.end}
                          </td>
                        </tr>
                      );
                    })}
                  </tbody>
                </table>
              )}
            </div>
          );
        })}
      </div>
    </section>
  );
}

function YogasList({ yogas }: { yogas: Yoga[] }) {
  const t = useTranslations("Kundali");

  return (
    <section>
      <h2 className="text-xl font-bold text-stone-950">{t("sections.yogas")}</h2>
      {yogas.length === 0 ? (
        <p className="mt-3 text-stone-600">{t("yogas.empty")}</p>
      ) : (
        <ul className="mt-4 space-y-3">
          {yogas.map((yoga, index) => (
            <li key={`${yoga.key}-${index}`} className="rounded-2xl border border-stone-200 bg-white p-4">
              <p className="font-bold text-stone-950">{yoga.name}</p>
              <p className="mt-1 text-sm text-stone-600">{yoga.description}</p>
            </li>
          ))}
        </ul>
      )}
    </section>
  );
}

function PredictionsSections({ predictions }: { predictions: Predictions }) {
  const t = useTranslations("Kundali");
  const areas: Array<[keyof Predictions, string]> = [
    ["marriage", t("predictions.marriage")],
    ["career", t("predictions.career")],
    ["education", t("predictions.education")],
    ["foreign_settlement", t("predictions.foreignSettlement")],
  ];

  return (
    <section>
      <h2 className="text-xl font-bold text-stone-950">{t("sections.predictions")}</h2>
      <div className="mt-4 grid gap-4 sm:grid-cols-2">
        {areas.map(([key, label]) => (
          <div key={key} className="rounded-2xl border border-stone-200 bg-white p-5">
            <h3 className="font-bold text-amber-900">{label}</h3>
            <p className="mt-2 text-sm leading-6 text-stone-700">{predictions[key].text}</p>
          </div>
        ))}
      </div>
    </section>
  );
}

function RemediesSection({ remedies }: { remedies: Remedy[] }) {
  const t = useTranslations("Kundali");

  return (
    <section>
      <h2 className="text-xl font-bold text-stone-950">{t("sections.remedies")}</h2>
      {remedies.length === 0 ? (
        <p className="mt-3 text-stone-600">{t("remedies.empty")}</p>
      ) : (
        <>
          <div className="mt-4 space-y-3">
            {remedies.map((remedy) => (
              <div key={remedy.planet} className="rounded-2xl border border-stone-200 bg-white p-5">
                <p className="font-bold text-stone-950">{remedy.planet}</p>
                <dl className="mt-3 grid gap-2 text-sm sm:grid-cols-2">
                  <div>
                    <dt className="text-stone-500">{t("remedies.gemstone")}</dt>
                    <dd>{remedy.gemstone}</dd>
                  </div>
                  <div>
                    <dt className="text-stone-500">{t("remedies.mantra")}</dt>
                    <dd>{remedy.mantra}</dd>
                  </div>
                  <div>
                    <dt className="text-stone-500">{t("remedies.donation")}</dt>
                    <dd>{remedy.donation}</dd>
                  </div>
                  <div>
                    <dt className="text-stone-500">{t("remedies.fastingDay")}</dt>
                    <dd>{remedy.fasting_day}</dd>
                  </div>
                </dl>
              </div>
            ))}
          </div>
          <p className="mt-4 text-xs text-stone-500">{remedies[0].caution_note}</p>
        </>
      )}
    </section>
  );
}

function YearWiseForecastSection({ chartId, token }: { chartId: number; token: string }) {
  const t = useTranslations("Kundali");
  const [style, setStyle] = useState<YearWiseStyle>("simplified");
  const [year, setYear] = useState(new Date().getFullYear());
  const [forecast, setForecast] = useState<YearWiseForecast | null>(null);
  const [status, setStatus] = useState<"idle" | "loading" | "error">("idle");

  async function load() {
    setStatus("loading");
    try {
      const result = await api.yearWise.forSavedChart(chartId, { year, style }, token);
      setForecast(result);
      setStatus("idle");
    } catch {
      setStatus("error");
    }
  }

  return (
    <section>
      <h2 className="text-xl font-bold text-stone-950">{t("sections.yearWise")}</h2>
      <div className="mt-4 flex flex-wrap items-end gap-4">
        <label className="block">
          <span className="text-sm font-bold text-stone-700">{t("yearWise.style")}</span>
          <select
            value={style}
            onChange={(event) => setStyle(event.target.value as YearWiseStyle)}
            className="mt-2 rounded-xl border border-stone-300 px-4 py-2"
          >
            <option value="simplified">{t("yearWise.simplified")}</option>
            <option value="varshphal">{t("yearWise.varshphal")}</option>
          </select>
        </label>
        <label className="block">
          <span className="text-sm font-bold text-stone-700">{t("yearWise.year")}</span>
          <input
            type="number"
            value={year}
            onChange={(event) => setYear(Number(event.target.value))}
            className="mt-2 w-28 rounded-xl border border-stone-300 px-4 py-2"
          />
        </label>
        <button
          type="button"
          onClick={load}
          disabled={status === "loading"}
          className="rounded-full bg-amber-800 px-5 py-3 text-sm font-bold text-white disabled:opacity-50"
        >
          {status === "loading" ? t("yearWise.loading") : t("yearWise.load")}
        </button>
      </div>

      {status === "error" && (
        <p role="alert" className="mt-4 text-sm text-red-700">
          {t("yearWise.error")}
        </p>
      )}

      {forecast?.style === "simplified" && (
        <SimplifiedForecastView result={forecast.result as SimplifiedForecastResult} />
      )}
      {forecast?.style === "varshphal" && (
        <VarshphalForecastView result={forecast.result as VarshphalForecastResult} />
      )}
    </section>
  );
}

function SimplifiedForecastView({ result }: { result: SimplifiedForecastResult }) {
  const t = useTranslations("Kundali");

  return (
    <div className="mt-5 space-y-4">
      <div className="grid gap-4 sm:grid-cols-2">
        <TransitCard label={t("yearWise.jupiterTransit")} transit={result.jupiter_transit} />
        <TransitCard label={t("yearWise.saturnTransit")} transit={result.saturn_transit} />
      </div>
      {result.governing_dasha.length > 0 && (
        <div>
          <h3 className="font-bold text-stone-950">{t("yearWise.governingDasha")}</h3>
          <ul className="mt-2 space-y-1 text-sm text-stone-700">
            {result.governing_dasha.map((period, index) => (
              <li key={index}>
                {period.mahadasha_lord} / {period.antardasha_lord}: {period.start} &ndash; {period.end}
              </li>
            ))}
          </ul>
        </div>
      )}
    </div>
  );
}

function TransitCard({ label, transit }: { label: string; transit: TransitInfo }) {
  const t = useTranslations("Kundali");

  return (
    <div className="rounded-2xl border border-stone-200 bg-white p-4">
      <p className="text-xs font-bold uppercase tracking-wide text-stone-500">{label}</p>
      <p className="mt-1 font-bold text-stone-950">{transit.sign}</p>
      <p className="text-sm text-stone-500">
        {t("yearWise.fromAscendant", { house: transit.house_from_ascendant })}
      </p>
      <p className="text-sm text-stone-500">
        {t("yearWise.fromMoon", { house: transit.house_from_moon })}
      </p>
    </div>
  );
}

function VarshphalForecastView({ result }: { result: VarshphalForecastResult }) {
  const t = useTranslations("Kundali");

  return (
    <div className="mt-5 space-y-4">
      <dl className="grid gap-4 sm:grid-cols-2">
        <SummaryItem
          label={t("yearWise.solarReturn")}
          value={new Date(result.solar_return_moment).toLocaleString()}
        />
        <SummaryItem
          label={t("summary.ascendant")}
          value={`${result.ascendant.sign} (${result.ascendant.degree})`}
        />
        <SummaryItem label={t("yearWise.muntha")} value={`${result.muntha.sign} (${result.muntha.lord})`} />
        <SummaryItem label={t("yearWise.varshesh")} value={result.varshesh.lord} />
      </dl>
      <div>
        <h3 className="font-bold text-stone-950">{t("yearWise.sahams")}</h3>
        <ul className="mt-2 grid gap-1 text-sm text-stone-700 sm:grid-cols-2">
          {result.sahams.map((saham) => (
            <li key={saham.key}>
              {saham.name}: {saham.sign}
            </li>
          ))}
        </ul>
      </div>
    </div>
  );
}
