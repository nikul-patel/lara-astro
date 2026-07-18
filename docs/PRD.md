# Astrology Platform — Product Requirements Document (PRD)

**Status:** Draft v2.0 — for review before implementation begins
**Date:** 2026-07-18
**Owner:** Nikul Patel

---

## 1. Executive Summary

A **reusable astrology-business website template/product** — not a site for one specific practice. Nikul is not an astrologer and has no existing client yet; the goal is to build one solid, well-architected codebase that can be **deployed and re-skinned for each future astrology-business client** (their branding, astrologers, services, pricing, content).

Each deployment is a bilingual/trilingual (English, Hindi, Gujarati), SEO-optimized astrology website where visitors can:

- Learn about the astrologer(s)/practice
- Get their birth chart generated from an integrated astrology calculation engine (traditional Indian/Vedic by default, with alternatives — see §8)
- Book paid consultations (payment settled manually via UPI — no payment gateway)
- Enroll in astrology courses (pre-recorded video + live scheduled classes)
- Read SEO content (blog/articles) that drives organic search traffic

A Laravel 13 + TailAdmin admin panel lets staff manage astrologers, services, pricing (INR & USD), bookings, courses, enrollments, content, and site settings.

**Tenancy model:** single-tenant template (§10a) — one codebase per client deployment, not a shared multi-tenant SaaS. This keeps each client's build simple and isolated; multi-tenant SaaS was considered and explicitly deferred (see §10a).

**Content for this build phase:** since there is no real client yet, the build proceeds with **realistic placeholder/demo content** — a fictional astrology practice ("demo tenant") with sample astrologers, services, pricing, courses, and blog posts — so the product is fully functional, demoable, and ready to be re-skinned the moment a real client signs on.

---

## 2. Goals & Success Metrics

| Goal | Metric |
|---|---|
| Rank organically for astrology consultation/course keywords | Search Console impressions/clicks growth, keyword rankings |
| Convert visitors into booked consultations | Booking requests/month, request→confirmed rate |
| Sell astrology courses | Enrollments/month |
| Serve non-English-first audiences | % traffic/bookings from hi/gu locale pages |
| Low operational overhead (manual UPI, no gateway) | Time admin spends confirming payments stays manageable at current volume |

---

## 3. Target Users

1. **End client (site visitor)** — wants a consultation or course; may be English, Hindi, or Gujarati speaking; price-sensitive across INR/USD (domestic + NRI/international audience); may be from any region, hence the region-aware astrology-system recommendation in §8.
2. **Astrologer(s)** — deliver consultations/courses; need visibility into their bookings/availability. (Within a deployment, one or many.)
3. **Business Admin/Owner** — the astrology-business client Nikul deploys this for; manages their own instance's pricing, content, confirms payments, publishes blog content for SEO.
4. **Nikul (builder/operator)** — owns the template codebase itself; customizes and deploys a copy per business client, is not a platform "super-admin" over live tenants (see §10a).

---

## 4. Scope

### In scope
- Public marketing + booking + course website, 3 languages (en/hi/gu)
- Birth chart generation (integrated calculation engine)
- Consultation booking workflow ending in "pending payment" + manual admin confirmation
- Course catalog: recorded video courses + live scheduled classes, same manual-payment enrollment flow
- Dual currency display (INR/USD), admin-controlled pricing
- Multi-astrologer support (configurable — works for 1 or many)
- Admin panel on Laravel 13 + TailAdmin
- SEO essentials: clean URLs, meta tags, sitemap.xml, schema.org markup (LocalBusiness/Service/Course/Article), hreflang tags, fast page loads, blog/CMS
- Standard site features: About, Contact, Testimonials, FAQ, Privacy Policy, Terms, Refund/Cancellation Policy, Blog
- Build, test, and deployment to production hosting

### Out of scope (explicitly, per requirements)
- Payment gateway integration (Razorpay/Stripe/etc.) — payments are manual via UPI
- Native mobile apps
- Live video conferencing infrastructure (we link out to Zoom/Google Meet, not build our own)

---

## 5. Functional Requirements

### 5.1 Public Website

**Home** — hero, value proposition, featured services, featured astrologer(s), testimonials, latest blog posts, trust signals.

