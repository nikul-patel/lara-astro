export const locales = ["en", "hi", "gu"] as const;
export type Locale = (typeof locales)[number];

export type Currency = "INR" | "USD";
export type CourseType = "recorded" | "live";
export type BookingStatus =
  | "pending_payment"
  | "confirmed"
  | "completed"
  | "cancelled"
  | "no_show";
export type PaymentStatus = "pending_payment" | "confirmed";
export type AstrologySystem = "vedic" | "western";
export type ChartStyle =
  | "north_indian"
  | "south_indian"
  | "east_indian";

export interface PaginationMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: PaginationMeta;
}

export interface ApiErrorPayload {
  message: string;
  errors?: Record<string, string[]>;
}

export interface Client {
  id: number;
  name: string;
  email: string;
  phone: string;
}

export type ClientInput = Pick<Client, "name" | "email" | "phone">;

export interface Price {
  price_inr: number;
  price_usd: number;
}

export interface Service extends Price {
  id: number;
  slug: string;
  astrologer_id: number;
  name: string;
  description: string;
  duration_minutes: number;
}

export interface Astrologer {
  id: number;
  slug: string;
  name: string;
  bio: string | null;
  photo_url: string | null;
  specialties: string[] | null;
  languages: string[] | null;
  experience_years?: number;
  services?: Service[];
}

export interface AvailabilitySlot {
  start: string;
  end: string;
  available: boolean;
  [key: string]: unknown;
}

export interface CourseModule {
  id: number;
  title: string;
  lessons?: CourseLesson[];
}

export interface CourseLesson {
  id: number;
  title: string;
  duration_minutes?: number;
  completed?: boolean;
  video_url?: string;
}

export interface LiveSession {
  id: number;
  starts_at: string;
  ends_at?: string;
  meeting_url?: string;
}

export interface Course extends Price {
  id: number;
  slug: string;
  title: string;
  description: string;
  type: CourseType;
  instructor?: Astrologer;
  modules?: CourseModule[];
  live_sessions?: LiveSession[];
}

export interface CmsPage {
  id: number;
  slug: string;
  title: string;
  content: string;
  meta_title?: string;
  meta_description?: string;
}

export interface Post extends CmsPage {
  excerpt?: string;
  featured_image_url?: string | null;
  published_at?: string | null;
}

export interface Testimonial {
  id: number;
  name: string;
  quote: string;
  rating?: number;
}

export interface ContactSettings {
  email?: string;
  phone?: string;
  address?: string;
}

export interface SocialLink {
  label: string;
  url: string;
}

export interface LegalLink {
  label: string;
  slug: string;
}

export interface SeoSettings {
  default_meta_title?: string;
  default_meta_description?: string;
  ga_measurement_id?: string;
  search_console_verification?: string;
  schema_business_name?: string;
  schema_business_type?: string;
}

export interface Settings {
  site_name: string;
  logo_url?: string | null;
  supported_languages: Locale[];
  upi_id?: string | null;
  upi_qr_url?: string | null;
  default_currency?: Currency;
  currencies?: Currency[];
  contact?: ContactSettings;
  social_links?: SocialLink[] | Record<string, string>;
  legal_links?: LegalLink[];
  seo?: SeoSettings;
  [key: string]: unknown;
}

export interface BirthDetails {
  dob: string;
  time: string;
  place: string;
  [key: string]: unknown;
}

export interface Booking {
  id: number;
  astrologer_id: number;
  service_id: number;
  slot: string;
  status: BookingStatus;
  reference_number: string;
  guest_token?: string;
  upi_id?: string | null;
  upi_qr_url?: string | null;
  client: Client;
  birth_details?: BirthDetails;
  birth_chart_id?: number;
}

export interface CreateBookingInput {
  astrologer_id: number;
  service_id: number;
  slot: string;
  client: ClientInput;
  birth_details?: BirthDetails;
  birth_chart_id?: number;
  guest: boolean;
}

export interface Enrollment {
  id: number;
  course_id: number;
  status: PaymentStatus;
  reference_number: string;
  guest_token?: string;
  upi_id?: string | null;
  upi_qr_url?: string | null;
  client?: Client;
  course?: Course;
}

export interface CreateEnrollmentInput {
  course_id: number;
  client: ClientInput;
  guest: boolean;
}

export interface ChartInput extends BirthDetails {
  name: string;
  system?: AstrologySystem;
  chart_style?: ChartStyle;
}

export interface ChartRecommendation {
  system: AstrologySystem;
  chart_style?: ChartStyle;
}

export interface Nakshatra {
  index: number;
  name: string;
  lord: string;
  pada: number;
}

export interface DashaPeriod {
  lord: string;
  start: string;
  end: string;
}

export interface Mahadasha extends DashaPeriod {
  antardashas: DashaPeriod[];
}

export interface DashaTimeline {
  mahadasha: Mahadasha[];
}

export interface Yoga {
  key: string;
  name: string;
  category: string;
  planets: string[];
  houses: number[];
  description: string;
}

export interface PredictionEntry {
  key: string;
  text: string;
}

export interface Predictions {
  marriage: PredictionEntry;
  career: PredictionEntry;
  education: PredictionEntry;
  foreign_settlement: PredictionEntry;
}

