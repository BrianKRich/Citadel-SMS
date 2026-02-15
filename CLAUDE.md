# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Student Management System (SMS) v2.0 — Laravel 12 + Vue 3 + Inertia.js + PostgreSQL. Built for Georgia Job Challenge Academy.

## Commands

```bash
# Development (starts Laravel, queue, log viewer, and Vite HMR)
composer run dev

# Testing (SQLite in-memory, no DB setup needed)
php artisan test
php artisan test --filter StudentTest    # single test
composer run test                        # clears config cache first

# Build frontend
npm run build

# Lint/format PHP
./vendor/bin/pint

# Database
php artisan migrate
php artisan db:seed

# Install deps (--legacy-peer-deps required for npm)
composer install
npm install --legacy-peer-deps

# First-time setup
composer run setup
```

## Architecture

**Inertia.js (no REST API):** Controllers return `Inertia::render('Page', $props)` — no JSON API layer. The only true API endpoint is `GET /api/theme`. Frontend routes use Ziggy's `route()` helper.

**Request flow:** `routes/web.php` → Controller → `Inertia::render()` → Vue page component receives data via `defineProps()`.

**Shared props:** `HandleInertiaRequests.php` shares `auth.user` with every page via `$page.props.auth.user`.

**Frontend entry:** `resources/js/app.js` bootstraps Inertia with auto-discovery of `Pages/**/*.vue`. Path alias `@/` → `resources/js/`.

### Key Patterns

- **Vue Composition API only** — all components use `<script setup>`
- **ClassModel** — named to avoid PHP reserved word `class`; uses `$table = 'classes'`
- **Auto-generated IDs** — `Student` → `STU-YYYY-###`, `Employee` → `EMP-YYYY-###` (in model `boot()`)
- **Polymorphic phones** — `PhoneNumber` model shared by Student and Employee via `morphMany`
- **SoftDeletes** — on Student and Employee models
- **Query scopes** — models define `scopeSearch()`, `scopeStatus()`, `scopeActive()` for controller filtering
- **Pagination** — all index actions use `->paginate(10)->withQueryString()`
- **Flash messages** — controllers use `->with('success', ...)`, pages read `$page.props.flash` and show `Alert` component
- **Validation** — inline in controllers via `$request->validate()`
- **Photo uploads** — `Storage::disk('public')` to `students/photos` or `employees/photos`

### Theme System

Dynamic CSS variables loaded at runtime by `useTheme.js` composable from `/api/theme`. Colors stored in `settings` DB table. Dark mode uses Tailwind `class` strategy with `useDarkMode.js`.

### Roles

Two roles: `admin` (full access) and default user (profile only). Check via `User::isAdmin()`.

## Code Layout

- `app/Http/Controllers/Admin/` — feature controllers (Students, Employees, Courses, Classes, etc.)
- `app/Models/` — Eloquent models with scopes and relationships
- `resources/js/Pages/Admin/` — Vue page components matching URL structure
- `resources/js/Components/UI/` — reusable UI components (Alert, Card, PageHeader, etc.)
- `resources/js/composables/` — `useTheme.js`, `useDarkMode.js`
- `resources/js/Layouts/` — AuthenticatedLayout, GuestLayout
- `docs/` — detailed docs (ARCHITECTURE.md, DATABASE.md, BACKEND.md, FRONTEND.md, etc.)

## CI/CD

Push to `main` auto-deploys to AWS Lightsail via `.github/workflows/deploy.yml`. Requires GitHub secrets for SSH access.

## Conventions

- 4-space indentation (2-space for YAML), LF line endings
- PascalCase Vue components, PSR-4 PHP autoloading
- Status badge styling via local `getStatusBadgeClass()` in each index page
- Do not use co-authors in git commits or pushes
