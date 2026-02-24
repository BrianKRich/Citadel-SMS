# Student Management System

A full-stack web application for managing student information, academic records, employee data, and educational administration. Built for **Georgia Job Challenge Academy** with Laravel 12, Vue 3, Inertia.js, and PostgreSQL.

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12, PHP 8.3 |
| Frontend | Vue 3 (Composition API, `<script setup>`) |
| Bridge | Inertia.js (no REST API layer) |
| Database | PostgreSQL |
| Build | Vite + Tailwind CSS |
| Testing | PHPUnit (302 tests, 1495 assertions) |
| Deploy | AWS Lightsail via GitHub Actions CI/CD |

---

## Completed Features

### Phase 0 — Foundation & Theme System
- Laravel Breeze authentication (login, register, password reset)
- Role-based access: `admin`, `site_admin`, default user
- Dynamic theme system: 5 customizable color schemes via CSS variables, no rebuild required
- Dark mode with `localStorage` persistence and no flash on load
- Responsive layout with mobile hamburger nav

### Phase 1 — Student & Course Foundation
- **Students:** full profile with auto-ID (`STU-YYYY-###`), demographics, status tracking, photo upload, soft deletes (7-year retention), restore UI
- **Guardians:** linked to students with `is_primary` flag
- **Employees:** full profile with auto-ID (`EMP-YYYY-###`), department/role assignment, soft deletes, restore UI
- **Courses:** unique course codes, department/level organization, credit tracking
- **Departments & Employee Roles:** configurable reference data
- Polymorphic phone numbers shared by Students and Employees

### Phase 2 — Class Scheduling & Enrollment
- Class sections with JSON schedule, room, capacity, instructor assignment
- Schedule conflict detection (same instructor, overlapping time slots)
- Enrollment management with status tracking (enrolled/dropped/completed/failed)
- Capacity enforcement and academic year/term organization

### Phase 3A — Grading & Assessments
- Assessment types with weighted grading
- Grade entry, GPA calculation, grade points
- Per-student grade history

### Phase 3B — Report Cards & Transcripts
- PDF report card generation (DomPDF, Blade templates)
- Transcript PDF generation
- Term-based grade summaries

### Phase 3D — Soft Delete Restore UI
- Trashed Students and Trashed Employees index pages
- Bulk and single restore functionality

### Phase 3E — Audit Logging
- Automatic audit trail via Eloquent observers (create/update/delete)
- Admin audit log viewer with filtering and record type display
- Purge with reason (site_admin only) + immutable purge log
- Dashboard card

### Phase 3F — Custom Fields
- EAV (Entity–Attribute–Value) custom fields on Students, Employees, Courses, Classes, Enrollments
- Admin-defined fields: text, textarea, number, date, select, checkbox
- Inline enable/disable toggle per entity type
- Show pages display active custom field values

### Phase 4 — Attendance Management
- Daily attendance tracking per class session
- Status types: present, absent, tardy, excused
- Attendance reports and summaries
- Feature flag: `feature_attendance_enabled`

### Phase 5 — Student Notes
- Department-scoped notes on student profiles
- Standalone notes index for Operations department
- Audit logged; auto-employee creation on user create
- Para-Pro role support

### Phase 7 — Document Management
- Private file storage on `local` disk (never publicly accessible)
- Documents linked to Students, Employees, or the Institution
- Per-entity document cards on Student and Employee Show pages (upload, download, delete)
- Institution-wide document library (`/admin/documents`) with filter bar (search, entity type, category)
- Upload form with entity selector — documents always linked to the correct person/org
- Secure download via authenticated controller route (`Storage::disk('local')->download()`)
- Accepted file types: PDF, Word, Excel, PNG/JPG/GIF/WebP — 10 MB max
- Feature flag: `feature_documents_enabled`
- Dashboard quick-action card

### Cross-Cutting
- **Breadcrumb navigation** on all admin child pages
- **Feature Settings** page (site_admin only): toggle Attendance, Theme, Documents, Grade Management, Report Cards
- **User Management**: create/edit/delete users; hire date; employee record auto-created on user create
- **Site Admin role**: full admin access + exclusive Audit Log purge and Feature Settings management
- **Dashboard**: stat cards + quick-action grid + fixed admin config row (Audit Log | Custom Fields | Feature Settings)

---

## User Roles

| Role | Access |
|------|--------|
| `user` | Profile page only |
| `admin` | All admin pages, view Audit Log |
| `site_admin` | All admin pages + Audit Log purge + Feature Settings |

**Default seeded account (created by `php artisan db:seed`):**

