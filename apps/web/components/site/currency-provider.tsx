"use client";

import {
  createContext,
  useCallback,
  useContext,
  useMemo,
  useSyncExternalStore,
} from "react";
import type { Currency } from "@/lib/api";

const STORAGE_KEY = "lara-astro-currency";

type CurrencyContextValue = {
  currency: Currency;
  setCurrency: (currency: Currency) => void;
  formatPrice: (priceInr: number, priceUsd: number) => string;
};

const CurrencyContext = createContext<CurrencyContextValue | null>(null);
const currencyListeners = new Set<() => void>();

type CurrencyProviderProps = {
  children: React.ReactNode;
  defaultCurrency?: Currency;
};

function isCurrency(value: string | null): value is Currency {
  return value === "INR" || value === "USD";
}

function subscribeToCurrency(listener: () => void) {
  currencyListeners.add(listener);
  const handleStorage = (event: StorageEvent) => {
    if (event.key === STORAGE_KEY) {
      listener();
    }
  };

  window.addEventListener("storage", handleStorage);
  return () => {
    currencyListeners.delete(listener);
    window.removeEventListener("storage", handleStorage);
  };
}

export function CurrencyProvider({
  children,
  defaultCurrency = "INR",
}: CurrencyProviderProps) {
  const currency = useSyncExternalStore(
    subscribeToCurrency,
    () => {
      const storedCurrency = window.localStorage.getItem(STORAGE_KEY);
      return isCurrency(storedCurrency) ? storedCurrency : defaultCurrency;
    },
    () => defaultCurrency,
  );

  const setCurrency = useCallback((nextCurrency: Currency) => {
    window.localStorage.setItem(STORAGE_KEY, nextCurrency);
    currencyListeners.forEach((listener) => listener());
  }, []);

  const formatPrice = useCallback(
    (priceInr: number, priceUsd: number) =>
      new Intl.NumberFormat(currency === "INR" ? "en-IN" : "en-US", {
        style: "currency",
        currency,
        maximumFractionDigits: currency === "INR" ? 0 : 2,
      }).format(currency === "INR" ? priceInr : priceUsd),
    [currency],
  );

  const value = useMemo(
    () => ({ currency, setCurrency, formatPrice }),
    [currency, formatPrice, setCurrency],
  );

  return (
    <CurrencyContext.Provider value={value}>
      {children}
    </CurrencyContext.Provider>
  );
}

export function useCurrency() {
  const context = useContext(CurrencyContext);

  if (!context) {
    throw new Error("useCurrency must be used inside CurrencyProvider");
  }

  return context;
}
