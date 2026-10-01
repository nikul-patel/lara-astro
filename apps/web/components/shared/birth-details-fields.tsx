const PLACE_SUGGESTIONS = [
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

export type BirthDetailsFieldKey = "name" | "dob" | "time" | "place" | "date";

export interface BirthDetailsValue {
  name?: string;
  dob?: string;
  time?: string;
  place?: string;
  date?: string;
}

const INPUT_CLASS =
  "mt-2 w-full rounded-2xl border border-stone-300 bg-white px-4 py-3 text-stone-950 outline-none transition focus:border-amber-700 focus:ring-2 focus:ring-amber-100";

const LABEL_CLASS = "text-sm font-bold text-stone-800";

/**
 * Shared name/dob/time/place/date inputs for the 5 "advanced free" tools
 * (Numerology, Panchang, Doshas, Sade Sati, Kundali Matching) plus the
 * existing birth chart tool — each uses a different subset of fields
 * (e.g. Numerology only needs name+dob, Panchang only date+place), so
 * `fields` controls which inputs render rather than hardcoding all four.
 * `idPrefix` disambiguates the place `<datalist>` id when this component
 * is rendered more than once on a page, as Kundali Matching's bride/groom
 * blocks do.
 */
export function BirthDetailsFields({
  fields,
  value,
  onChange,
  t,
  idPrefix = "",
}: {
  fields: readonly BirthDetailsFieldKey[];
  value: BirthDetailsValue;
  onChange: (value: BirthDetailsValue) => void;
  t: (key: string) => string;
  idPrefix?: string;
}) {
  const placeListId = `${idPrefix}birth-place-suggestions`;

  function set(key: BirthDetailsFieldKey, fieldValue: string) {
    onChange({ ...value, [key]: fieldValue });
  }

  return (
    <div className="grid gap-5 sm:grid-cols-2">
      {fields.includes("name") && (
        <label className="sm:col-span-2">
          <span className={LABEL_CLASS}>{t("name")}</span>
          <input
            required
            autoComplete="name"
            value={value.name ?? ""}
            onChange={(event) => set("name", event.target.value)}
            className={INPUT_CLASS}
          />
        </label>
      )}
      {fields.includes("dob") && (
        <label>
          <span className={LABEL_CLASS}>{t("dob")}</span>
          <input
            required
            type="date"
            value={value.dob ?? ""}
            onChange={(event) => set("dob", event.target.value)}
            className={INPUT_CLASS}
          />
        </label>
      )}
      {fields.includes("date") && (
        <label>
          <span className={LABEL_CLASS}>{t("date")}</span>
          <input
            type="date"
            value={value.date ?? ""}
            onChange={(event) => set("date", event.target.value)}
            className={INPUT_CLASS}
          />
        </label>
      )}
      {fields.includes("time") && (
        <label>
          <span className={LABEL_CLASS}>{t("time")}</span>
          <input
            required
            type="time"
            value={value.time ?? ""}
            onChange={(event) => set("time", event.target.value)}
            className={INPUT_CLASS}
          />
        </label>
      )}
      {fields.includes("place") && (
        <label className="sm:col-span-2">
          <span className={LABEL_CLASS}>{t("place")}</span>
          <input
            required
            list={placeListId}
            autoComplete="off"
            value={value.place ?? ""}
            onChange={(event) => set("place", event.target.value)}
            placeholder={t("placePlaceholder")}
            className={INPUT_CLASS}
          />
          <datalist id={placeListId}>
            {PLACE_SUGGESTIONS.map((place) => (
              <option key={place} value={place} />
            ))}
          </datalist>
          <span className="mt-2 block text-xs leading-5 text-stone-500">
            {t("placeHelp")}
          </span>
        </label>
      )}
    </div>
  );
}
