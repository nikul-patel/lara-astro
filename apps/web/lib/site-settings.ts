import { api, type Settings } from "@/lib/api";

const fallbackSettings: Settings = {
  site_name: "Jyotish Path",
  supported_languages: ["en", "hi", "gu"],
  default_currency: "INR",
  currencies: ["INR", "USD"],
  contact: {
    email: "hello@example.com",
    phone: "+91 00000 00000",
    address: "Gujarat, India",
  },
  social_links: [],
};

export async function getSiteSettings(): Promise<Settings> {
  try {
    return await api.settings();
  } catch {
    // The public Settings endpoint is delivered on Track A. Keep preview and
    // static builds usable until it is available, then prefer its tenant data.
    return fallbackSettings;
  }
}
