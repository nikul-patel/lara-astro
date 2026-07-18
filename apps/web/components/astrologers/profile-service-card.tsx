"use client";

import { Link } from "@/i18n/navigation";
import type { Service } from "@/lib/api";
import { useCurrency } from "@/components/site/currency-provider";

type ProfileServiceCardProps = {
  astrologerSlug: string;
  service: Service;
  bookLabel: string;
  durationLabel: string;
  fromLabel: string;
};

export function ProfileServiceCard({
  astrologerSlug,
  service,
  bookLabel,
  durationLabel,
  fromLabel,
}: ProfileServiceCardProps) {
  const { formatPrice } = useCurrency();
  const bookingHref = `/booking?astrologer=${encodeURIComponent(astrologerSlug)}&service=${encodeURIComponent(service.slug)}`;

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
        <p className="text-sm font-medium text-stone-500">
          {service.duration_minutes} {durationLabel}
        </p>
      </div>
      <Link
        href={bookingHref}
        className="mt-6 rounded-full bg-amber-800 px-5 py-3 text-center text-sm font-bold text-white transition hover:bg-amber-900 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-amber-800"
      >
        {bookLabel}
      </Link>
    </article>
  );
}
