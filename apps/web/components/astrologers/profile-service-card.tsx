"use client";

import type { Service } from "@/lib/api";
import { useCurrency } from "@/components/site/currency-provider";
import { Link } from "@/i18n/navigation";

type ProfileServiceCardProps = {
  service: Service;
  durationLabel: string;
  fromLabel: string;
  astrologerSlug: string;
  bookLabel: string;
};

export function ProfileServiceCard({
  service,
  durationLabel,
  fromLabel,
  astrologerSlug,
  bookLabel,
}: ProfileServiceCardProps) {
  const { formatPrice } = useCurrency();

  return (
    <article className="flex h-full flex-col rounded-3xl border border-stone-200 bg-white p-7 shadow-sm">
      <h3 className="text-xl font-bold text-stone-950">{service.name}</h3>
      <p className="mt-3 flex-1 text-sm leading-6 text-stone-600">
        {service.description}
      </p>
      <div className="mt-7 flex items-end justify-between gap-4 border-t border-stone-100 pt-5">
        <div>
          <p className="text-xs font-semibold uppercase tracking-wider text-stone-500">
            {fromLabel}
          </p>
          <p className="mt-1 text-lg font-bold text-amber-800">
            {formatPrice(service.price_inr, service.price_usd)}
          </p>
        </div>
        <div className="text-right">
          <p className="text-sm font-medium text-stone-500">
            {service.duration_minutes} {durationLabel}
          </p>
          <Link
            href={{ pathname: "/booking", query: { astrologer: astrologerSlug, service: service.slug } }}
            className="mt-3 inline-block rounded-full bg-amber-800 px-4 py-2 text-sm font-bold text-white hover:bg-amber-900"
          >
            {bookLabel}
          </Link>
        </div>
      </div>
    </article>
  );
}
