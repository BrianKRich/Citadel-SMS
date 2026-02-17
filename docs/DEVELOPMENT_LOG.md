# Student Management System — Development Log

**Author:** Brian K. Rich
**Organization:** Georgia Job Challenge Academy
**Repository:** https://github.com/BrianKRich/Student-Management-System

This document records the implementation details, decisions, and outcomes for each development phase.

---

## Phase 0: Foundation & Theme System

**Dates:** February 8–9, 2026
**Commits:** `24c6c08` → `61af1b8` (8 commits)

### What Was Built

Scaffolded the Laravel 12 application with Vue 3 and Inertia.js, establishing the architecture all subsequent features build upon.

**Authentication (Laravel Breeze):**
- User registration, login, password reset, email verification
- Session management with secure cookies
- Role field on `users` table (`admin` / default user)
- `User::isAdmin()` helper method

**Theme System:**
- `settings` table as key/value store for theme colors
- `GET /api/theme` — the only true REST API endpoint in the app
- `useTheme.js` composable fetches theme on mount, converts hex to RGB, sets CSS custom properties
- `tailwind.config.js` extended with CSS variable references using `rgb(var(...) / <alpha-value>)` pattern
- 5 customizable color schemes with real-time preview via `ThemePreview.vue`
- Fallback colors: Navy `#1B3A6B`, Gold `#FFB81C`, Red `#C8102E` (GYCA brand)

**Dark Mode:**
- Tailwind `class` strategy — toggle adds/removes `dark` class on `<html>`
- `useDarkMode.js` composable with `localStorage` persistence
- `DarkModeToggle.vue` component in nav bar
- All components use `dark:` variant classes

**Layouts & Components:**
- `AuthenticatedLayout.vue` — nav, header slot, main content, footer
- `GuestLayout.vue` — centered card for auth pages
- Reusable UI library: `Card`, `PageHeader`, `Alert`, `Footer`, `DarkModeToggle`
- Form primitives: `TextInput`, `InputLabel`, `InputError`, `Checkbox`
- Buttons: `PrimaryButton`, `SecondaryButton`, `DangerButton`
- Navigation: `NavLink`, `ResponsiveNavLink`, `Dropdown`, `DropdownLink`

**Infrastructure:**
- PostgreSQL database (`sms`)
- Vite build with HMR
- `@/` path alias → `resources/js/`
- Responsive design with mobile and desktop support

**Documentation created:**
- `docs/ARCHITECTURE.md`, `docs/DATABASE.md`, `docs/BACKEND.md`, `docs/FRONTEND.md`, `docs/API.md`
- `docs/DARK_MODE.md`, `docs/RESPONSIVE_DESIGN.md`, `docs/SECURITY.md`
- `docs/COMPONENTS.md`, `docs/ADMIN_DASHBOARD.md`, `docs/USER_MANAGEMENT.md`

### Key Decisions

1. **Inertia.js over REST API** — Controllers return `Inertia::render()`, not JSON. No separate API layer for the web app. This simplified the stack but means the frontend and backend are tightly coupled.
2. **Theme via CSS variables** — Dynamic colors loaded at runtime rather than compiled into Tailwind. Enables runtime theme switching without rebuilding CSS.
3. **Composition API only** — All components use `<script setup>`. No Options API anywhere in the codebase.
4. **No Pinia** — State managed via Inertia shared props (`usePage()`) for global data and Vue `ref`/`computed` for local state. Noted as a future option if needed.

---

## Phase 1: Student & Course Foundation

**Date:** February 9, 2026
**Commits:** `61af1b8` → `b72a643` (3 commits)

### What Was Built

**Database (10 tables):**

