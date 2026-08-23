import type { ApiErrorPayload } from "./types";

const DEFAULT_API_BASE_URL = "http://localhost:8000/api/v1";

type QueryValue = string | number | boolean | null | undefined;

export interface ApiRequestOptions
  extends Omit<RequestInit, "body" | "headers"> {
  body?: unknown;
  headers?: HeadersInit;
  query?: Record<string, QueryValue>;
  token?: string;
}

export class ApiError extends Error {
  constructor(
    message: string,
    readonly status: number,
    readonly errors?: Record<string, string[]>,
    readonly payload?: unknown,
  ) {
    super(message);
    this.name = "ApiError";
  }
}

export function getApiBaseUrl(): string {
  return (
    process.env.NEXT_PUBLIC_API_BASE_URL?.replace(/\/$/, "") ??
    DEFAULT_API_BASE_URL
  );
}

export function buildUrl(path: string, query?: Record<string, QueryValue>): string {
  const normalizedPath = path.startsWith("/") ? path : `/${path}`;
  const url = new URL(`${getApiBaseUrl()}${normalizedPath}`);

  for (const [key, value] of Object.entries(query ?? {})) {
    if (value !== undefined && value !== null && value !== "") {
      url.searchParams.set(key, String(value));
    }
  }

  return url.toString();
}

function isApiErrorPayload(payload: unknown): payload is ApiErrorPayload {
  return (
    typeof payload === "object" &&
    payload !== null &&
    "message" in payload &&
    typeof payload.message === "string"
  );
}

export async function apiRequest<T>(
  path: string,
  options: ApiRequestOptions = {},
): Promise<T> {
  const { body, headers, query, token, ...init } = options;
  const requestHeaders = new Headers(headers);

  requestHeaders.set("Accept", "application/json");
  if (body !== undefined && !requestHeaders.has("Content-Type")) {
    requestHeaders.set("Content-Type", "application/json");
  }
  if (token) {
    requestHeaders.set("Authorization", `Bearer ${token}`);
  }

  const response = await fetch(buildUrl(path, query), {
    ...init,
    headers: requestHeaders,
    body: body === undefined ? undefined : JSON.stringify(body),
  });

  const contentType = response.headers.get("content-type");
  const payload: unknown =
    response.status === 204
      ? undefined
      : contentType?.includes("application/json")
        ? await response.json()
        : await response.text();

  if (!response.ok) {
    throw new ApiError(
      isApiErrorPayload(payload)
        ? payload.message
        : `API request failed with status ${response.status}`,
      response.status,
      isApiErrorPayload(payload) ? payload.errors : undefined,
      payload,
    );
  }

  return payload as T;
}

/**
 * Fetches a binary response (currently just the PDF report) as a Blob,
 * bypassing apiRequest's JSON handling. The report endpoint sits behind a
 * Sanctum bearer token kept in localStorage rather than a cookie, so a
 * plain `<a href>` can't authenticate the download — callers turn this
 * Blob into an object URL and click a temporary `<a download>` instead
 * (see components/kundali/kundali-report.tsx).
 */
export async function apiDownload(
  path: string,
  options: { query?: Record<string, QueryValue>; token?: string } = {},
): Promise<{ blob: Blob; filename: string | null }> {
  const requestHeaders = new Headers();
  if (options.token) {
    requestHeaders.set("Authorization", `Bearer ${options.token}`);
  }

  const response = await fetch(buildUrl(path, options.query), {
    headers: requestHeaders,
  });

  if (!response.ok) {
    throw new ApiError(
      `API request failed with status ${response.status}`,
      response.status,
    );
  }

  const disposition = response.headers.get("content-disposition");
  const filenameMatch = disposition?.match(/filename="?([^";]+)"?/);

  return { blob: await response.blob(), filename: filenameMatch?.[1] ?? null };
}
