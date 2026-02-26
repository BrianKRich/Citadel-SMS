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
| Testing | PHPUnit (370 tests, 1957 assertions) |
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
- **Courses:** unique course codes, department/level organization, credit tracking; grading type per course: **Credit System** (with credits field) or **Pass/Fail** (displays P/F)
- **Departments & Employee Roles:** configurable reference data
- Polymorphic phone numbers shared by Students and Employees

### Phase 2 — Class Scheduling & Enrollment
- Class sections with JSON schedule, room, capacity, instructor assignment
- Enrollment management with status tracking (enrolled/dropped/completed/failed)
- Capacity enforcement and academic year organization

### Phase 3A — Grading & Assessments
- Assessment types with weighted grading
- Grade entry, GPA calculation, grade points
- Per-student grade history

### Phase 3B — Report Cards & Transcripts
- PDF report card generation (DomPDF, Blade templates)
- Transcript PDF generation
- Cohort-based grade summaries

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
- Documents linked to Students, Employees, the Institution, or Training Records
- Per-entity document cards on Student and Employee Show pages (upload, download, delete)
- Institution-wide document library (`/admin/documents`) with filter bar (search, entity type, category)
- Upload form with entity selector — documents always linked to the correct person/org
- Secure download via authenticated controller route (`Storage::disk('local')->download()`)
- Accepted file types: PDF, Word, Excel, PNG/JPG/GIF/WebP — 10 MB max
- Feature flag: `feature_documents_enabled`
- Dashboard quick-action card

### Phase 8 — Staff Training Management
- Training course catalog with name, assigned trainer, description, and active/inactive status
- Training completion records: employee, course, date completed, trainer name, notes
- **Batch logging**: select multiple employees at once from a searchable checklist — one submission creates a record for each
- Trainer Name auto-populates from the selected course when logging a completion
- Document attachments on each training record (certificates, sign-in sheets) via the existing document system
- Staff Training card on the Employee Show page: table of completions with course, date, and trainer columns
- `Trainer` role added to all six departments (Education, Administration, Counseling, Cadre, Health Services, Operations)
- Feature flag: `feature_staff_training_enabled`
- Dashboard quick-action card

### Phase 9 — Academic Structure Reorganization
- **New hierarchy:** AcademicYear → Class → Cohort (Alpha/Bravo) → CohortCourse → Enrollment
- **Cohorts** auto-created (Alpha + Bravo) when a Class is saved
- **CohortCourse** (Course Assignment): links a catalog course to a cohort with instructor, room, schedule, capacity, and status
- **Instructor types:** Staff (live employee search), Technical College, or University (institution dropdown)
- **Educational Institutions** lookup table for colleges and universities used as external instructors
- **Enrollment** updated to reference `cohort_course_id` instead of `class_id`
- **Report Cards & Transcripts** updated to cohort-based GPA calculations
- Dashboard **Institutions** card added
- 34 new/updated tests; 368 total passing

### Phase 9 UI Improvements
- **CohortCourse Show page:** "+ Enroll Student" button pre-populates the enrollment form with the course pre-selected
- **Enrollment Create form:** supports `?cohort_course_id=` query param for pre-selection

### Class Form Reorganization (2026-02-25)
- **Create/Edit Class form:** removed Alpha/Bravo cohort date section; added **Class Name** field (e.g., "Cohort Alpha / Bravo") and **Start Date / End Date** fields directly on the class
- **Class Setup index:** replaced "Cohort Alpha Dates" and "Cohort Bravo Dates" columns with **Start Date** and **End Date** columns from the class record
- **Class Show page:** removed cohort view selector dropdown and cohort cards; now displays clean class detail card (Name, NGB Number, Academic Year, Status, Start Date, End Date)
- `classes` table gains three new columns: `name` (varchar, nullable), `start_date` (date, nullable), `end_date` (date, nullable)

### Post-Phase 9 UI & UX Improvements
- **Academy Setup** (feature-flagged): configure academy name, address, phone, director, year founded; read-only display with Edit button once data is saved; quick-action cards for Departments CRUD and Employee Roles CRUD; 4 "Coming Soon" placeholder cards
- **Departments CRUD**: create, edit, delete departments; delete guarded if employees exist
- **Employee Roles CRUD**: create, edit, delete roles scoped to a department; filter by department; delete guarded if employees exist
- **Class Layout hub**: dashboard navigation hub grouping Academic Years, Class Management, and Course Catalog under a single card
- **Course Catalog delete**: delete button added to the Courses index (desktop table + mobile card)
- **Enrollment date format**: all enrollment date displays standardised to `MM-DD-YYYY` (Students index, Students show, Enrollment index, CohortCourse show)
- **Academic Year status**: replaced `is_current` boolean with `status` enum (`forming` / `current` / `completed`); colour-coded badge on index and show pages; dropdown on create/edit
- **Pass/Fail grading**: courses carry a `grading_type` (`credit_system` | `pass_fail`); Credits field hidden when Pass/Fail is selected; index displays **P/F** in the Credits column
- **Role-based card visibility**: `user` role sees only profile — User Management, Employee Management, and Academy Setup cards hidden on dashboard; Department Management visible to `site_admin` only
- **Zebra-striped tables**: alternating row shading on Users, Employees, and Students index pages
- **Hire date optional**: self-registered users get a `joined` date from `created_at`; Employee `hire_date` remains null until manually set

