"use client";

import { useCurrency } from "@/components/site/currency-provider";

export function CoursePrice({ inr, usd }: { inr: number; usd: number }) {
  const { formatPrice } = useCurrency();
  return <>{formatPrice(inr, usd)}</>;
}
