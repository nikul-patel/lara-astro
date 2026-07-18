"use client";

import type { Currency } from "@/lib/api";
import { useCurrency } from "./currency-provider";

const currencies: Currency[] = ["INR", "USD"];

export function CurrencySwitcher({ label }: { label: string }) {
  const { currency, setCurrency } = useCurrency();

  return (
    <fieldset className="flex items-center gap-2">
      <legend className="sr-only">{label}</legend>
      <span className="hidden text-xs font-semibold uppercase tracking-wider text-stone-500 lg:inline">
        {label}
      </span>
      <div className="inline-flex rounded-full border border-stone-300 bg-white p-1 shadow-sm">
        {currencies.map((option) => (
          <button
            key={option}
            type="button"
            aria-pressed={currency === option}
            onClick={() => setCurrency(option)}
            className={`rounded-full px-3 py-1.5 text-xs font-bold transition-colors ${
              currency === option
                ? "bg-amber-700 text-white"
                : "text-stone-600 hover:bg-amber-50 hover:text-amber-900"
            }`}
          >
            {option}
          </button>
        ))}
      </div>
    </fieldset>
  );
}
