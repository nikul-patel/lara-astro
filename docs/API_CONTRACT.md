# API v1 Contract — `apps/api` ↔ `apps/web`

Source of truth for the JSON API the Next.js frontend (`apps/web`) consumes from the Laravel backend (`apps/api`). Entities match PRD §7. This is a living document — update it alongside API changes, don't let it drift.

Base URL (local dev): `http://localhost:8000/api/v1`. Configured via `NEXT_PUBLIC_API_BASE_URL` in `apps/web`.

## Auth model

- **Public read endpoints** (services, astrologers, courses, CMS content, settings, availability, chart calculation): no auth required.
- **Client account**: Laravel Sanctum token auth. `POST /auth/register`, `POST /auth/login` return a bearer token; send as `Authorization: Bearer <token>` on subsequent requests. Guest booking/enrollment (no account) is also supported — see below.
- **Admin panel** (TailAdmin, server-rendered Blade): separate session-based auth, not part of this JSON contract.
- CORS: allowed origins are the Vercel preview + production domains, configured in `apps/api/config/cors.php`.

## Conventions

- **Locale**: `?locale=en|hi|gu` query param on any endpoint returning translatable content (services, astrologers, courses, CMS). Defaults to `en` if omitted or unsupported.
- **Currency**: prices are always returned as both `price_inr` and `price_usd` (both explicitly stored per PRD §11 — never computed client-side). The frontend's currency switcher just chooses which field to display.
- **Pagination**: list endpoints return `{ "data": [...], "meta": { "current_page", "last_page", "per_page", "total" } }` (Laravel's default paginator shape).
- **Errors**: `{ "message": string, "errors"?: { [field]: string[] } }` with standard HTTP status codes (422 validation, 404 not found, 401/403 auth, 500 server).
- **IDs**: numeric auto-increment `id` plus a URL-safe `slug` for anything linked to from a page (astrologers, services, courses, posts, pages).

## Endpoints

### Settings
- `GET /settings` → site branding, supported languages, UPI ID + QR image URL, SEO defaults, currency display config. Powers the frontend's global layout (#22).

### Astrologers & Services
- `GET /astrologers?locale=` → list. `GET /astrologers/{slug}?locale=` → detail with `services[]`.
- `GET /services?astrologer_id=&locale=` → list, each with `price_inr`, `price_usd`, `duration_minutes`.
- `GET /availability?astrologer_id=&service_id=&from=&to=` → open slots (works whether that astrologer is in manual-slots or Google-Calendar-sync mode — the API abstracts the source).

### Bookings
- `POST /bookings` → body: `{ astrologer_id, service_id, slot, client: { name, email, phone }, birth_details?, birth_chart_id?, guest: boolean }`. Returns booking with `status: "pending_payment"`, a `reference_number`, and the UPI ID/QR (from Settings) for the confirmation screen.
- `GET /bookings/{id}?token=` → lookup for guests (token issued at creation) or via authenticated `/me/bookings` for account holders.
- Status values: `pending_payment | confirmed | completed | cancelled | no_show` (admin-panel-driven transitions; the public API is read + create only, no client-side status changes).

### Courses & Enrollments
- `GET /courses?type=recorded|live&locale=` → list. `GET /courses/{slug}?locale=` → detail with curriculum/modules.
- `POST /enrollments` → same pending→confirmed pattern as bookings, body: `{ course_id, client: {...}, guest: boolean }`.
- `GET /me/enrollments` (authenticated) → includes lesson access / progress for recorded courses, live session schedule/links for live courses.

### Birth Chart
- `POST /chart` → body: `{ name, dob, time, place, system?: "vedic"|"western", chart_style?: "north_indian"|"south_indian"|"east_indian" }`. If `system`/`chart_style` omitted, the API returns its region-based recommendation (derived from `place` geocoding, per PRD §8) alongside the calculated chart, so the frontend can pre-select it and show the override control.
- Response includes: resolved timezone, planetary positions, houses, the system/style actually used, and the recommended system/style (so the frontend can show "recommended" vs "your selection" if they differ).
- `POST /charts` (authenticated) → save a chart to the client's account. `GET /me/charts`.

### CMS
- `GET /pages/{slug}?locale=` — static pages (About, legal pages, FAQ).
- `GET /posts?locale=&page=` / `GET /posts/{slug}?locale=` — blog.
- `GET /testimonials?locale=` — testimonials.

### Client Account
- `POST /auth/register`, `POST /auth/login`, `POST /auth/logout`, `GET /me`.
- `GET /me/bookings`, `GET /me/enrollments`, `GET /me/charts`.

## Open items (flag if these need to change)

- Exact chart JSON shape (planetary positions/houses) will be finalized alongside #16 (birth chart engine) — the request/response envelope above is stable, the internals of the `chart` payload may grow.
- Availability slot shape (manual vs Google-Calendar-backed) needs confirming against #10/#17 once availability admin UI is built — the read contract here (`GET /availability`) is meant to stay stable regardless of the backing source.
- `GET /courses/{slug}`'s curriculum outline (#14) deliberately omits `CourseLesson.video_url` and `LiveSession.meeting_url` — those are gated behind enrollment per "`GET /me/enrollments` ... includes lesson access ... live session schedule/links", so `CourseLesson`/`LiveSession` only get those fields on the authenticated learner endpoints built in #15/#28. The public shapes only expose titles/durations/schedule.
