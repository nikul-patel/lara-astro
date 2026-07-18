import type {
  ChartInput,
  ChartResult,
  ChartStyle,
} from "@/lib/api";

export type PlanetDisplay = {
  name: string;
  sign: string;
  degree: string;
};

export type HouseDisplay = {
  number: number;
  sign: string;
  planets: string[];
};

function asRecord(value: unknown): Record<string, unknown> | null {
  return typeof value === "object" && value !== null && !Array.isArray(value)
    ? (value as Record<string, unknown>)
    : null;
}

function firstString(
  record: Record<string, unknown>,
  keys: string[],
  fallback = "—",
) {
  for (const key of keys) {
    const value = record[key];
    if (typeof value === "string" && value.trim()) return value;
    if (typeof value === "number") return String(value);
  }

  return fallback;
}

function toEntries(value: unknown): Array<[string, unknown]> {
  if (Array.isArray(value)) {
    return value.map((item, index) => [String(index + 1), item]);
  }

  const record = asRecord(value);
  return record ? Object.entries(record) : [];
}

export function getPlanetaryPositions(value: unknown): PlanetDisplay[] {
  return toEntries(value).flatMap(([key, item]) => {
    const record = asRecord(item);
    if (!record) return [];

    return [
      {
        name: firstString(record, ["name", "planet", "label"], key),
        sign: firstString(record, ["sign", "rashi", "zodiac_sign"]),
        degree: firstString(record, ["degree", "degrees", "longitude"]),
      },
    ];
  });
}

export function getHouses(value: unknown): HouseDisplay[] {
  return toEntries(value).flatMap(([key, item], index) => {
    const record = asRecord(item);
    if (!record) return [];

    const rawPlanets = record.planets ?? record.occupants;
    const planets = Array.isArray(rawPlanets)
      ? rawPlanets.map(String)
      : typeof rawPlanets === "string"
        ? rawPlanets.split(",").map((planet) => planet.trim())
        : [];
    const rawNumber = record.number ?? record.house ?? key;
    const number = Number(rawNumber);

    return [
      {
        number: Number.isFinite(number) ? number : index + 1,
        sign: firstString(record, ["sign", "rashi", "zodiac_sign"]),
        planets: planets.filter(Boolean),
      },
    ];
  });
}

const demoPlanets = [
  ["Sun", "Leo", "12° 18′"],
  ["Moon", "Taurus", "24° 42′"],
  ["Mars", "Gemini", "08° 05′"],
  ["Mercury", "Virgo", "03° 31′"],
  ["Jupiter", "Sagittarius", "17° 49′"],
  ["Venus", "Cancer", "21° 14′"],
  ["Saturn", "Aquarius", "10° 27′"],
  ["Rahu", "Pisces", "05° 36′"],
  ["Ketu", "Virgo", "05° 36′"],
].map(([name, sign, degree]) => ({ name, sign, degree }));

const signs = [
  "Aries",
  "Taurus",
  "Gemini",
  "Cancer",
  "Leo",
  "Virgo",
  "Libra",
  "Scorpio",
  "Sagittarius",
  "Capricorn",
  "Aquarius",
  "Pisces",
];

export function createDemoChart(input: ChartInput): ChartResult {
  const chartStyle: ChartStyle = input.chart_style ?? "north_indian";

  return {
    timezone: "Asia/Kolkata",
    system: input.system ?? "vedic",
    chart_style: input.system === "western" ? undefined : chartStyle,
    recommendation: {
      system: "vedic",
      chart_style: "north_indian",
    },
    planetary_positions: demoPlanets,
    houses: signs.map((sign, index) => ({
      number: index + 1,
      sign,
      planets: demoPlanets
        .filter((_, planetIndex) => planetIndex % 12 === index)
        .map((planet) => planet.name),
    })),
  };
}