| Table | Key Details |
|-------|-------------|
| `students` | Soft deletes, auto-ID `STU-YYYY-###` via `boot()`, status enum (active/inactive/graduated/withdrawn/suspended), photo upload |
| `guardians` | Name, relationship, contact info |
| `guardian_student` | Many-to-many pivot with `is_primary` flag, cascade deletes from both sides |
| `academic_years` | `is_current` flag, start/end dates |
| `terms` | FK to academic_year, `is_current` flag, type enum (semester/trimester/quarter) |
| `courses` | Unique course code, department FK, level, credits (decimal 5,2), status |
| `departments` | Name, code, description |
| `employees` | Soft deletes, auto-ID `EMP-YYYY-###`, department FK, role FK, qualifications |
| `employee_roles` | Role definitions (Teacher, Administrator, Counselor, etc.) |
| `phone_numbers` | Polymorphic (`phoneable_type`/`phoneable_id`) shared by Student and Employee |

Also: `counties` table with all 159 Georgia counties (2,460 lines in seeder).

**Models (14):**
- `Student`, `Guardian`, `Employee`, `EmployeeRole`, `PhoneNumber`
- `AcademicYear`, `Term`, `Course`, `Department`, `County`, `Setting`
- All models define `$fillable`, `$casts`, relationships, and query scopes (`scopeSearch`, `scopeStatus`, `scopeActive`)

**Controllers (7):**
- `StudentController`, `EmployeeController`, `GuardianController`
- `CourseController`, `AcademicYearController`, `ClassController`
- `EnrollmentController`
- All follow RESTful naming, use inline `$request->validate()`, return `Inertia::render()`

**Frontend:**
- Admin dashboard with `StatCard` components showing real-time counts
- `AdminActionCard` quick-action navigation cards
- CRUD pages for Students, Employees, Courses
- Index pages with search, filtering, pagination (`paginate(10)->withQueryString()`)
- Photo upload for students via `Storage::disk('public')`

**Seeders (7):**
- `CountySeeder`, `DepartmentSeeder`, `EmployeeSeeder`, `AcademicYearSeeder`
- `CourseSeeder` (10 courses), `StudentSeeder` (20 students), `ClassSeeder`
- `ThemeSeeder` for default theme colors

### Key Decisions

1. **`ClassModel` naming** — PHP reserves `Class` as a keyword. Model named `ClassModel` with `protected $table = 'classes'`.
2. **Polymorphic phones** — Single `phone_numbers` table serves both Student and Employee via `morphMany`, avoiding duplicate phone tables.
3. **Auto-generated IDs in `boot()`** — `STU-YYYY-###` and `EMP-YYYY-###` generated in `static::creating()` callback, querying last ID in current year. Human-readable and year-scoped.
4. **Soft deletes on Student and Employee only** — 7-year data retention compliance requirement. Other models use hard deletes.
5. **Employee over Teacher** — Originally planned as "Teacher" model, broadened to "Employee" with roles to support non-teaching staff (counselors, administrators). `employee_id` FK on `classes` table instead of `teacher_id`.

---

## Phase 2: Class Scheduling & Enrollment

**Date:** February 9, 2026
**Commits:** `43a9cab` → `a0c12e0` (8 commits)

### What Was Built

**Database (2 tables):**

| Table | Key Details |
|-------|-------------|
| `classes` | FK to course, employee, academic_year, term. JSON `schedule` column: `[{day, start_time, end_time}]`. `max_students` capacity. Status enum (open/closed/in_progress/completed). Room field. |
| `enrollments` | FK to student, class. Unique constraint on `(student_id, class_id)`. Status enum (enrolled/dropped/completed/failed). `final_grade` and `grade_points` columns (nullable, populated later by Phase 3). |

**ClassModel computed attributes:**
- `getEnrolledCountAttribute()` — counts active enrollments
- `getAvailableSeatsAttribute()` — `max_students - enrolled_count`
- `isFull()` — boolean check

**Schedule conflict detection** in `ClassController`:
```
timesOverlap($start1, $end1, $start2, $end2) → ($start1 < $end2) && ($end1 > $start2)
```
Checks all same-day time slots for the instructor across existing classes in the same term.

