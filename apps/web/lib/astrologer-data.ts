import type { AppLocale } from "@/i18n/routing";
import {
  apiForLocale,
  type Astrologer,
  type Service,
} from "@/lib/api";
import { demoHomeData } from "@/lib/home-data";

export type AstrologerProfile = Astrologer & {
  services: Service[];
};

function getDemoProfiles(locale: AppLocale): AstrologerProfile[] {
  const demo = demoHomeData[locale];

  return demo.astrologers.map((astrologer) => ({
    ...astrologer,
    services: demo.services.filter(
      (service) => service.astrologer_id === astrologer.id,
    ),
  }));
}

export const demoAstrologerSlugs = demoHomeData.en.astrologers.map(
  (astrologer) => astrologer.slug,
);

export async function getAstrologers(
  locale: AppLocale,
): Promise<Astrologer[]> {
  try {
    const response = await apiForLocale(locale).astrologers.list();

    return response.data.length > 0
      ? response.data
      : getDemoProfiles(locale);
  } catch {
    return getDemoProfiles(locale);
  }
}

export async function getAstrologerProfile(
  locale: AppLocale,
  slug: string,
): Promise<AstrologerProfile | null> {
  const localizedApi = apiForLocale(locale);
  const demoProfile = getDemoProfiles(locale).find(
    (astrologer) => astrologer.slug === slug,
  );

  try {
    const astrologer = await localizedApi.astrologers.get(slug);
    let services = astrologer.services ?? [];

    if (services.length === 0) {
      try {
        const response = await localizedApi.services.list({
          astrologer_id: astrologer.id,
        });
        services = response.data;
      } catch {
        services = demoProfile?.services ?? [];
      }
    }

    return { ...astrologer, services };
  } catch {
    return demoProfile ?? null;
  }
}
