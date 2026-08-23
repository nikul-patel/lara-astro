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
- `POST /enrollments` → same pending→confirmed pattern as bookings, body: `{ course_id, client: {...}, guest: boolean }`. Response includes the same `upi_id`/`upi_qr_url` (read live from Settings) as `BookingResource`, for the confirmation screen.
- `GET /me/enrollments` (authenticated) → includes lesson access / progress for recorded courses, live session schedule/links for live courses.

### Birth Chart
- `POST /chart` → body: `{ name, dob, time, place, system?: "vedic"|"western", chart_style?: "north_indian"|"south_indian"|"east_indian" }`. If `system`/`chart_style` omitted, the API returns its region-based recommendation (derived from `place` geocoding, per PRD §8) alongside the calculated chart, so the frontend can pre-select it and show the override control.
- Response shape (finalized in #16; extended by the Kundali Intelligence Suite, see "Open items"): `{ timezone, system, chart_style: string|null, recommendation: { system, chart_style }, planetary_positions: [{ name, sign, degree, longitude }], houses: [{ number, sign, planets: string[] }] (always 12, whole-sign), ascendant: { sign, degree }, nakshatra: { index, name, lord, pada }|null, dasha: { mahadasha: [{ lord, start, end, antardashas: [{ lord, start, end }] }] (9 entries, each with 9 nested antardashas) }|null, yogas: [{ key, name, category, planets: string[], houses: number[], description }]|null, predictions: { marriage, career, education, foreign_settlement: { key, text } }|null, remedies: [{ planet, afflictions: string[], gemstone, mantra, donation, fasting_day, caution_note }]|null, location_matched: boolean }`. `chart_style` is `null` for the western system. `nakshatra`/`dasha`/`yogas` are `null` for the western system (sidereal-only concepts); `predictions`/`remedies` are additionally `null` whenever the deployment's Settings has `astrology_predictions_enabled` off. `location_matched` is `false` when the birth place couldn't be resolved (falls back to Delhi/`Asia/Kolkata`) — see "Open items" below on geocoding.
- Requesting `system: "western"` when the deployment's Settings has disabled it (PRD §8 point 4) returns a 422 on the `system` field.
- `POST /charts` (authenticated) → save a chart to the client's account. `GET /me/charts`. `GET /charts/{id}` (authenticated, owner-only — 403 otherwise) → a single saved chart, same `{ id, name, input, result }` shape as the list entries; used by the kundali detail page for deep-linking/refresh-safety.
- `POST /year-wise` → body: same birth details as `POST /chart` plus `year?: number` (defaults to the current year) and `style?: "simplified"|"varshphal"` (defaults to `"simplified"`). Public and stateless, mirroring `POST /chart` — nothing is persisted. `style: "simplified"` responds `{ year, jupiter_transit: { sign, house_from_ascendant, house_from_moon }, saturn_transit: {...}, governing_dasha: [{ mahadasha_lord, antardasha_lord, start, end }] }`; `style: "varshphal"` responds `{ year, solar_return_moment, ascendant, planetary_positions, houses, muntha: { sign, lord }, varshesh: { lord, candidates }, is_day_birth, sahams: [{ key, name, longitude, sign }] }` (8 curated Sahams).
- `GET /charts/{id}/year-wise?year=&style=` (authenticated, owner-only — 403 otherwise) → same two response shapes as above, computed once per `(chart, year, style)` and cached in `year_wise_forecasts`; wrapped in `{ id, year, style, result }`. Returns 201 on the first (computing) request per Laravel's resource-response convention, 200 on a cached repeat.
- `GET /charts/{id}/report?year_wise_style=&year=` (authenticated, owner-only — 403 otherwise) → downloads a PDF (`application/pdf`) Kundali report assembled from the chart's already-computed `result` (chart summary, dasha timeline, yogas, predictions, remedies) plus, when `year_wise_style` is given, the same cached year-wise forecast `GET /charts/{id}/year-wise` would return. Regenerated on every request (no PDF caching) since rendering is cheap pure-CPU HTML-to-PDF.

### CMS
- `GET /pages/{slug}?locale=` — static pages (About, legal pages, FAQ).
- `GET /posts?locale=&page=` / `GET /posts/{slug}?locale=` — blog.
- `GET /testimonials?locale=` — testimonials.

### Client Account
- `POST /auth/register`, `POST /auth/login`, `POST /auth/logout`, `GET /me`.
- `GET /me/bookings`, `GET /me/enrollments`, `GET /me/charts`.

## Open items (flag if these need to change)

- Chart JSON shape is finalized above (#16). The calculation engine itself is a self-hosted, dependency-free implementation (Meeus low-precision Sun/Moon series, Standish's Keplerian planetary elements, whole-sign houses) rather than a Swiss Ephemeris binding — this build environment had no route to install/download one (no C compiler, no network access to Swiss Ephemeris's data files). Accuracy is good for correct sign/house placement, not arc-second precision. Geocoding is a small bundled gazetteer (PRD §8's named states/cities plus other major Indian/international cities) rather than a live geocoding API, for the same reason — unrecognized places fall back to Delhi/`Asia/Kolkata` (`location_matched: false`). Swapping either for the real thing later is contained to `app/Services/Astrology/`.
- **Kundali Intelligence Suite** (nakshatra/Vimshottari dasha, yoga detection, life-area predictions, remedies) extends the chart response above, all Vedic-only and computed on the same self-hosted engine — no external astrology package was added (evaluated and rejected `kunjara/jyotish`: GPL-2.0 license risk plus a compiled `swetest` binary dependency incompatible with this deployment target). Dasha transition dates use a 365.25-day/year approximation, so expect drift on the order of a day per decade against a table computed from the true sidereal year — consistent with this engine's existing sign/house-level precision, not arc-second accuracy. Yoga detection (`app/Services/Astrology/Yogas/`) and predictions (`app/Services/Astrology/Predictions/`) use simplified, documented classical rules rather than exhaustive classical-text coverage — see those namespaces' class docblocks for the specific simplifications (e.g. Neecha Bhanga checks 2 of several classical cancellation conditions). Predictions and remedies are gated by the new `Setting::astrology_predictions_enabled` flag (default on). Prediction/remedy text is currently English-only even though the template arrays are shaped for `en`/`hi`/`gu` — translating the generated prose (not just UI chrome) is unscheduled follow-up work.
- **Year-wise forecasts** (`POST /year-wise`, `GET /charts/{id}/year-wise`) live outside `birth_charts.result` in their own `year_wise_forecasts` table since, unlike the rest of the Kundali Intelligence Suite, they're date-dependent rather than immutable-at-birth. `style: "simplified"` samples transiting Jupiter/Saturn at a single fixed snapshot (July 1, noon UTC of the target year) rather than tracking exact sign-ingress dates within the year — a deliberate scope simplification. `style: "varshphal"` is a genuine second calculation engine (`app/Services/Astrology/YearlyForecast/`): it Newton-iterates a solar-return moment, casts a full return chart via the shared `ChartAssembler` (also used by the natal `BirthChartCalculator`), and derives Muntha/Varshesh/Saham from it. Varshesh uses a simplified 3-candidate dignity contest rather than full classical Panchadhikari, and only 8 of the ~16-32 classically-attested Sahams are implemented — both documented simplifications in `VarshphalCalculator`'s and `Saham`'s class docblocks.
- **PDF report** (`GET /charts/{id}/report`) uses `barryvdh/laravel-dompdf` (MIT-licensed, pure PHP, no system binaries — deploys cleanly on Laravel Cloud unlike a headless-Chrome approach) — the one new composer dependency added for this feature set. Blade templates live under `resources/views/pdf/`; dompdf's CSS support is print-era (tables/floats, no flexbox/grid), which shapes that template's layout. `app/Services/Astrology/YearlyForecast/CachedForecast.php` centralizes the compute-or-fetch-cached-forecast logic shared by `YearWiseForecastController::forSavedChart()` and `ChartReportController::download()`.
- Availability slot shape (manual vs Google-Calendar-backed) needs confirming against #10/#17 once availability admin UI is built — the read contract here (`GET /availability`) is meant to stay stable regardless of the backing source.
- `GET /courses/{slug}`'s curriculum outline (#14) deliberately omits `CourseLesson.video_url` and `LiveSession.meeting_url` — those are gated behind enrollment per "`GET /me/enrollments` ... includes lesson access ... live session schedule/links", so `CourseLesson`/`LiveSession` only get those fields on the authenticated learner endpoints built in #15/#28. The public shapes only expose titles/durations/schedule.