export interface Remedy {
  planet: string;
  afflictions: string[];
  gemstone: string;
  mantra: string;
  donation: string;
  fasting_day: string;
  caution_note: string;
}

export interface ChartResult {
  timezone: string;
  system: AstrologySystem;
  chart_style?: ChartStyle;
  recommendation: ChartRecommendation;
  planetary_positions: unknown;
  houses: unknown;
  chart?: unknown;
  ascendant?: { sign: string; degree: string };
  nakshatra?: Nakshatra | null;
  dasha?: DashaTimeline | null;
  yogas?: Yoga[] | null;
  predictions?: Predictions | null;
  remedies?: Remedy[] | null;
  location_matched?: boolean;
  [key: string]: unknown;
}

export type YearWiseStyle = "simplified" | "varshphal";

export interface TransitInfo {
  sign: string;
  house_from_ascendant: number;
  house_from_moon: number;
}

export interface GoverningDashaPeriod {
  mahadasha_lord: string;
  antardasha_lord: string;
  start: string;
  end: string;
}

export interface SimplifiedForecastResult {
  year: number;
  jupiter_transit: TransitInfo;
  saturn_transit: TransitInfo;
  governing_dasha: GoverningDashaPeriod[];
}

export interface Saham {
  key: string;
  name: string;
  longitude: number;
  sign: string;
}

export interface VarshphalForecastResult {
  year: number;
  solar_return_moment: string;
  ascendant: { sign: string; degree: string };
  planetary_positions: unknown;
  houses: unknown;
  muntha: { sign: string; lord: string };
  varshesh: { lord: string; candidates: string[] };
  is_day_birth: boolean;
  sahams: Saham[];
}

export type YearWiseForecastResult =
  | SimplifiedForecastResult
  | VarshphalForecastResult;

export interface YearWiseForecast {
  id: number;
  year: number;
  style: YearWiseStyle;
  result: YearWiseForecastResult;
}

export interface SavedChart {
  id: number;
  name: string;
  input: ChartInput;
  result: ChartResult;
}

export interface AuthCredentials {
  email: string;
  password: string;
}

export interface RegisterInput extends AuthCredentials {
  name: string;
  phone?: string;
  password_confirmation: string;
}

export interface AuthResponse {
  token: string;
  client: Client;
}

export interface MeResponse {
  client: Client;
}

export interface DoshaHouseAffliction {
  afflicted: boolean;
  house: number | null;
}

export interface ManglikResult {
  is_manglik: boolean;
  from_ascendant: DoshaHouseAffliction;
  from_moon: DoshaHouseAffliction;
  from_venus: DoshaHouseAffliction;
  cancelled: boolean;
  cancellation_reason: string | null;
  description: string;
}

export interface KaalSarpResult {
  present: boolean;
  type: string | null;
  rahu_house: number | null;
  description: string;
}

export interface DoshaInput extends BirthDetails {
  name: string;
}

export interface DoshaResult {
  manglik: ManglikResult;
  kaal_sarp: KaalSarpResult;
}

export type SadeSatiPhase = "rising" | "peak" | "setting" | "none";

export interface SadeSatiInput extends BirthDetails {
  name: string;
  reference_date?: string;
}

export interface SadeSatiResult {
  moon_sign: string;
  phase: SadeSatiPhase;
  is_active: boolean;
  cycle_start: string;
  peak_phase_start: string;
  setting_phase_start: string;
  cycle_end: string;
}

export interface KundaliMatchingPerson {
  name: string;
  dob: string;
  time: string;
  place: string;
}

export interface KundaliMatchingInput {
  bride: KundaliMatchingPerson;
  groom: KundaliMatchingPerson;
}

export interface KootaResult {
  name: string;
  points: number;
  max_points: number;
  description: string;
}

export interface KundaliMatchingPartyResult {
  nakshatra: Nakshatra;
  rashi: string;
}

export interface KundaliMatchingResult {
  total_points: number;
  max_points: number;
  minimum_recommended: number;
  is_recommended: boolean;
  has_nadi_dosha: boolean;
  has_bhakoot_dosha: boolean;
  kootas: KootaResult[];
  bride: KundaliMatchingPartyResult;
  groom: KundaliMatchingPartyResult;
}

export interface PanchangInput {
  date?: string;
  place: string;
}

export interface PanchangTithi {
  number: number;
  name: string;
  paksha: string;
}

export interface PanchangYoga {
  index: number;
  name: string;
}

export interface PanchangVaar {
  name: string;
  lord: string;
}

export interface PanchangResult {
  date: string;
  tithi: PanchangTithi;
  nakshatra: Nakshatra;
  yoga: PanchangYoga;
  /** A plain karana name (e.g. "Bava") — unlike yoga/nakshatra it has no independently useful numeric index. */
  karana: string;
  vaar: PanchangVaar;
  sunrise: string;
  sunset: string;
  location_matched: boolean;
}

export interface NumerologyInput {
  name: string;
  dob: string;
}

export interface NumerologyNumber {
  number: number;
  meaning: string;
}

export interface NumerologyResult {
  life_path: NumerologyNumber;
  destiny: NumerologyNumber;
  soul_urge: NumerologyNumber;
  personality: NumerologyNumber;
}
