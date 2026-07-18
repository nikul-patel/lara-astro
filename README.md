# lara-astro

Astrology consultation/courses platform — a reusable, single-tenant template redeployed per astrology-business client. See [`docs/PRD.md`](docs/PRD.md) for full requirements and [`docs/API_CONTRACT.md`](docs/API_CONTRACT.md) for the API shape.

## Structure

This is a monorepo with two independently-deployed apps:

- **`apps/api`** — Laravel 13 + [TailAdmin](https://github.com/TailAdmin/tailadmin-laravel). Serves the admin panel (Blade, session auth) and a JSON API (`/api/v1/...`, Sanctum token auth) consumed by `apps/web`. Deploys to **Laravel Cloud**.
- **`apps/web`** — Next.js (App Router, TypeScript, Tailwind). The public site: booking, courses, birth chart tool, blog, etc. Deploys to **Vercel**.

Work is tracked in GitHub Issues — see the [epic #3](../../issues/3) for the full task breakdown across both apps.

## Local development

### `apps/api`

```bash
cd apps/api
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan serve
```

### `apps/web`

```bash
cd apps/web
npm install
npm run dev
```

Set `NEXT_PUBLIC_API_BASE_URL` in `apps/web/.env.local` to point at the running Laravel API (e.g. `http://localhost:8000/api/v1`).

**Note for anyone touching `apps/web`:** this Next.js version has real breaking changes from older training data (Cache Components replacing route-segment caching config, etc.) — see `apps/web/AGENTS.md` and the docs under `apps/web/node_modules/next/dist/docs/` before writing pages.
