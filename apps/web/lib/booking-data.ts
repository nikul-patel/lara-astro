import type { AppLocale } from "@/i18n/routing";
import {
  apiForLocale,
  type Astrologer,
  type PaginatedResponse,
  type Service,
} from "@/lib/api";
import { demoHomeData } from "@/lib/home-data";
import { getSiteSettings } from "@/lib/site-settings";

export type BookingData = {
  astrologers: Astrologer[];
  services: Service[];
  upiId: string | null;
  upiQrUrl: string | null;
};

async function getAllPages<T>(
  getPage: (page: number) => Promise<PaginatedResponse<T>>,
): Promise<T[]> {
  const items: T[] = [];
  let page = 1;
  let lastPage = 1;

  do {
    const response = await getPage(page);
    items.push(...response.data);
    lastPage = response.meta.last_page;
    page += 1;
  } while (page <= lastPage);

  return items;
}

export async function getBookingData(locale: AppLocale): Promise<BookingData> {
  const localizedApi = apiForLocale(locale);
  const fallback = demoHomeData[locale];

  const [astrologersResult, servicesResult, settings] = await Promise.all([
    getAllPages((page) => localizedApi.astrologers.list({ page })).catch(
      () => null,
    ),
    getAllPages((page) => localizedApi.services.list({ page })).catch(
      () => null,
    ),
    getSiteSettings(),
  ]);

  const hasCompleteApiData = Boolean(
    astrologersResult?.length && servicesResult?.length,
  );
  const astrologers = hasCompleteApiData
    ? astrologersResult!
    : fallback.astrologers;
  const services = hasCompleteApiData ? servicesResult! : fallback.services;

  return {
    astrologers,
    services,
    upiId: settings.upi_id ?? null,
    upiQrUrl: settings.upi_qr_url ?? null,
  };
}
