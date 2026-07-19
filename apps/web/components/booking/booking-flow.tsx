"use client";

import type { FormEvent } from "react";
import { useEffect, useMemo, useState } from "react";
import { useLocale, useTranslations } from "next-intl";
import { useCurrency } from "@/components/site/currency-provider";
import {
  api,
  ApiError,
  type Astrologer,
  type AvailabilitySlot,
  type Booking,
  type BirthDetails,
  type SavedChart,
  type Service,
} from "@/lib/api";

const AUTH_TOKEN_KEY = "lara-astro-auth-token";
const DAY_MS = 24 * 60 * 60 * 1000;

type BookingFlowProps = {
  astrologers: Astrologer[];
  services: Service[];
  upiId: string | null;
  upiQrUrl: string | null;
  initialAstrologerId?: number;
  initialServiceId?: number;
};

type ContactDetails = {
  name: string;
  email: string;
  phone: string;
  dob: string;
  time: string;
  place: string;
};

function normalizeSlots(value: unknown): AvailabilitySlot[] {
  const items = Array.isArray(value)
    ? value
    : value && typeof value === "object" && "data" in value
      ? (value as { data?: unknown }).data
      : [];

  if (!Array.isArray(items)) return [];

  return items.filter(
    (slot): slot is AvailabilitySlot =>
      Boolean(
        slot &&
          typeof slot === "object" &&
          "start" in slot &&
          typeof slot.start === "string" &&
          (!(("available" in slot)) || slot.available !== false),
      ),
  );
}

function createDemoSlots(): AvailabilitySlot[] {
  const slots: AvailabilitySlot[] = [];
  const now = new Date();

  for (let dayOffset = 1; dayOffset <= 5; dayOffset += 1) {
    const day = new Date(now.getTime() + dayOffset * DAY_MS);
    if (day.getDay() === 0) continue;

    for (const hour of [10, 12, 16]) {
      const start = new Date(day);
      start.setHours(hour, 0, 0, 0);
      const end = new Date(start.getTime() + 60 * 60 * 1000);
      slots.push({ start: start.toISOString(), end: end.toISOString(), available: true });
    }
  }

  return slots;
}

function createDemoBooking(
  astrologerId: number,
  serviceId: number,
  slot: string,
  contact: ContactDetails,
  birthDetails: BirthDetails | undefined,
  birthChartId: number | undefined,
): Booking {
  return {
    id: -1,
    astrologer_id: astrologerId,
    service_id: serviceId,
    slot,
    status: "pending_payment",
    reference_number: `DEMO-${new Date().getFullYear()}-0001`,
    client: { id: -1, name: contact.name, email: contact.email, phone: contact.phone },
    birth_details: birthDetails,
    birth_chart_id: birthChartId,
  };
}

