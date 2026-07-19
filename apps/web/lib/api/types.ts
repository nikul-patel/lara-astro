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
  whatsapp_url?: string;
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

export interface ChartResult {
  timezone: string;
  system: AstrologySystem;
  chart_style?: ChartStyle;
  recommendation: ChartRecommendation;
  planetary_positions: unknown;
  houses: unknown;
  chart?: unknown;
  [key: string]: unknown;
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