**Astrologer Profiles** — bio, photo, specialties, experience, languages spoken, services offered. Supports 1..N astrologers (data model doesn't assume a single practitioner).

**Birth Chart Tool**
- Form: name, date of birth, exact time of birth, place of birth (with geocoding/timezone lookup for accuracy).
- Astrology system/chart style is pre-selected via the region-aware recommendation described in §8, with a visible override control.
- Submits to the astrology calculation engine (see §8) → renders chart (Kundli/D1, planetary positions, houses, optionally basic Dasha) on-site.
- Chart can be saved to the client's account and optionally attached to a consultation booking so the astrologer sees it ahead of time.

**Services & Booking**
- Services list (e.g., "30-min Consultation," "Career Reading," "Match-making") each tied to an astrologer, with duration, description, and price (INR + USD).
- Client picks astrologer → service → available slot.
- **Availability model — configurable per astrologer:**
  - *Built-in mode*: admin manually defines available slots/working hours in the admin panel.
  - *Google Calendar sync mode*: astrologer connects Google Calendar (OAuth); the system reads free/busy to compute open slots and writes the booking as a calendar event.
- Client fills contact details (+ optional birth details / attaches saved chart) → booking is created as **"Pending Payment."**
- Confirmation screen shows the practice's UPI ID and a QR code, with instructions to pay and a reference/order number to quote.
- Admin manually verifies the UPI payment (in their bank/UPI app) and marks the booking **"Confirmed"** in the admin panel, which triggers a confirmation email/WhatsApp message to the client with meeting details (link or in-person/phone info).
- Booking states: `pending_payment → confirmed → completed`, plus `cancelled` / `no_show`.

**Courses**
- Course types: **recorded** (self-paced video modules/lessons, progress tracking) and **live** (scheduled cohort/class with a meeting link, like a recurring booking).
- Course detail page: curriculum/syllabus, instructor, price (INR/USD), reviews.
- Enrollment follows the same pending→confirmed manual-payment pattern as bookings.
- Once confirmed, client gets access to recorded lessons in a simple learner dashboard, or the live class link/schedule.

**Pricing & Currency**
- Every service/course stores an explicit INR price and USD price (admin-entered, not auto-converted, to avoid FX-error liability) — see §11 for rationale.
- Currency switcher on the site (manual toggle; optionally defaults by visitor geolocation) changes displayed price everywhere.

**Multi-language**
- English, Hindi, Gujarati at launch, architected to allow adding more later.
- URL-based locale routing (`/en/...`, `/hi/...`, `/gu/...`) for clean per-language SEO indexing, with `hreflang` tags linking equivalent pages.
- All CMS content (pages, blog posts, service/course descriptions, testimonials) is translatable per-language in the admin panel; UI strings are translated via Laravel localization files.

**Content/SEO pages** — Blog/Articles (for organic search), FAQ, About, Contact (form + WhatsApp click-to-chat + map if applicable), Testimonials, Privacy Policy, Terms & Conditions, Refund/Cancellation Policy, sitemap.xml, robots.txt.

**Accounts** — lightweight client accounts (email/phone + OTP or simple auth) to view booking/course history and saved birth charts. Guest checkout (booking without a full account) should also be supported to reduce friction.

### 5.2 Admin Panel (Laravel 13 + TailAdmin)

- **Dashboard** — bookings today/this week, pending-payment count needing action, revenue snapshot (INR/USD), new enrollments, popular services/courses.
- **Astrologer management** — CRUD profiles, per-astrologer availability mode (manual vs Google Calendar), connect/disconnect Google account.
- **Services management** — CRUD services per astrologer, pricing (INR/USD), duration, active/inactive.
- **Bookings management** — list/filter by status, view client + birth details, mark payment confirmed (with UPI reference note), reschedule/cancel, add internal notes.
- **Courses management** — CRUD courses, upload/attach video lessons (or link external hosted video — see §11), manage live class schedules, curriculum builder.
- **Enrollments management** — same pending/confirm workflow as bookings; view learner progress for recorded courses.
- **CMS** — manage pages, blog posts (with SEO fields: meta title/description/slug, featured image), testimonials, FAQ — each with per-language content.
- **Client management** — view client list, their bookings/enrollments/charts.
- **Settings** — site identity (logo, contact info, social links), UPI ID + QR image, supported languages, SEO defaults (default meta, GA/Search Console verification, schema.org business info), email templates.
- **Roles & permissions** — Admin (full access), Astrologer (own bookings/availability/courses only), Editor (blog/content only) — via Laravel policies/roles.

---

## 6. Non-Functional Requirements

- **SEO**: server-rendered HTML (not a client-only SPA), clean semantic markup, structured data (schema.org: LocalBusiness, Service, Course, Article, Review), XML sitemap auto-generated, canonical tags, hreflang for the 3 locales, Core Web Vitals targets (LCP < 2.5s, CLS < 0.1), image optimization/lazy-loading.
- **Performance**: page cache for public pages, DB query optimization, CDN for static assets/images.
- **Security**: standard Laravel hardening (CSRF, validated inputs, rate-limited forms), HTTPS everywhere, admin 2FA recommended, backups (DB + uploaded media).
- **Accessibility**: semantic HTML, alt text, keyboard-navigable forms, adequate color contrast.
- **Reliability**: automated daily backups, error monitoring/logging.
- **Maintainability**: single primary stack (see §10) to keep long-term maintenance simple for a solo/small team.

---

## 7. Data Model (key entities)

`Astrologer` · `Service` (belongs to Astrologer, price_inr, price_usd, duration) · `Availability`/`GoogleCalendarConnection` · `Booking` (client, service, astrologer, slot, status, birth_chart_id?, upi_reference) · `Client` · `BirthChart` (input details + generated chart data) · `Course` (type: recorded/live) · `CourseModule`/`Lesson` (video) · `LiveSession` (schedule, meeting link) · `Enrollment` (client, course, status) · `Page`/`Post` (CMS, per-locale) · `Testimonial` · `Setting` (site-wide config incl. UPI details, currency defaults) · `User`/`Role` (admin panel accounts).

---

## 8. Astrology Engine Integration — Finalized Approach

**Recommendation: open-source, self-hosted calculation engine** (no per-request API fees, no third-party rate limits, full data control) — built on the **Swiss Ephemeris**, the de-facto standard astronomical calculation library used by essentially all serious astrology software.

- A small internal calculation service (PHP binding or a lightweight sidecar service) takes DOB/time/place + timezone and returns planetary positions, houses, and chart data, which the frontend renders as an SVG chart.

**System default: traditional Indian (Vedic/Jyotish, sidereal, Lahiri ayanamsa).** This is the base configuration for every deployment, since the primary market is Indian/NRI clients.

**Visitor choice + region-aware recommendation** (per your direction — decision is mine, so here's the concrete design):

1. On first use of the birth chart tool, the site detects the visitor's likely region (IP geolocation, falling back to selected site language) and pre-selects a **recommended** chart system/style — visible as a clearly-labeled default, not forced:
   - Visitor located in **North Indian states** (e.g. Delhi, UP, Punjab, Haryana, Rajasthan, MP) → recommend **Vedic, North Indian (diamond) chart style**.
   - Visitor located in **South Indian states** (Tamil Nadu, Karnataka, Andhra Pradesh, Telangana, Kerala) → recommend **Vedic, South Indian (square grid) chart style**.
   - Visitor located in **East Indian states** (West Bengal, Odisha, Assam) → recommend **Vedic, East Indian (Bengali) chart style**.
   - Visitor outside India (NRI/international, e.g. via `/en/` locale + non-India IP) → recommend **Vedic, North Indian style** by default (most widely recognized internationally), while clearly offering **Western tropical** as a one-click alternative for visitors who expect that system.
2. A visible toggle always lets the visitor override: **astrology system** (Vedic/Sidereal ↔ Western/Tropical) and, for Vedic, **chart style** (North / South / East Indian). Their choice is remembered (session/account) for future charts.
3. MVP calculation scope: Lahiri ayanamsa for Vedic (the standard default used by most Indian astrology software) and standard tropical for Western. Additional ayanamsa options (Raman, KP, etc.) are a plausible future enhancement, not MVP.
4. This recommendation logic and the calculation engine are both **per-deployment configurable** in the admin panel (a business client could, e.g., disable Western mode entirely, or force South Indian style only, if that fits their brand) — consistent with this being a reusable template.

---

## 9. Third-Party Integrations Needed

| Integration | Purpose | Notes |
|---|---|---|
| Astrology calculation engine | Birth chart generation | Recommended: self-hosted Swiss-Ephemeris-based |
| Google Calendar API | Optional per-astrologer availability sync | Requires Google Cloud OAuth app + astrologer's Google account consent |
| Transactional email (e.g., SMTP via provider) | Booking/enrollment confirmations, admin alerts | Need an email-sending provider (see prerequisites) |
| WhatsApp click-to-chat / Business API | Client contact + optional booking confirmation | Click-to-chat link is free/simple; Business API is more capable but has cost/approval process |
| Geocoding/timezone lookup | Accurate birth chart timezone from place of birth | Needed for correct chart calculation |
| Video hosting (YouTube/Vimeo unlisted, or S3+CDN) | Recorded course lessons | Avoids heavy storage/bandwidth cost of self-hosting video |
| Google Analytics / Search Console | SEO tracking | You'll need/verify a Google account for the domain |

---

## 10a. Tenancy Model — Single-Tenant Template (not multi-tenant SaaS)

Confirmed decision: this is built as **one reusable, well-structured Laravel codebase** that gets **deployed fresh per astrology-business client** — each client gets their own database, their own domain/hosting, their own branding/content. It is *not* a shared multi-tenant SaaS where multiple businesses share one running install.

Why this over multi-tenant SaaS:
- Matches the actual need: a small number of bespoke client deployments, not a self-serve signup product.
- No cross-tenant data isolation, per-tenant billing/subscription, or super-admin-over-tenants layer to build — meaningfully smaller and faster to ship well.
- Each client can be customized (branding, enabled features, astrology-system defaults per §8) without affecting others, and can be hosted independently per their own requirements.

What this means practically for the build:
- Branding/theme (logo, colors, site name, contact info) lives in admin **Settings**, so re-skinning for a new client is configuration, not code changes.
- Demo content (see §1) is seeded as the first "reference" deployment; a new client engagement = fresh deploy + admin-panel content entry + branding, not a rebuild.
- If real demand later emerges for a true shared multi-tenant SaaS (multiple businesses self-signing-up on one install), that is a distinct, larger project to be scoped separately — not part of this build.

---

## 10. Recommended Tech Stack

**Admin panel (fixed by requirement):** Laravel 13 + TailAdmin (https://github.com/TailAdmin/tailadmin-laravel), PHP 8.3+, MySQL, Tailwind CSS.

**Frontend — recommendation: the same Laravel application**, using **Blade + Livewire 3 + Alpine.js + Tailwind CSS**, rather than a decoupled React/Next.js frontend.

Reasoning:
- Blade/Livewire renders full HTML server-side by default — excellent, simple SEO without needing SSR workarounds (unlike a client-rendered SPA).
- One codebase, one framework, one deployment target — significantly less ongoing maintenance for a project of this size, and matches the Tailwind styling already used by TailAdmin so public site and admin panel feel visually consistent.
- Livewire/Alpine give enough interactivity (booking flow, chart form, currency/language switching) without the complexity of running a separate JS build/deploy pipeline and a separate API layer.
- Laravel's built-in localization + a package like `mcamara/laravel-localization` handles the /en /hi /gu routing and hreflang needs cleanly.

**Alternative considered:** a decoupled Next.js frontend calling a Laravel API. This can yield marginally better raw front-end performance/interactivity, but roughly doubles build and long-term maintenance effort (two codebases, two deploy pipelines, API contract to maintain) for a site of this scope. Not recommended unless you have a specific reason to want it — happy to revisit if you disagree.

**Supporting pieces:** Laravel Queues (email sending, calendar sync jobs), Laravel Scheduler (reminder emails, cleanup of stale pending bookings), `spatie/laravel-translatable` or similar for per-locale CMS fields, `spatie/laravel-sitemap` for SEO sitemap, `spatie/laravel-permission` for admin roles.

---

## 11. Key Design Decisions & Rationale

- **Manual INR + USD pricing (not auto-converted):** admin sets both prices explicitly per service/course. Avoids exchange-rate drift causing under/over-charging, and keeps pricing intentional (e.g., round USD numbers for international clients) rather than a raw FX conversion.
- **"Confirmed on request, pay later" booking flow:** since there's no gateway, a booking is provisionally created immediately (so the slot is held and the client gets clear next steps), then flipped to Confirmed once you manually verify the UPI payment. This is simpler and more transparent for clients than only accepting a transaction ID with no visible next step.
- **Multi-astrologer architecture from day one:** even if a given deployment launches with a single astrologer, the data model treats "astrologer" as a first-class, repeatable entity, so adding more later requires no re-architecture — just adding records.
- **Single-tenant template, not multi-tenant SaaS:** see §10a — one codebase redeployed per client keeps scope and data isolation simple; each client's branding/content lives entirely in that deployment's admin Settings + CMS.
- **Vedic/Indian as the default astrology system, with visitor choice + region-aware recommendation:** see §8. Defaulting to sidereal/Lahiri matches the primary market, while the override + recommendation logic keeps the product usable and credible for visitors expecting Western tropical charts, without forcing a system on anyone.
- **Build against realistic demo content first:** with no real client yet, a fully-populated fictional demo (astrologer, services, pricing, courses, blog) makes the product demoable/sellable immediately and doubles as the reference content structure for onboarding each real client later.

---

## 12. Prerequisites Needed From You Before Implementation Starts

Because this build now proceeds with **demo content** (§1) rather than a real client's material, most content-collection items below are **deferred to "when a real client is onboarded"** rather than blocking today. What's actually needed to start building:

**Blocking now**
1. **GitHub write access** — the session currently cannot push to `nikul-patel/lara-astro` (permission denied). Needs to be granted before any commits can reach the remote.
2. **Hosting for the demo/staging deployment**: you indicated no domain/hosting yet. Recommendation: a VPS (e.g., DigitalOcean/Hetzner/AWS Lightsail) sized for Laravel — minimum PHP 8.3+, MySQL 8, Redis (recommended), 2GB+ RAM — used to host the demo build so it's reviewable/demoable. A throwaway/staging domain (or subdomain) is fine for now; production domain can come later per real client.

**Needed before a specific client goes live (not blocking the demo build)**
3. That client's domain, SSL (typically automatic via Let's Encrypt once domain + hosting are set), UPI ID + QR code image, logo/brand colors, astrologer profile(s), service list + pricing, course content, legal page copy (Privacy/Terms/Refund — we can draft templates, client signs off), blog/SEO content, testimonials.
4. Google account access if that client wants Google Calendar sync, Analytics, or Search Console.
5. Email-sending provider (transactional email) for that deployment's booking/enrollment notifications.
6. (Optional) WhatsApp Business number for click-to-chat.

**Resolved (previously open, now decided)**
- ~~Vedic vs Western astrology, chart style~~ → resolved in §8: Vedic/Indian default, region-aware recommendation, visitor override, per-deployment configurable.
- ~~Single practice vs multi-astrologer~~ → resolved: multi-astrologer-capable architecture (§10a, §11).
- ~~Tenancy model~~ → resolved: single-tenant template, redeployed per client (§10a).
- Language list (English, Hindi, Gujarati) confirmed for the template's launch languages.

---

## 13. Suggested Phases

| Phase | Deliverable |
|---|---|
| 0 | This PRD signed off; blocking prerequisites (§12) resolved: GitHub push access + staging hosting |
| 1 | Laravel 13 + TailAdmin scaffold, auth, roles, base admin panel deployed to staging |
| 2 | Public site skeleton: layout, i18n routing (en/hi/gu), currency switcher, CMS pages, SEO base (sitemap, meta, schema) |
| 3 | Astrologer/Service management + booking flow (pending→confirmed UPI flow) |
| 4 | Birth chart engine integration (Vedic default + region-aware recommendation + Western override, per §8) |
| 5 | Courses (recorded + live) + enrollment flow |
| 6 | **Demo content population** — fictional astrology practice: astrologer profile, services/pricing, courses, blog posts, testimonials (§1) |
| 7 | QA pass (functional, SEO, performance, cross-browser/mobile), demo review with you |
| 8 | Demo deployed to staging/demo domain. **Per-client rollout (future, repeatable):** fresh deploy → real branding/content/pricing via admin panel → that client's domain/hosting → go live |

---

## 14. Open Questions Log

- Do you want client accounts to be mandatory, or is guest booking/enrollment acceptable? (Current assumption: guest allowed, account optional.)
- Video hosting preference for recorded courses: YouTube/Vimeo unlisted (simplest, free/cheap) vs self-hosted S3+CDN (more control, more cost)? (Current assumption for demo: YouTube/Vimeo unlisted.)
- Any existing brand/design reference sites you like the look of, for the demo's visual style?
- Staging/demo domain: do you want to register one now (e.g. a generic name like "yoursite-demo.com") or use a subdomain of something you already control?

---

*Once GitHub push access and staging hosting (§12) are available, implementation can begin per the phase plan above, building against demo content and finalizing per-client rollout details as real clients are onboarded.*