export function BookingFlow({
  astrologers,
  services,
  upiId,
  upiQrUrl,
  initialAstrologerId,
  initialServiceId,
}: BookingFlowProps) {
  const t = useTranslations("Booking");
  const locale = useLocale();
  const { formatPrice } = useCurrency();
  const [step, setStep] = useState(1);
  const [astrologerId, setAstrologerId] = useState<number | null>(
    initialAstrologerId ?? null,
  );
  const [serviceId, setServiceId] = useState<number | null>(
    initialServiceId ?? null,
  );
  const [slot, setSlot] = useState("");
  const [slots, setSlots] = useState<AvailabilitySlot[]>([]);
  const [slotsPending, setSlotsPending] = useState(false);
  const [savedCharts, setSavedCharts] = useState<SavedChart[]>([]);
  const [birthChartId, setBirthChartId] = useState<number | undefined>();
  const [includeBirthDetails, setIncludeBirthDetails] = useState(false);
  const [contact, setContact] = useState<ContactDetails>({
    name: "",
    email: "",
    phone: "",
    dob: "",
    time: "",
    place: "",
  });
  const [pending, setPending] = useState(false);
  const [notice, setNotice] = useState<string | null>(null);
  const [booking, setBooking] = useState<Booking | null>(null);
  const [isDemoBooking, setIsDemoBooking] = useState(false);

  const selectedAstrologer = astrologers.find((item) => item.id === astrologerId);
  const selectedService = services.find((item) => item.id === serviceId);
  const availableServices = useMemo(
    () => services.filter((service) => service.astrologer_id === astrologerId),
    [astrologerId, services],
  );

  useEffect(() => {
    const token = window.localStorage.getItem(AUTH_TOKEN_KEY);
    if (!token) return;

    api.charts.mine(token).then(setSavedCharts).catch(() => setSavedCharts([]));
  }, []);

  async function loadAvailability() {
    if (!astrologerId || !serviceId) return;
    setSlotsPending(true);
    setNotice(null);
    setSlot("");

    if (astrologerId < 0 || serviceId < 0) {
      setSlots(createDemoSlots());
      setNotice(t("demoAvailability"));
      setSlotsPending(false);
      setStep(3);
      return;
    }

    const from = new Date();
    const to = new Date(from.getTime() + 14 * DAY_MS);

    try {
      const response = await api.availability({
        astrologer_id: astrologerId,
        service_id: serviceId,
        from: from.toISOString(),
        to: to.toISOString(),
      });
      setSlots(normalizeSlots(response));
      setStep(3);
    } catch {
      setSlots([]);
      setNotice(t("availabilityError"));
    } finally {
      setSlotsPending(false);
    }
  }

  async function submitBooking(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    if (!astrologerId || !serviceId || !slot) return;
    setPending(true);
    setNotice(null);

    const birthDetails = includeBirthDetails
      ? { dob: contact.dob, time: contact.time, place: contact.place }
      : undefined;
    const token = window.localStorage.getItem(AUTH_TOKEN_KEY) ?? undefined;

    if (astrologerId < 0 || serviceId < 0) {
      setBooking(
        createDemoBooking(
          astrologerId,
          serviceId,
          slot,
          contact,
          birthDetails,
          birthChartId,
        ),
      );
      setIsDemoBooking(true);
      setPending(false);
      return;
    }

    try {
      const result = await api.bookings.create(
        {
          astrologer_id: astrologerId,
          service_id: serviceId,
          slot,
          client: { name: contact.name, email: contact.email, phone: contact.phone },
          birth_details: birthDetails,
          birth_chart_id: birthChartId,
          guest: !token,
        },
        token,
      );
      setBooking(result);
      setIsDemoBooking(false);
    } catch (error) {
      setNotice(
        error instanceof ApiError && error.status === 422
          ? error.message
          : t("bookingError"),
      );
    } finally {
      setPending(false);
    }
  }

  if (booking) {
    const confirmationUpiId = booking.upi_id ?? upiId;
    const confirmationQrUrl = booking.upi_qr_url ?? upiQrUrl;

    return (
      <section className="mx-auto max-w-3xl rounded-[2rem] border border-stone-200 bg-white p-7 shadow-sm sm:p-10" aria-live="polite">
        <div className="grid size-14 place-items-center rounded-full bg-emerald-100 text-2xl text-emerald-800" aria-hidden="true">✓</div>
        <p className="mt-6 text-sm font-bold uppercase tracking-[0.18em] text-amber-700">{t("confirmationEyebrow")}</p>
        <h2 className="mt-2 text-3xl font-bold text-stone-950">{isDemoBooking ? t("demoConfirmationTitle") : t("confirmationTitle")}</h2>
        <p className="mt-4 text-stone-600">{isDemoBooking ? t("demoConfirmationDescription") : t("confirmationDescription")}</p>

        <dl className="mt-8 grid gap-4 rounded-3xl bg-stone-50 p-6 sm:grid-cols-2">
          <div><dt className="text-xs font-bold uppercase tracking-wider text-stone-500">{t("reference")}</dt><dd className="mt-1 font-bold text-stone-950">{booking.reference_number}</dd></div>
          <div><dt className="text-xs font-bold uppercase tracking-wider text-stone-500">{t("status")}</dt><dd className="mt-1 font-bold text-amber-800">{t("pendingPayment")}</dd></div>
          <div><dt className="text-xs font-bold uppercase tracking-wider text-stone-500">{t("consultation")}</dt><dd className="mt-1 text-stone-800">{selectedAstrologer?.name} · {selectedService?.name}</dd></div>
          <div><dt className="text-xs font-bold uppercase tracking-wider text-stone-500">{t("scheduledFor")}</dt><dd className="mt-1 text-stone-800">{new Intl.DateTimeFormat(locale, { dateStyle: "medium", timeStyle: "short" }).format(new Date(booking.slot))}</dd></div>
        </dl>

        {!isDemoBooking && confirmationUpiId ? (
          <div className="mt-8 grid items-center gap-7 rounded-3xl border border-amber-200 bg-amber-50 p-6 sm:grid-cols-[1fr_auto]">
            <div>
              <h3 className="text-xl font-bold text-amber-950">{t("paymentTitle")}</h3>
              <p className="mt-2 text-sm leading-6 text-amber-900/75">{t("paymentDescription", { reference: booking.reference_number })}</p>
              <p className="mt-4 rounded-xl bg-white px-4 py-3 font-bold text-amber-900">{confirmationUpiId}</p>
            </div>
            {confirmationQrUrl && (
              // The tenant-controlled QR URL comes from the Settings/booking API.
              // eslint-disable-next-line @next/next/no-img-element
              <img src={confirmationQrUrl} alt={t("qrAlt")} className="size-40 rounded-2xl border border-amber-200 bg-white object-contain p-2" />
            )}
          </div>
        ) : !isDemoBooking ? (
          <p className="mt-8 rounded-2xl border border-blue-200 bg-blue-50 px-5 py-4 text-sm text-blue-900">{t("paymentUnavailable")}</p>
        ) : null}

        <ol className="mt-8 list-decimal space-y-2 pl-5 text-sm leading-6 text-stone-600">
          <li>{isDemoBooking ? t("demoNextStep") : t("nextStepPay")}</li>
          <li>{t("nextStepReview")}</li>
          <li>{t("nextStepConfirmed")}</li>
        </ol>
      </section>
    );
  }

  const stepLabels = [t("steps.astrologer"), t("steps.service"), t("steps.slot"), t("steps.details")];

  return (
    <div className="grid gap-8 lg:grid-cols-[0.7fr_1.3fr] lg:items-start">
      <aside className="rounded-3xl border border-stone-200 bg-white p-6 lg:sticky lg:top-28">
        <ol className="space-y-3">
          {stepLabels.map((label, index) => {
            const number = index + 1;
            return (
              <li key={label} className={`flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-bold ${step === number ? "bg-amber-800 text-white" : number < step ? "bg-amber-50 text-amber-900" : "text-stone-400"}`}>
                <span className={`grid size-7 place-items-center rounded-full text-xs ${step === number ? "bg-white text-amber-900" : "bg-stone-100 text-stone-600"}`}>{number < step ? "✓" : number}</span>
                {label}
              </li>
            );
          })}
        </ol>
        {(selectedAstrologer || selectedService || slot) && (
          <dl className="mt-6 space-y-3 border-t border-stone-200 pt-6 text-sm">
            {selectedAstrologer && <div><dt className="text-stone-500">{t("summaryAstrologer")}</dt><dd className="font-bold text-stone-900">{selectedAstrologer.name}</dd></div>}
            {selectedService && <div><dt className="text-stone-500">{t("summaryService")}</dt><dd className="font-bold text-stone-900">{selectedService.name} · {formatPrice(selectedService.price_inr, selectedService.price_usd)}</dd></div>}
            {slot && <div><dt className="text-stone-500">{t("summarySlot")}</dt><dd className="font-bold text-stone-900">{new Intl.DateTimeFormat(locale, { dateStyle: "medium", timeStyle: "short" }).format(new Date(slot))}</dd></div>}
          </dl>
        )}
      </aside>

      <section className="rounded-[2rem] border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
        {step === 1 && (
          <div>
            <h2 className="text-2xl font-bold text-stone-950">{t("chooseAstrologer")}</h2>
            <p className="mt-2 text-sm text-stone-600">{t("chooseAstrologerDescription")}</p>
            <div className="mt-6 grid gap-3 sm:grid-cols-2">
              {astrologers.map((astrologer) => (
                <button key={astrologer.id} type="button" aria-pressed={astrologerId === astrologer.id} onClick={() => { setAstrologerId(astrologer.id); setServiceId(null); setSlot(""); }} className={`rounded-2xl border p-5 text-left transition ${astrologerId === astrologer.id ? "border-amber-700 bg-amber-50 ring-2 ring-amber-100" : "border-stone-200 hover:border-amber-300"}`}>
                  <span className="block font-bold text-stone-950">{astrologer.name}</span>
                  <span className="mt-2 block text-sm leading-6 text-stone-600">{(astrologer.specialties ?? []).join(" · ")}</span>
                </button>
              ))}
            </div>
            <button type="button" disabled={!astrologerId} onClick={() => setStep(2)} className="mt-7 rounded-full bg-amber-800 px-6 py-3 text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-40">{t("continue")}</button>
          </div>
        )}

        {step === 2 && (
          <div>
            <button type="button" onClick={() => setStep(1)} className="text-sm font-bold text-amber-800">← {t("back")}</button>
            <h2 className="mt-5 text-2xl font-bold text-stone-950">{t("chooseService")}</h2>
            <div className="mt-6 space-y-3">
              {availableServices.map((service) => (
                <button key={service.id} type="button" aria-pressed={serviceId === service.id} onClick={() => { setServiceId(service.id); setSlot(""); }} className={`flex w-full flex-col justify-between gap-3 rounded-2xl border p-5 text-left transition sm:flex-row sm:items-center ${serviceId === service.id ? "border-amber-700 bg-amber-50 ring-2 ring-amber-100" : "border-stone-200 hover:border-amber-300"}`}>
                  <span><span className="block font-bold text-stone-950">{service.name}</span><span className="mt-1 block text-sm text-stone-600">{service.duration_minutes} {t("minutes")}</span></span>
                  <span className="font-bold text-amber-800">{formatPrice(service.price_inr, service.price_usd)}</span>
                </button>
              ))}
            </div>
            {availableServices.length === 0 && <p className="mt-6 rounded-2xl bg-stone-50 p-5 text-sm text-stone-600">{t("noServices")}</p>}
            <button type="button" disabled={!serviceId || slotsPending} onClick={loadAvailability} className="mt-7 rounded-full bg-amber-800 px-6 py-3 text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-40">{slotsPending ? t("loadingSlots") : t("continue")}</button>
          </div>
        )}

        {step === 3 && (
          <div>
            <button type="button" onClick={() => setStep(2)} className="text-sm font-bold text-amber-800">← {t("back")}</button>
            <h2 className="mt-5 text-2xl font-bold text-stone-950">{t("chooseSlot")}</h2>
            <p className="mt-2 text-sm text-stone-600">{t("slotTimezone")}</p>
            {notice && <p className="mt-5 rounded-2xl border border-blue-200 bg-blue-50 px-5 py-4 text-sm text-blue-900">{notice}</p>}
            <div className="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
              {slots.map((item) => (
                <button key={item.start} type="button" aria-pressed={slot === item.start} onClick={() => setSlot(item.start)} className={`rounded-2xl border px-4 py-4 text-left transition ${slot === item.start ? "border-amber-700 bg-amber-50 ring-2 ring-amber-100" : "border-stone-200 hover:border-amber-300"}`}>
                  <span className="block text-sm font-bold text-stone-950">{new Intl.DateTimeFormat(locale, { weekday: "short", month: "short", day: "numeric" }).format(new Date(item.start))}</span>
                  <span className="mt-1 block text-sm text-stone-600">{new Intl.DateTimeFormat(locale, { hour: "numeric", minute: "2-digit" }).format(new Date(item.start))}</span>
                </button>
              ))}
            </div>
            {slots.length === 0 && !notice && <p className="mt-6 rounded-2xl bg-stone-50 p-5 text-sm text-stone-600">{t("noSlots")}</p>}
            <button type="button" disabled={!slot} onClick={() => { setNotice(null); setStep(4); }} className="mt-7 rounded-full bg-amber-800 px-6 py-3 text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-40">{t("continue")}</button>
          </div>
        )}

        {step === 4 && (
          <form onSubmit={submitBooking}>
            <button type="button" onClick={() => setStep(3)} className="text-sm font-bold text-amber-800">← {t("back")}</button>
            <h2 className="mt-5 text-2xl font-bold text-stone-950">{t("yourDetails")}</h2>
            <div className="mt-6 grid gap-5 sm:grid-cols-2">
              <label className="sm:col-span-2"><span className="text-sm font-bold text-stone-800">{t("name")}</span><input required autoComplete="name" value={contact.name} onChange={(event) => setContact({ ...contact, name: event.target.value })} className="mt-2 w-full rounded-2xl border border-stone-300 px-4 py-3 outline-none focus:border-amber-700 focus:ring-2 focus:ring-amber-100" /></label>
              <label><span className="text-sm font-bold text-stone-800">{t("email")}</span><input required type="email" autoComplete="email" value={contact.email} onChange={(event) => setContact({ ...contact, email: event.target.value })} className="mt-2 w-full rounded-2xl border border-stone-300 px-4 py-3 outline-none focus:border-amber-700 focus:ring-2 focus:ring-amber-100" /></label>
              <label><span className="text-sm font-bold text-stone-800">{t("phone")}</span><input required type="tel" autoComplete="tel" value={contact.phone} onChange={(event) => setContact({ ...contact, phone: event.target.value })} className="mt-2 w-full rounded-2xl border border-stone-300 px-4 py-3 outline-none focus:border-amber-700 focus:ring-2 focus:ring-amber-100" /></label>
            </div>

            <label className="mt-6 flex items-start gap-3 rounded-2xl bg-stone-50 p-4 text-sm text-stone-700"><input type="checkbox" checked={includeBirthDetails} onChange={(event) => setIncludeBirthDetails(event.target.checked)} className="mt-1 size-4 accent-amber-800" /><span><strong className="block text-stone-900">{t("includeBirthDetails")}</strong>{t("includeBirthDetailsHelp")}</span></label>
            {includeBirthDetails && (
              <div className="mt-4 grid gap-4 rounded-2xl border border-stone-200 p-5 sm:grid-cols-2">
                <label><span className="text-sm font-bold text-stone-800">{t("dob")}</span><input required type="date" value={contact.dob} onChange={(event) => setContact({ ...contact, dob: event.target.value })} className="mt-2 w-full rounded-xl border border-stone-300 px-3 py-2" /></label>
                <label><span className="text-sm font-bold text-stone-800">{t("time")}</span><input required type="time" value={contact.time} onChange={(event) => setContact({ ...contact, time: event.target.value })} className="mt-2 w-full rounded-xl border border-stone-300 px-3 py-2" /></label>
                <label className="sm:col-span-2"><span className="text-sm font-bold text-stone-800">{t("place")}</span><input required value={contact.place} onChange={(event) => setContact({ ...contact, place: event.target.value })} className="mt-2 w-full rounded-xl border border-stone-300 px-3 py-2" /></label>
              </div>
            )}

            {savedCharts.length > 0 && (
              <label className="mt-5 block"><span className="text-sm font-bold text-stone-800">{t("savedChart")}</span><select value={birthChartId ?? ""} onChange={(event) => setBirthChartId(event.target.value ? Number(event.target.value) : undefined)} className="mt-2 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3"><option value="">{t("noSavedChart")}</option>{savedCharts.map((chart) => <option key={chart.id} value={chart.id}>{chart.name}</option>)}</select></label>
            )}

            <p className="mt-6 text-xs leading-5 text-stone-500">{t("privacy")}</p>
            {notice && <p className="mt-5 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-900">{notice}</p>}
            <button type="submit" disabled={pending} className="mt-7 w-full rounded-full bg-amber-800 px-6 py-4 text-sm font-bold text-white disabled:cursor-wait disabled:opacity-50">{pending ? t("submitting") : t("submit")}</button>
          </form>
        )}
      </section>
    </div>
  );
}