### Query Optimization & Bug Fixes (2026-02-26)
- **N+1 fix — Attendance class summary**: replaced per-student query loop with a single bulk query grouped in PHP
- **Employee show — enrollment count**: added `withCount('enrollments')` to `classCourses` eager-load so the capacity display renders correctly
- **Assessment & Attendance templates**: corrected `class_model` → `class_course` JSON key and `section_name` path in three Vue templates (course column was always "N/A")
- **Employee role search**: extended to match department name in addition to role name
- **Report cards index**: filtered to students with at least one graded enrollment (removes students with no grades from the listing)
- **System department protection**: seeded departments (Education, Administration, etc.) protected from deletion and renaming

### Cross-Cutting
- **Breadcrumb navigation** on all admin child pages
- **Feature Settings** page (site_admin only): toggle Attendance, Theme, Documents, Grade Management, Report Cards, Staff Training, Academy Setup
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
php artisan test --filter CohortCourseTest

# Clear config cache first (recommended)
composer run test
```

**Current status:** 370 tests, 1957 assertions, all passing.

---

## Key URLs

| URL | Description |
|-----|-------------|
| `/` | Landing page |
| `/admin` | Dashboard |
| `/admin/students` | Student management |
| `/admin/students/trashed` | Soft-deleted students (restore / force delete) |
| `/admin/guardians` | Guardian management |
| `/admin/employees` | Employee management |
| `/admin/employees/trashed` | Soft-deleted employees (restore / force delete) |
| `/admin/users` | User management |
| `/admin/class-layout` | Class Layout hub (Academic Years, Classes, Courses) |
| `/admin/academic-years` | Academic year management |
| `/admin/classes` | Class management |
| `/admin/class-courses` | Course assignments (class → course → instructor) |
| `/admin/courses` | Course catalog |
| `/admin/institutions` | Educational institutions (colleges & universities) |
| `/admin/enrollment` | Student enrollment |
| `/admin/assessments` | Assessment management |
| `/admin/assessment-categories` | Assessment category management |
| `/admin/grades` | Grade management |
| `/admin/grading-scales` | Grading scale configuration |
| `/admin/attendance` | Attendance tracking |
| `/admin/report-cards` | Student report cards (PDF) |
| `/admin/transcripts` | Student transcripts (PDF) |
| `/admin/student-notes` | Student notes (Operations) |
| `/admin/documents` | Document library |
| `/admin/training-courses` | Training course catalog |
| `/admin/training-records` | Staff training completion records |
| `/admin/custom-fields` | Custom field definitions |
| `/admin/audit-log` | Audit log viewer |
| `/admin/academy` | Academy information & setup hub |
| `/admin/departments` | Departments CRUD |
| `/admin/employee-roles` | Employee roles CRUD |
| `/admin/feature-settings` | Feature flag toggles (site_admin only) |
| `/admin/theme` | Theme customization |

---

## Project Structure

```
app/
├── Http/Controllers/Admin/    # Feature controllers
├── Models/                    # Eloquent models (29)
├── Observers/                 # Audit log observers
└── Http/Middleware/           # HandleInertiaRequests (shared props)

resources/js/
├── Pages/Admin/               # Vue page components (97+)
├── Components/UI/             # Reusable UI components
├── Components/Users/          # User form components
├── Layouts/                   # AuthenticatedLayout, GuestLayout
└── composables/               # useTheme.js, useDarkMode.js

database/
├── migrations/                # 38+ migrations
├── seeders/                   # 18 seeders
└── factories/                 # 22 factories

tests/Feature/Admin/           # 32 test files
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
| [OPEN_ISSUES.md](docs/OPEN_ISSUES.md) | Open decisions and issues pending resolution |

---

## Roadmap

| Phase | Description | Status |
|-------|-------------|--------|
| Phase 0 | Foundation, Auth, Theme | ✅ Done |
| Phase 1 | Students, Employees, Courses | ✅ Done |
| Phase 2 | Classes, Enrollment | ✅ Done |
| Phase 3A | Grading & Assessments | ✅ Done |
| Phase 3B | Report Cards & Transcripts | ✅ Done |
| Phase 3D | Soft Delete Restore UI | ✅ Done |
| Phase 3E | Audit Logging | ✅ Done |
| Phase 3F | Custom Fields | ✅ Done |
| Phase 4 | Attendance Management | ✅ Done |
| Phase 5 | Student Notes | ✅ Done |
| Phase 7 | Document Management | ✅ Done |
| Phase 8 | Staff Training Management | ✅ Done |
| Phase 9 | Academic Structure Reorganization | ✅ Done |
| Phase 3G | Custom Report Builder | 🔜 Next |
| Phase 3C | CSV Import & Bulk Export | ⏸ Deferred |
| Parent Portal | Parent/Guardian Portal | 📋 Planned |
| Academic Calendar | Calendar integration | 📋 Planned |

---

## Open Issues

See [docs/OPEN_ISSUES.md](docs/OPEN_ISSUES.md) for decisions pending resolution.

---

## License

© 2026 Brian K. Rich. All rights reserved.

---

## CI/CD

Pushes to `main` auto-deploy to AWS Lightsail via GitHub Actions (`.github/workflows/deploy.yml`).

**Pipeline steps:**
1. GitHub runner: `composer install` (for Ziggy), `npm ci`, `npm run build`
2. Server via SSH: `git pull`, `composer install`, `migrate`, `seed`, `composer --no-dev`
3. GitHub runner → server: `rsync public/build/` (compiled frontend assets)
4. Server via SSH: rebuild PHP caches, restart queue

The frontend build runs on GitHub's runner (7GB RAM) to avoid memory limits on the small Lightsail instance.

---

**Version:** 2.3.1
**Last Updated:** February 26, 2026
**Status:** Active Development
