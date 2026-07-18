# Astrology Platform — Product Requirements Document (PRD)

**Status:** Draft v1.0 — for client review before implementation begins
**Date:** 2026-07-18
**Owner:** Nikul Patel

---

## 1. Executive Summary

A bilingual/trilingual (English, Hindi, Gujarati), SEO-optimized astrology website where visitors can:

- Learn about the astrologer(s)/practice
- Get their birth chart generated from an integrated astrology calculation engine
- Book paid consultations (payment settled manually via UPI — no payment gateway)
- Enroll in astrology courses (pre-recorded video + live scheduled classes)
- Read SEO content (blog/articles) that drives organic search traffic

A Laravel 13 + TailAdmin admin panel lets staff manage astrologers, services, pricing (INR & USD), bookings, courses, enrollments, content, and site settings.

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

1. **Client (visitor)** — wants a consultation or course; may be English, Hindi, or Gujarati speaking; price-sensitive across INR/USD (domestic + NRI/international audience).
2. **Astrologer(s)** — deliver consultations/courses; need visibility into their bookings/availability.
3. **Admin/Owner** — manages the whole platform: pricing, content, confirms payments, publishes blog content for SEO.

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

## 8. Astrology Engine Integration

**Recommendation: open-source, self-hosted calculation engine** (no per-request API fees, no third-party rate limits, full data control) — built on the **Swiss Ephemeris**, the de-facto standard astronomical calculation library used by essentially all serious astrology software.

- Approach: a small internal calculation service (PHP binding or a lightweight sidecar service) that takes DOB/time/place + timezone and returns planetary positions, houses (Placidus or Vedic/Lahiri per your tradition), and chart data, which the frontend then renders as an SVG/chart image.
- Since Indian/Vedic astrology is implied (INR pricing, Hindi/Gujarati), the engine should default to **sidereal (Lahiri ayanamsa)** calculations, with the option to add Western tropical charts later if needed.
- **Open question for you:** do you follow a specific tradition (Vedic/Jyotish vs Western tropical) and a specific chart style (North Indian / South Indian / East Indian Kundli chart)? This affects both the calculation config and the chart rendering style — please confirm.
- If you already use a specific commercial astrology software/API today, tell us and we'll evaluate integrating with that instead of the self-hosted engine.

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
- **Multi-astrologer architecture from day one:** even if you launch with a single astrologer, the data model treats "astrologer" as a first-class, repeatable entity, so adding more later requires no re-architecture — just adding records.

---

## 12. Prerequisites Needed From You Before Implementation Starts

**Infrastructure**
1. Domain name (registered, or tell us your preferred name so we can advise on registering it).
2. Hosting: you indicated you don't have this yet. Recommendation: a VPS (e.g., DigitalOcean/Hetzner/AWS Lightsail) with a control panel (e.g., Ploi, Forge, or plain Nginx) sized for Laravel — we can provision this for you if you grant access/budget, or you can provision it and hand over SSH access. Minimum: PHP 8.3+, MySQL 8, Redis (recommended), 2GB+ RAM.
3. SSL certificate — typically automatic via Let's Encrypt once domain + hosting are set up.

**Accounts/Access**
4. A Google account for: Google Cloud project (Calendar API + OAuth, if using calendar sync), Google Analytics, Search Console.
5. An email-sending provider account (e.g., a transactional email service) for booking/enrollment notification emails, or an existing business email/SMTP you want us to use.
6. Your UPI ID and a UPI QR code image to display at checkout.
7. (Optional) WhatsApp Business number if you want a click-to-chat button.

**Content & Branding**
8. Logo, brand colors, and any existing brand guidelines.
9. Astrologer profile(s): name(s), photo(s), bio, qualifications, languages spoken, specialties.
10. Service list with descriptions, durations, and INR/USD pricing.
11. Course list: for each course — curriculum/syllabus, whether recorded or live, pricing, and either the actual video files (or links to where they're hosted) or a placeholder plan for producing them.
12. Legal page content: Privacy Policy, Terms & Conditions, Refund/Cancellation Policy (we can draft templates for your review, but you should have final sign-off since these are legal documents).
13. Initial blog/SEO content ideas or existing articles, plus any target keywords or competitor sites you want us to reference for SEO strategy.
14. Testimonials/reviews, if you have existing ones to seed the site.

**Decisions to confirm**
15. Vedic (sidereal/Lahiri) vs Western (tropical) astrology, and preferred chart style (North/South/East Indian) — see §8.
16. Final language list confirmation: English, Hindi, Gujarati at launch (as discussed).
17. Availability mode per astrologer: manual admin-set slots vs Google Calendar sync (can differ per astrologer).

---

## 13. Suggested Phases

| Phase | Deliverable |
|---|---|
| 0 | This PRD signed off; prerequisites (§12) provided; hosting/domain ready |
| 1 | Laravel 13 + TailAdmin scaffold, auth, roles, base admin panel deployed to staging |
| 2 | Public site skeleton: layout, i18n routing (en/hi/gu), currency switcher, CMS pages, SEO base (sitemap, meta, schema) |
| 3 | Astrologer/Service management + booking flow (pending→confirmed UPI flow) |
| 4 | Birth chart engine integration + chart display |
| 5 | Courses (recorded + live) + enrollment flow |
| 6 | Content population (with your material from §12), blog, testimonials |
| 7 | QA pass (functional, SEO, performance, cross-browser/mobile), UAT with you |
| 8 | Production deployment, DNS cutover, post-launch monitoring |

---

## 14. Open Questions Log

- Astrology tradition/chart style (Vedic vs Western; North/South/East Indian layout) — **needs your answer**.
- Do you want client accounts to be mandatory, or is guest booking/enrollment acceptable? (Current assumption: guest allowed, account optional.)
- Video hosting preference for recorded courses: YouTube/Vimeo unlisted (simplest, free/cheap) vs self-hosted S3+CDN (more control, more cost)?
- Any existing brand/design reference sites you like the look of?

---

*Once hosting/domain (or a decision to have us provision them), the astrology-tradition decision, and initial content/branding are available, implementation can begin per the phase plan above.*
