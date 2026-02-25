# Open Issues — Decisions Needed

## Issue #1: Course Assignments Navigation
**Status:** Updated — February 25, 2026

The Class Show page no longer displays cohort cards or course assignment rows. Course Assignments (`/admin/cohort-courses`) are accessible directly via the Class Layout hub or the Course Catalog. The cohort Alpha/Bravo distinction has been removed from all class management UI; classes now carry a `name` field, `start_date`, and `end_date` directly.

---

## Issue #2: Academy Setup — Departments & Roles Pre-Populated
**Status:** Open
**Date Raised:** 2026-02-24

The Departments and Employee Roles tables are seeded by `DepartmentSeeder` at initial setup (Education, Administration, Counseling, Cadre, Health Services, Operations + Trainer role per dept). The new Departments CRUD and Employee Roles CRUD pages allow admins to manage these after the fact.

**Decision needed:** Should the seeded departments/roles be locked (read-only) or fully editable? Currently they are fully editable and deletable (with the guard that no employees are assigned). If the seeded data is considered foundational, deleting a core department could break employee assignments silently if employees are soft-deleted.

**Recommendation:** Add a `is_system` boolean flag to `departments` to mark seeded records as non-deletable, or add a warning in the UI.
