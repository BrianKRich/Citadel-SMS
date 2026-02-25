# SMS Project Memory

## Lightsail Instance
- **Host:** 18.191.102.47
- **User:** ubuntu
- **SSH Key:** /home/keith/pem/lightsailsslkey.pem
- **Connect:** `ssh -i /home/keith/pem/lightsailsslkey.pem ubuntu@18.191.102.47`
- **Deploy path:** /var/www/citadel-sms
- **Seeding prod:** Requires `composer install` first (faker is a dev dep), then `composer install --no-dev` after

## Project
- **Repo:** https://github.com/BrianKRich/Student-Management-System
- **Local path:** /home/keith/projects/sms
- **Stack:** Laravel 12 + Vue 3 + Inertia.js + PostgreSQL
- **Tests:** 368 passing (no new tests since Phase 3F)
- **Vue pages:** 97+

## Completed Phases
- Phase 0: Foundation, theme system, dark mode, auth
- Phase 1: Students, courses, guardians, employees
- Phase 2: Classes, enrollment, academic years
- Phase 3A: Grading & assessments
- Phase 3B: Report cards & transcripts (PDF)
- Phase 3D: Soft delete restore UI
- Phase 3E: Audit Logging (observers, purge with reason + immutable purge log, dashboard card)
- Phase 3F: Custom Fields (EAV on Student/Employee/Course/Class/Enrollment; inline toggle; 23 tests)
- Phase 4: Attendance Management with Feature Flag
- Phase 5: Student Notes (dept-scoped notes; audit logged; Operations dept; auto-Employee on User create)
- Site Admin role: isAdmin() includes site_admin; isSiteAdmin() gates Audit Log purge and Feature Settings
- User Management: hire_date nullable on edit; employee record always created on user create
- Phase 7: Document Management (private local-disk storage; Student/Employee/Institution docs; feature-flagged; 20 tests)
- Phase 8: Staff Training Management (training_courses + training_records; batch logging; feature-flagged; 32 tests)
- Phase 9: Academic Structure Reorganization (AcademicYear → Class → ClassCourse → Enrollment)
- Class Form Reorganization (2026-02-25): removed Alpha/Bravo cohort UI; added name, start_date, end_date to classes

## Session 2026-02-25 UX Hardening
- **Academic Year status:** replaced `is_current` boolean with `status` enum (forming/current/completed); migration run on prod
- **Pass/Fail grading:** `grading_type` column on courses (credit_system|pass_fail); Credits hidden for pass_fail; Index shows P/F; migration run
- **Role-based card visibility:** user role hides User Mgmt, Employee Mgmt, Academy Setup; Dept Mgmt visible to site_admin only
- **Zebra striping:** Users, Employees, Students index tables
- **Hire date optional:** nullable on user edit; Employee hire_date not overwritten unless provided
- **Class Index default:** academic year filter defaults to current year

## Phase 9: Class Structure — Key Details (Cohort layer REMOVED)
- **Hierarchy:** `AcademicYear → Class → ClassCourse → Enrollment / Assessment / AttendanceRecord`
- **ClassModel:** has `hasMany(ClassCourse, 'class_id')`
- **ClassCourse instructor_type:** enum `staff|technical_college|university`
- **Enrollment FK:** `class_course_id`
- **Routes:** `admin.class-courses.*`, `admin.institutions.*`, `api.employees.search`
- **ReportCardController:** Passes `currentClass` (not `class` — reserved JS keyword)
- **TranscriptController:** Passes `classGroups`

## Dashboard Layout Pattern
- Quick Actions is TWO grids:
  1. Main grid (`lg:grid-cols-3`) — operational + conditional cards
  2. Admin config row (`sm:grid-cols-3`, `mt-5`) — always: Audit Log | Custom Fields | Feature Settings
- New feature cards go in main grid only

## Document Management (Phase 7)
- **Feature flag:** `feature_documents_enabled`
- **Storage:** `local` disk; path `documents/{EntityType}/{entity_id}/{uuid}_{filename}`
- **Download:** plain `<a href>` (NOT Inertia Link)
- **Institution docs:** `entity_type='Institution'`, `entity_id=0`

## Staff Training (Phase 8)
- **Feature flag:** `feature_staff_training_enabled`
- **Tables:** `training_courses` + `training_records`
- **TrainingRecord** is a valid entity type in DocumentController

## Academy Setup Feature
- **Feature flag:** `feature_academy_setup_enabled`
- **Settings keys:** `academy_name`, `academy_address`, `academy_phone`, `academy_director`, `academy_year_started`
- **Vue pages:** `Admin/Academy.vue`, `Admin/Departments/{Index,Create,Edit}`, `Admin/EmployeeRoles/{Index,Create,Edit}`
- **Dept Mgmt visibility:** filtered via `usePage()` + computed `visibleActions` (siteAdminOnly flag)

## Open GitHub Issues
- **#21:** Academy Setup — protect seeded departments from deletion (`is_system` flag)
- **#15:** Phase 3G: Custom Reports — next in queue
- **#14:** Performance & Infrastructure Improvements
- **#11:** Phase 3C: CSV Import & Bulk Export (deferred)
- **#4:** Phase 5: Parent/Guardian Portal
- **#3:** Phase 6: Academic Calendar
- **#1:** Phase 9: Reporting & Analytics

## Dev Server Workflow
- **Always use:** `php artisan serve --host=127.0.0.1 --port=8000` in background
- **Never use:** `composer run dev` — conflicts with background serve on port 8000
- **After code changes:** run `npm run build` then restart serve

## Key Technical Notes
- **Vite build required:** After creating new Vue pages, run `npm run build` before tests
- **Route order matters:** Custom routes (trashed, restore, download) must be defined BEFORE `Route::resource()`
- **Feature flags:** Stored in `settings` table; shared via `HandleInertiaRequests` as `$page.props.features.*`
- **CI/CD:** Push to `main` auto-deploys to Lightsail via GitHub Actions. Frontend build runs on GitHub runner. Server never runs Node/npm.
- **No co-author credits in commits** — CLAUDE.md explicitly forbids this

## Key Custom Fields Notes
- Name generated with `Str::slug($label, '_')`
- Trait `SavesCustomFieldValues` in `App\Http\Controllers\Concerns\`
- `CustomField::forEntityWithValues(type, id)` returns fields with `pivot_value` pre-attached
- Toggle endpoint: `POST admin/custom-fields/{customField}/toggle` — must be defined BEFORE resource route
