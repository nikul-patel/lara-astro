import { apiRequest } from "./client";
import type {
  Astrologer,
  AuthCredentials,
  AuthResponse,
  AvailabilitySlot,
  Booking,
  ChartInput,
  ChartResult,
  CmsPage,
  Course,
  CourseType,
  CreateBookingInput,
  CreateEnrollmentInput,
  Enrollment,
  Locale,
  MeResponse,
  PaginatedResponse,
  Post,
  RegisterInput,
  SavedChart,
  Service,
  Settings,
  Testimonial,
} from "./types";

export * from "./client";
export * from "./types";

type QueryValue = string | number | boolean | undefined;

interface LocaleQuery extends Record<string, QueryValue> {
  locale?: Locale;
}

interface ListServicesQuery extends LocaleQuery {
  astrologer_id?: number;
}

interface AvailabilityQuery extends Record<string, QueryValue> {
  astrologer_id: number;
  service_id: number;
  from: string;
  to: string;
}

interface ListCoursesQuery extends LocaleQuery {
  type?: CourseType;
}

interface ListPostsQuery extends LocaleQuery {
  page?: number;
}

const get = <T>(
  path: string,
  query?: Record<string, QueryValue>,
  token?: string,
) => apiRequest<T>(path, { query, token });

const post = <T>(path: string, body?: unknown, token?: string) =>
  apiRequest<T>(path, { method: "POST", body, token });

export const api = {
  settings: () => get<Settings>("/settings"),

  astrologers: {
    list: (query: LocaleQuery = {}) =>
      get<PaginatedResponse<Astrologer>>("/astrologers", query),
    get: (slug: string, query: LocaleQuery = {}) =>
      get<Astrologer>(`/astrologers/${encodeURIComponent(slug)}`, query),
  },

  services: {
    list: (query: ListServicesQuery = {}) =>
      get<PaginatedResponse<Service>>("/services", query),
  },

  availability: (query: AvailabilityQuery) =>
    get<AvailabilitySlot[]>("/availability", query),

  bookings: {
    create: (input: CreateBookingInput, token?: string) =>
      post<Booking>("/bookings", input, token),
    get: (id: number, guestToken?: string, token?: string) =>
      get<Booking>(
        `/bookings/${id}`,
        guestToken ? { token: guestToken } : undefined,
        token,
      ),
    mine: (token: string) => get<Booking[]>("/me/bookings", undefined, token),
  },

  courses: {
    list: (query: ListCoursesQuery = {}) =>
      get<PaginatedResponse<Course>>("/courses", query),
    get: (slug: string, query: LocaleQuery = {}) =>
      get<Course>(`/courses/${encodeURIComponent(slug)}`, query),
  },

  enrollments: {
    create: (input: CreateEnrollmentInput, token?: string) =>
      post<Enrollment>("/enrollments", input, token),
    mine: (token: string) =>
      get<Enrollment[]>("/me/enrollments", undefined, token),
  },

  charts: {
    calculate: (input: ChartInput) => post<ChartResult>("/chart", input),
    save: (input: ChartInput & { result: ChartResult }, token: string) =>
      post<SavedChart>("/charts", input, token),
    mine: (token: string) => get<SavedChart[]>("/me/charts", undefined, token),
  },

  pages: {
    get: (slug: string, query: LocaleQuery = {}) =>
      get<CmsPage>(`/pages/${encodeURIComponent(slug)}`, query),
  },

  posts: {
    list: (query: ListPostsQuery = {}) =>
      get<PaginatedResponse<Post>>("/posts", query),
    get: (slug: string, query: LocaleQuery = {}) =>
      get<Post>(`/posts/${encodeURIComponent(slug)}`, query),
  },

  testimonials: (query: LocaleQuery = {}) =>
    get<PaginatedResponse<Testimonial>>("/testimonials", query),

  auth: {
    register: (input: RegisterInput) =>
      post<AuthResponse>("/auth/register", input),
    login: (input: AuthCredentials) => post<AuthResponse>("/auth/login", input),
    logout: (token: string) => post<void>("/auth/logout", undefined, token),
    me: (token: string) => get<MeResponse>("/me", undefined, token),
  },
} as const;