| Field | Value |
|-------|-------|
| Email | `admin@admin.com` |
| Password | `admin` |
| Role | `site_admin` |

> Change the password immediately after first login in a production environment.

To promote an existing user to site admin via tinker:
```bash
php artisan tinker
User::where('email', 'your@email.com')->first()->update(['role' => 'site_admin']);
```

---

## Installation

### Requirements
- PHP 8.3+
- PostgreSQL 14+
- Node.js 18+
- Composer 2+

### Setup

```bash
git clone https://github.com/BrianKRich/Student-Management-System.git
cd Student-Management-System

# Install dependencies
composer install
npm install --legacy-peer-deps

# Environment
cp .env.example .env
php artisan key:generate

# Configure .env: DB_CONNECTION=pgsql, DB_DATABASE=sms, etc.

# Database
php artisan migrate
php artisan db:seed

# Build frontend
npm run build

# First-time setup shortcut
composer run setup
```

### Development Server

```bash
# Start Laravel server in background
php artisan serve --host=127.0.0.1 --port=8000

# Or start all services (Laravel + queue + log viewer + Vite HMR)
composer run dev
```

---

## Testing

```bash
# Run all tests (PostgreSQL sms_testing database required)
php artisan test

# Run a specific test class
php artisan test --filter DocumentTest

# Clear config cache first (recommended)
composer run test
```

**Current status:** 302 tests, 1495 assertions, all passing.

---

## Key URLs

| URL | Description |
|-----|-------------|
| `/` | Landing page |
| `/admin` | Dashboard |
| `/admin/students` | Student management |
| `/admin/employees` | Employee management |
| `/admin/courses` | Course catalog |
| `/admin/classes` | Class scheduling |
| `/admin/documents` | Document library |
| `/admin/audit-log` | Audit log viewer |
| `/admin/feature-settings` | Feature flag toggles (site_admin) |
| `/admin/theme` | Theme customization |

---

## Project Structure

```
app/
├── Http/Controllers/Admin/    # Feature controllers
├── Models/                    # Eloquent models (24)
├── Observers/                 # Audit log observers
└── Http/Middleware/           # HandleInertiaRequests (shared props)

resources/js/
├── Pages/Admin/               # Vue page components (72)
├── Components/UI/             # Reusable UI components
├── Components/Users/          # User form components
├── Layouts/                   # AuthenticatedLayout, GuestLayout
└── composables/               # useTheme.js, useDarkMode.js

database/
├── migrations/                # 29 tables
├── seeders/                   # 16 seeders
└── factories/                 # 17 factories

tests/Feature/Admin/           # 28 test files
docs/                          # Architecture, database, backend, frontend docs
```

---

## Documentation

| Document | Contents |
|----------|---------|
| [ARCHITECTURE.md](docs/ARCHITECTURE.md) | System design and patterns |
| [DATABASE.md](docs/DATABASE.md) | Schema and relationships |
| [BACKEND.md](docs/BACKEND.md) | Controllers, models, business logic |
| [FRONTEND.md](docs/FRONTEND.md) | Vue components and pages |
| [DEVELOPMENT_LOG.md](docs/DEVELOPMENT_LOG.md) | Per-phase implementation history |
| [SECURITY.md](docs/SECURITY.md) | Security features and practices |
| [COMPONENTS.md](docs/COMPONENTS.md) | Vue component library reference |

---

## Roadmap

| Phase | Description | Status |
|-------|-------------|--------|
| Phase 0 | Foundation, Auth, Theme | ✅ Done |
| Phase 1 | Students, Employees, Courses | ✅ Done |
| Phase 2 | Classes, Enrollment, Terms | ✅ Done |
| Phase 3A | Grading & Assessments | ✅ Done |
| Phase 3B | Report Cards & Transcripts | ✅ Done |
| Phase 3D | Soft Delete Restore UI | ✅ Done |
| Phase 3E | Audit Logging | ✅ Done |
| Phase 3F | Custom Fields | ✅ Done |
| Phase 4 | Attendance Management | ✅ Done |
| Phase 5 | Student Notes | ✅ Done |
| Phase 7 | Document Management | ✅ Done |
| Phase 3G | Custom Report Builder | 🔜 Next |
| Phase 3C | CSV Import & Bulk Export | ⏸ Deferred |
| Phase 5 | Parent/Guardian Portal | 📋 Planned |
| Phase 6 | Academic Calendar | 📋 Planned |
| Phase 8 | Reporting & Analytics | 📋 Planned |

---

## License

© 2026 Brian K. Rich. All rights reserved.

---

**Version:** 2.0.0
**Last Updated:** February 24, 2026
**Status:** Active Development