**Enrollment validation** in `EnrollmentController` — four checks before enrolling:
1. Not already enrolled (unique constraint)
2. Class not full (`isFull()`)
3. Class status is `open`
4. No student schedule conflict (same overlap check applied to student's existing enrollments)

**Frontend updates:**
- Index pages for all admin modules (Classes, Enrollments, Academic Years)
- Quick Action cards on dashboard linked to real routes
- Drop/withdraw functionality for enrollments

**Shared Inertia props** via `HandleInertiaRequests.php`:
- `auth.user` — current authenticated user on every page
- `flash.success` / `flash.error` — flash messages for Alert component

### Key Decisions

1. **JSON schedule column** — Stored as `[{day, start_time, end_time}]` array rather than a separate `schedules` table. Simpler for the current scope; avoids join overhead for display.
2. **Capacity enforcement in controller** — `max_students` checked at enrollment time, not via database constraint. Allows flexibility for admin overrides in the future.
3. **`is_current` flags** — `AcademicYear` and `Term` each have `is_current` boolean. Enforced in controller logic (unset all others before setting new current), not at DB level. Trade-off: simpler schema, requires careful controller code.
4. **Enrollment `final_grade` and `grade_points`** — Added as nullable columns in Phase 2 migration, populated by Phase 3's `GradeCalculationService`. Forward-planned to avoid a Phase 3 migration on the enrollments table.

---

## Branding & Configuration

**Dates:** February 9–10, 2026
**Commits:** `a657534` → `84da428` (8 commits)

### What Changed

- Renamed from "Citadel SMS" to "Student Management System"
- Organization set to Georgia Job Challenge Academy
- Copyright notices updated to Brian K. Rich
- Restored original logo and branding assets
- Replaced Teacher model with broader Employee system
- Added class and employee seed data
- Database renamed to `sms` in all documentation
- Cleaned up all legacy naming references across docs and config

---

## Deployment & DevOps

**Date:** February 12, 2026
**Commits:** `4fdbd08` → `c50a025` (7 commits)

### What Was Built

**CI/CD Pipeline** (`.github/workflows/deploy.yml`):
- Trigger: push to `main` branch + manual `workflow_dispatch`
- SSH into AWS Lightsail instance at `18.191.102.47`
- Runs: `git pull`, `composer install`, `npm install --legacy-peer-deps`, `npm run build`, `php artisan migrate --force`
- Uses GitHub secrets for SSH key and deploy path

### Problems Solved

1. **npm peer dependency conflicts** — Vue 3 + some packages had peer dep mismatches. Fixed with `--legacy-peer-deps` flag.
2. **Ubuntu permission issues** — Removed `chmod` step that the `ubuntu` user lacked permission to run on Lightsail.
3. **Deploy path configuration** — Changed from hardcoded path to `DEPLOY_PATH` environment variable passed to SSH action.

---

## Phase 3A: Core Grading System

**Date:** February 15, 2026
**Commits:** `a06404c` → `3551e58` (2 commits — planning doc + implementation)

### What Was Built

**Database (4 tables):**

| Table | Key Details |
|-------|-------------|
| `grading_scales` | JSON `scale` column: `[{letter, min_percentage, gpa_points}]`. `is_default` boolean. Default scale: A=90/4.0, B=80/3.0, C=70/2.0, D=60/1.0, F=0/0.0. |
| `assessment_categories` | Optional `course_id` FK (NULL = global/reusable template). `weight` decimal(5,2). ON DELETE SET NULL for course FK. |
| `assessments` | FK to class and category. `max_score`, optional `weight` override, `is_extra_credit` boolean, `status` enum (draft/published). Index on `class_id`. |
| `grades` | FK to enrollment and assessment. Unique on `(enrollment_id, assessment_id)`. `score`, `is_late`, `late_penalty`, `graded_by` FK to users, `graded_at`. |

**Models (4 new):**
- `GradingScale` — `getLetterGrade(float)`, `getGpaPoints(string)`, `setDefault()`, `scopeDefault()`
- `AssessmentCategory` — scopes: `search()`, `course()`, `global()`
- `Assessment` — scopes: `class()`, `category()`, `published()`, `draft()`, `search()`. Accessor: `effective_weight`
- `Grade` — accessor: `adjusted_score` (applies late penalty: `score - (score * late_penalty / 100)`)

**Updated models:**
- `ClassModel` → added `assessments()` hasMany
- `Course` → added `assessmentCategories()` hasMany
- `Enrollment` → added `calculateFinalGrade()` delegating to service

**GradeCalculationService** (`app/Services/GradeCalculationService.php`) — 5 methods:

| Method | Logic |
|--------|-------|
| `calculateWeightedAverage(Enrollment)` | Groups grades by category, computes earned/max ratio per category, applies category weight, normalizes if weights don't sum to 1.0. Extra credit is additive. Clamps to [0, 100]. |
| `calculateTermGpa(Student, Term)` | Credit-weighted GPA: `sum(grade_points * credits) / sum(credits)`. Falls back to 1 credit if course credits is null. |
| `calculateCumulativeGpa(Student)` | Same as term GPA but across all enrollments. |
| `updateEnrollmentGrade(Enrollment)` | Calls `calculateWeightedAverage` → looks up default `GradingScale` → gets letter and GPA points → updates enrollment. |
| `updateAllClassGrades(ClassModel)` | Batch calls `updateEnrollmentGrade` for all enrolled students. |

**Controllers (4 new):**
- `AssessmentCategoryController` — resource CRUD, weight validation 0-1, blocks destroy if linked assessments exist
- `AssessmentController` — resource CRUD, filters by class/category/status, show includes grade stats (avg/min/max)
- `GradeController` — `index` (class list), `classGrades` (matrix), `enter` (bulk form), `store` (bulk save + recalc), `studentGrades` (per-student view)
- `GradingScaleController` — resource CRUD + `setDefault()` action

**Vue Pages (14 new):**
- `AssessmentCategories/` — Index, Create, Edit
- `Assessments/` — Index, Create, Edit, Show
- `GradingScales/` — Index, Create, Edit
- `Grades/` — Index, ClassGrades (matrix with color-coded scores), Enter (spreadsheet-style bulk entry), StudentGrades (per-student with cumulative GPA)

**Seeders (4 new):**
- `GradingScaleSeeder` — standard A-F scale
- `AssessmentCategorySeeder` — Homework (.15), Quizzes (.15), Midterm (.25), Final (.30), Projects (.15)
- `AssessmentSeeder` — sample assessments for seeded classes
- `GradeSeeder` — sample grades for seeded enrollments

**Factories (13 new):**
- All models needed for testing: `AcademicYear`, `Term`, `Course`, `Department`, `EmployeeRole`, `Employee`, `ClassModel`, `Student`, `Enrollment`, `AssessmentCategory`, `Assessment`, `Grade`, `GradingScale`
- Added `HasFactory` trait to `ClassModel`, `Department`, `EmployeeRole`
- `Enrollment::factory()->withGrade('A', 4.0)` state for convenient test setup

**Dashboard updates:**
- `AdminController::dashboard()` — added total assessments, grades this week, average GPA stats
- `Dashboard.vue` — added "Grade Management" action card

**Test suite (6 new files, 41 new tests):**

| File | Tests |
|------|-------|
| `Unit/Models/GradingScaleTest.php` | Letter grade boundaries, GPA point mapping |
| `Unit/Services/GradeCalculationServiceTest.php` | Weighted average (single/multi category), extra credit, late penalty, enrollment grade update, term GPA, cumulative GPA |
| `Feature/Admin/AssessmentCategoryTest.php` | Index, auth redirect, store (valid + validation), update, destroy (empty + linked) |
| `Feature/Admin/AssessmentTest.php` | Index + filters, store + validation, show with grades, update, destroy |
| `Feature/Admin/GradeTest.php` | Class matrix, bulk entry form, bulk store + validation + recalculation, student grades, unique constraint |
| `Feature/Admin/GradingScaleTest.php` | Index, store + JSON validation, set default unsets others, prevent deleting default |

**Total after 3A: 66 tests, 228 assertions — all passing.**

**Test infrastructure fix:** Updated `phpunit.xml` to use PostgreSQL `sms_testing` database with `RefreshDatabase` trait (was previously configured for SQLite).

### Key Decisions

1. **`assessment_categories` with optional `course_id`** — Deviated from original plan which called for global-only `assessment_types`. Nullable `course_id` enables both global templates (NULL) and course-specific categories. ON DELETE SET NULL preserves categories if a course is deleted.
2. **Weight normalization** — If category weights don't sum to 1.0, the service normalizes. This prevents incorrect averages when not all categories have assessments yet.
3. **Extra credit is additive** — Not part of normal weighting. Calculated separately and added on top of the weighted average, then clamped to 100.
4. **Late penalty as percentage** — `adjusted_score = score - (score * late_penalty / 100)`. A 10% penalty on a 100-point score yields 90.
5. **Bulk grade entry** — `Grade::updateOrCreate` with composite key `(enrollment_id, assessment_id)`. Supports both initial entry and corrections in the same form.
6. **`effective_weight` accessor** — Assessment can override its category's weight. `$assessment->weight ?? $category->weight`. Enables per-assignment weight tweaking without changing the category default.
7. **Status badge pattern** — Each index page defines a local `getStatusBadgeClass()` function rather than a shared utility. Documented as a project convention in CLAUDE.md.

### Known Issue (discovered during 3B planning)

`ClassGrades.vue` references `enrollment.weighted_average` and `enrollment.final_letter_grade` but neither exists on the Enrollment model or database. The `weighted_average` column doesn't exist, and the model stores letter grades as `final_grade` not `final_letter_grade`. These fields render as "—" in the UI. Scheduled for fix in Phase 3B Step 1.

---

## Project Statistics (as of Phase 3A)

| Metric | Value |
|--------|-------|
| Total commits | 47 |
| Development period | Feb 8–16, 2026 |
| Phases completed | 4 (Phase 0–2 + 3A) |
| PHP code | ~3,977 lines |
| Vue code | ~7,317 lines |
| Database tables | 22 |
| Eloquent models | 18 |
| Admin controllers | 11 |
| Vue pages | 36 |
| Vue components | 23 |
| Seeders | 13 |
| Factories | 14 |
| Test files | 15 |
| Tests passing | 66 (228 assertions) |
| Services | 1 (GradeCalculationService) |
| Contributors | 1 |

---

## Phase 3B Step 1: Foundation, Class Rank & DomPDF

**Date:** February 16, 2026
**Commit:** `38d11d9`

### What Was Built

**Database migration:**
- Added `weighted_average` decimal(5,2) column to `enrollments` table
- Renamed `final_grade` → `final_letter_grade` on `enrollments` table

**Model updates:**
- Updated `Enrollment` model: `$fillable` and `$casts` now include `weighted_average` and `final_letter_grade`
- Updated `EnrollmentFactory` to use `final_letter_grade` and `weighted_average`

**GradeCalculationService — new method:**

| Method | Logic |
|--------|-------|
| `calculateClassRank(ClassModel, Term)` | Standard competition ranking (1,1,3). Queries enrollments for given class/term, orders by `weighted_average` DESC, handles ties. Returns array of `[enrollment_id => rank]`. |

**Package installed:**
- `barryvdh/laravel-dompdf` — PDF generation library for rendering Blade templates to PDF

**Tests:** 66 passing (228 assertions) — no regressions.

---

## Phase 3B Step 2: Blade PDF Templates

**Date:** February 16, 2026
**Commit:** `9d6de03`

### What Was Built

**DomPDF configuration:**
- Published `config/dompdf.php` via `vendor:publish`

**PDF templates (2 new Blade files):**

| Template | Purpose |
|----------|---------|
| `resources/views/pdf/report-card.blade.php` | Per-student, per-term report card. Header with school name, student info, term/academic year. Grades table: Course Code, Course Name, Section, Teacher, Avg %, Grade, GPA Pts, Credits. Footer with Term GPA, Cumulative GPA, generation date. |
| `resources/views/pdf/transcript.blade.php` | Multi-term transcript. Conditional "OFFICIAL"/"UNOFFICIAL" header. Rotated low-opacity watermark for unofficial. Student info block. Per-term sections with course table + term GPA/credits. Cumulative summary. Signature lines for official mode. |

**Template design decisions:**
- All CSS inline for DomPDF compatibility (no external stylesheets)
- Font: DejaVu Sans (bundled with DomPDF, no font installation needed)
- Page size: Letter (8.5 × 11)
- `page-break-inside: avoid` on term blocks in transcript
- Null-safe rendering with `?? '—'` fallbacks for missing grades
- Striped table rows via `nth-child(even)` background color

**Tests:** 66 passing (228 assertions) — templates are inert until wired to services in Step 3.

---

## Project Statistics (as of Phase 3B Step 2)

| Metric | Value |
|--------|-------|
| Total commits | 49 |
| Development period | Feb 8–16, 2026 |
| Phases completed | 4 (Phase 0–2 + 3A) |
| Phase 3B steps completed | 2 of 7 |
| Database tables | 22 |
| Eloquent models | 18 |
| Admin controllers | 11 |
| Vue pages | 36 |
| Vue components | 23 |
| Blade PDF templates | 2 |
| Services | 1 (GradeCalculationService) |
| Test files | 15 |
| Tests passing | 66 (228 assertions) |
| Contributors | 1 |

---

## Expanded Seed Data

**Date:** February 16, 2026

### What Was Built

Scaled up all seeders from minimal sample data to realistic volumes for development and demo purposes. Created a new `EnrollmentSeeder` to bridge students and classes with grade data.

**Changes by seeder:**

| Seeder | Before | After |
|--------|--------|-------|
| `CourseSeeder` | 5 courses | 20 courses across 10 departments |
| `EmployeeSeeder` | 4 teachers + 3 staff | 20 teachers + 3 staff (23 total) |
| `StudentSeeder` | 3 students | 100 students (3 named + 97 factory) |
| `ClassSeeder` | 10 classes (5+5) | 40 classes (20 Fall + 20 Spring) |
| `EnrollmentSeeder` | did not exist | ~500 enrollments (5 per student) |
| `AssessmentSeeder` | auto-covers new classes | 120 assessments (6 × 20 Fall classes) |
| `GradeSeeder` | auto-covers new enrollments | ~2,500 grades |

**New departments/subjects added:** Mathematics (Geometry), English (Composition), Science (Biology, Chemistry), History (U.S. History), Computer Science (Web Development), Physical Education (Fitness, Team Sports), Health, Art (Visual Arts, Digital Media), Music, Foreign Language (Spanish I & II), Life Skills (Financial Literacy).

**Key design decisions:**
- Each of 20 teachers teaches exactly 1 course, 1 class per term — no schedule conflicts
- `EnrollmentSeeder` respects `max_students` capacity per class
- Factory students use `Student::factory()->count(97)->create()` for realistic Faker-generated data
- Existing `AssessmentSeeder` and `GradeSeeder` required zero changes — they already loop over all `in_progress` classes and enrolled students

**Tests:** 66 passing (228 assertions) — no regressions.

---

## Current Status

**Expanded seed data** complete. Phase 3B Step 2 complete. Next: Step 3 (ReportCardService + TranscriptService). Tracked in GitHub Issue #6.

**Roadmap:** Phase 3 (Issue #6) → 4: Attendance (#5) → 5: Guardian Portal (#4) → 6: Calendar (#3) → 7: Documents (#2) → 8: Reporting (#1)
