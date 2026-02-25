# Open Issues — Decisions Needed

## Issue #1: Course Assignments Navigation
**Status:** Resolved — February 24, 2026

Course Assignments (`/admin/cohort-courses`) remains accessible from the Class Show page. The new **Class Layout** hub (`/admin/class-layout`) consolidates Academic Years, Class Management, and Course Catalog under one navigation card on the Dashboard. Course Assignments are reachable via Class Layout → Class Management → Class Show → cohort course rows.

If a direct top-level Course Assignments card is needed in the future, it can be added to the Class Layout hub page.

---

## Issue #2: Academy Setup — Departments & Roles Pre-Populated
**Status:** Open
**Date Raised:** 2026-02-24

The Departments and Employee Roles tables are seeded by `DepartmentSeeder` at initial setup (Education, Administration, Counseling, Cadre, Health Services, Operations + Trainer role per dept). The new Departments CRUD and Employee Roles CRUD pages allow admins to manage these after the fact.

**Decision needed:** Should the seeded departments/roles be locked (read-only) or fully editable? Currently they are fully editable and deletable (with the guard that no employees are assigned). If the seeded data is considered foundational, deleting a core department could break employee assignments silently if employees are soft-deleted.

**Recommendation:** Add a `is_system` boolean flag to `departments` to mark seeded records as non-deletable, or add a warning in the UI.
