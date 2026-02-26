# Open Issues — Decisions Needed

## Issue #1: Course Assignments Navigation
**Status:** Resolved — February 25, 2026

The Class Show page no longer displays cohort cards or course assignment rows. Course Assignments (`/admin/class-courses`) are accessible directly via the Class Layout hub or the Course Catalog. The cohort Alpha/Bravo distinction has been removed from all class management UI; classes now carry a `name` field, `start_date`, and `end_date` directly.

---

## Issue #2: Academy Setup — Departments & Roles Pre-Populated
**Status:** Open
**Date Raised:** 2026-02-24

The Departments and Employee Roles tables are seeded by `DepartmentSeeder` at initial setup (Education, Administration, Counseling, Cadre, Health Services, Operations + Trainer role per dept). The new Departments CRUD and Employee Roles CRUD pages allow admins to manage these after the fact.

**Decision needed:** Should the seeded departments/roles be locked (read-only) or fully editable? Currently they are fully editable and deletable (with the guard that no employees are assigned). If the seeded data is considered foundational, deleting a core department could break employee assignments silently if employees are soft-deleted.

**Recommendation:** Add a `is_system` boolean flag to `departments` to mark seeded records as non-deletable, or add a warning in the UI.

**GitHub Issue:** #21

---

## Issue #3: Phase 3G — Custom Reports
**Status:** Next Up
**GitHub Issue:** #15

Admins can define, save, and run reusable reports across all data categories without developer help. Planned features: column picker, baked-in filters, save/share, PDF/CSV export, integration with Phase 3F custom fields via `cf_{id}` prefix.

---

## Issue #4: Performance & Infrastructure
**Status:** Planned
**GitHub Issue:** #14

- Search: `LIKE '%query%'` will degrade at scale; consider PostgreSQL `tsvector`
- Query caching for reference data (terms, academic years, courses)
- Staging environment (currently push to main → direct production deploy)
- Automated database backup strategy for production PostgreSQL

---

## Issue #5: CSV Import & Bulk Export
**Status:** Deferred
**GitHub Issue:** #11

Bulk student import from CSV/Excel, bulk grade import, enrollment import from external systems, and CSV/Excel export from all index pages.

---

## Issue #6: Parent/Guardian Portal
**Status:** Planned
**GitHub Issue:** #4

Non-admin read-only portal for guardians: student profile, schedule, grades, report card PDF, attendance summary.

---

## Issue #7: Academic Calendar
**Status:** Planned
**GitHub Issue:** #3

`calendar_events` table, admin CRUD, monthly grid view, iCal export. Integration with attendance for auto-excused on holidays.

---

## Issue #8: Reporting & Analytics
**Status:** Planned
**GitHub Issue:** #1

Dashboard-level insights: GPA distribution, enrollment trends, attendance rates, at-risk student identification, PDF/CSV export.
