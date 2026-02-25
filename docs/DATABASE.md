# Student Management System - Database Architecture

**Version:** 3.3 (Phase 0-3F + 4 + Student Notes + Academic Year Status + Course Grading Type)
**Last Updated:** February 25, 2026
**Database:** PostgreSQL 14+

---

## Table of Contents

- [Overview](#overview)
- [Database Schema](#database-schema)
- [Table Relationships](#table-relationships)
- [Indexes](#indexes)
- [Constraints](#constraints)
- [Migration History](#migration-history)
- [Seeding Strategy](#seeding-strategy)
- [Query Optimization](#query-optimization)

---

## Overview

Student Management System uses PostgreSQL as its primary database. The schema is designed to support comprehensive student management including:

- Student records and demographics
- Guardian relationships
- Academic year and term management
- Course catalog
- Employee profiles (staff, instructors, counselors)
- Department and role management
- Class scheduling
- Enrollment tracking
- Grade management
- Attendance records
- Student notes (department-scoped)

**Design Principles:**
- Normalized schema (3NF) to reduce data redundancy
- Foreign key constraints for referential integrity
- Soft deletes for data retention
- Auto-generated unique IDs for students and employees
- Polymorphic relationships for flexible associations (phone numbers)
- Indexes on frequently queried columns

---

## Database Schema

### Core Tables

#### **users**
Primary authentication table for all system users.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | NOT NULL | User's full name |
| email | VARCHAR(255) | UNIQUE, NOT NULL | Email address (login) |
| email_verified_at | TIMESTAMP | NULLABLE | Email verification timestamp |
| password | VARCHAR(255) | NOT NULL | Bcrypt hashed password |
| remember_token | VARCHAR(100) | NULLABLE | Remember me token |
| created_at | TIMESTAMP | | Creation timestamp |
| updated_at | TIMESTAMP | | Last update timestamp |

**Relationships:**
- Has one Student (user_id)
- Has one Guardian (user_id)
- Has one Employee (user_id)

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (email)

---

#### **students**
Complete student records with demographics and enrollment information.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| student_id | VARCHAR(255) | UNIQUE, NOT NULL | Auto-generated ID (STU-YYYY-###) |
| first_name | VARCHAR(255) | NOT NULL | Student's first name |
| last_name | VARCHAR(255) | NOT NULL | Student's last name |
| middle_name | VARCHAR(255) | NULLABLE | Student's middle name |
| date_of_birth | DATE | NOT NULL | Date of birth |
| gender | ENUM | NOT NULL | male, female, other |
| email | VARCHAR(255) | UNIQUE, NULLABLE | Student email |
| phone | VARCHAR(255) | NULLABLE | Phone number |
| address | TEXT | NULLABLE | Street address |
| city | VARCHAR(255) | NULLABLE | City |
| state | VARCHAR(255) | NULLABLE | State/province |
| postal_code | VARCHAR(255) | NULLABLE | ZIP/postal code |
| country | VARCHAR(255) | DEFAULT 'USA' | Country |
| emergency_contact_name | VARCHAR(255) | NULLABLE | Emergency contact name |
| emergency_contact_phone | VARCHAR(255) | NULLABLE | Emergency contact phone |
| enrollment_date | DATE | NOT NULL | Date enrolled |
| status | ENUM | NOT NULL, DEFAULT 'active' | active, inactive, graduated, withdrawn, suspended |
| photo | VARCHAR(255) | NULLABLE | Path to student photo |
| notes | TEXT | NULLABLE | Additional notes |
| user_id | BIGINT | FK, NULLABLE | Link to user account for portal |
| created_at | TIMESTAMP | | Creation timestamp |
| updated_at | TIMESTAMP | | Last update timestamp |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete timestamp |

**Relationships:**
- Belongs to User (user_id)
- Has many Guardians through guardian_student (many-to-many)
- Has many Enrollments
- Has many AttendanceRecords

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (student_id)
- UNIQUE (email)
- INDEX (user_id)
- INDEX (status)
- INDEX (deleted_at)

**Auto-Generated Fields:**
- `student_id` is auto-generated using format: STU-{YEAR}-{SEQUENCE}
  - Example: STU-2026-001, STU-2026-002
  - Sequence resets each year

---

#### **guardians**
Parent/guardian information and contact details.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| first_name | VARCHAR(255) | NOT NULL | Guardian's first name |
| last_name | VARCHAR(255) | NOT NULL | Guardian's last name |
| relationship | ENUM | NOT NULL | mother, father, guardian, grandparent, other |
| email | VARCHAR(255) | NULLABLE | Guardian email |
| phone | VARCHAR(255) | NOT NULL | Phone number |
| address | TEXT | NULLABLE | Street address |
| occupation | VARCHAR(255) | NULLABLE | Occupation |
| user_id | BIGINT | FK, NULLABLE | Link to user account for portal |
| created_at | TIMESTAMP | | Creation timestamp |
| updated_at | TIMESTAMP | | Last update timestamp |

**Relationships:**
- Belongs to User (user_id)
- Has many Students through guardian_student (many-to-many)

**Indexes:**
- PRIMARY KEY (id)
- INDEX (user_id)

---

#### **guardian_student** (Pivot Table)
Many-to-many relationship between guardians and students.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| guardian_id | BIGINT | FK, NOT NULL | Guardian reference |
| student_id | BIGINT | FK, NOT NULL | Student reference |
| is_primary | BOOLEAN | DEFAULT false | Primary guardian flag |
| created_at | TIMESTAMP | | Creation timestamp |
| updated_at | TIMESTAMP | | Last update timestamp |

**Relationships:**
- Belongs to Guardian (guardian_id)
- Belongs to Student (student_id)

**Indexes:**
- PRIMARY KEY (id)
- INDEX (guardian_id)
- INDEX (student_id)
- UNIQUE (guardian_id, student_id)

**Constraints:**
- FOREIGN KEY (guardian_id) REFERENCES guardians(id) ON DELETE CASCADE
- FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE

---

#### **academic_years**
Academic year definitions (e.g., 2025-2026).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | NOT NULL | Year name (e.g., "2025-2026") |
| start_date | DATE | NOT NULL | Academic year start date |
| end_date | DATE | NOT NULL | Academic year end date |
| status | VARCHAR(255) | DEFAULT 'forming' | forming, current, completed |
| created_at | TIMESTAMP | | Creation timestamp |
| updated_at | TIMESTAMP | | Last update timestamp |

**Relationships:**
- Has many Classes

**Indexes:**
- PRIMARY KEY (id)
- INDEX (status)

**Business Rules:**
- Only one academic year can have status = 'current' at a time
- `AcademicYear::setCurrent()` sets all years to 'forming', then this one to 'current'
- `scopeCurrent()` filters by `status = 'current'`

---

#### **terms**
Academic terms/semesters within an academic year.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| academic_year_id | BIGINT | FK, NOT NULL | Academic year reference |
| name | VARCHAR(255) | NOT NULL | Term name (e.g., "Fall 2025") |
| start_date | DATE | NOT NULL | Term start date |
| end_date | DATE | NOT NULL | Term end date |
| is_current | BOOLEAN | DEFAULT false | Current term flag |
| created_at | TIMESTAMP | | Creation timestamp |
| updated_at | TIMESTAMP | | Last update timestamp |

**Relationships:**
- Belongs to AcademicYear (academic_year_id)
- Has many Classes

**Indexes:**
- PRIMARY KEY (id)
- INDEX (academic_year_id)
- INDEX (is_current)

**Constraints:**
- FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE

---

#### **courses**
Course catalog (course definitions, not class instances).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| course_code | VARCHAR(255) | UNIQUE, NOT NULL | Unique code (e.g., MATH-101) |
| name | VARCHAR(255) | NOT NULL | Course name |
| description | TEXT | NULLABLE | Course description |
| credits | DECIMAL(5,2) | NULLABLE | Credit hours (null for pass_fail courses) |
| grading_type | VARCHAR(255) | DEFAULT 'credit_system' | credit_system, pass_fail |
| department | VARCHAR(255) | NULLABLE | Department (e.g., Mathematics) |
| level | VARCHAR(255) | NULLABLE | Level (Beginner, Intermediate, Advanced) |
| is_active | BOOLEAN | DEFAULT true | Active status |
| created_at | TIMESTAMP | | Creation timestamp |
| updated_at | TIMESTAMP | | Last update timestamp |

**Relationships:**
- Has many Classes

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (course_code)
- INDEX (department)
- INDEX (level)
- INDEX (is_active)

---

#### **departments**
Staff departments for organizational grouping.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | UNIQUE, NOT NULL | Department name |
| created_at | TIMESTAMP | | Creation timestamp |
| updated_at | TIMESTAMP | | Last update timestamp |

**Relationships:**
- Has many EmployeeRoles
- Has many Employees

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (name)

---

#### **employee_roles**
Job roles within each department.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| department_id | BIGINT | FK, NOT NULL | Department reference |
| name | VARCHAR(255) | NOT NULL | Role name |
| created_at | TIMESTAMP | | Creation timestamp |
| updated_at | TIMESTAMP | | Last update timestamp |

**Relationships:**
- Belongs to Department (department_id)
- Has many Employees (via role_id)

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (department_id, name) - role names unique per department

**Constraints:**
- FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE

---

#### **employees**
Staff employee profiles and employment details.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| employee_id | VARCHAR(255) | UNIQUE, NOT NULL | Auto-generated ID (EMP-YYYY-###) |
| user_id | BIGINT | FK, NULLABLE | Link to user account |
| first_name | VARCHAR(255) | NOT NULL | Employee's first name |
| last_name | VARCHAR(255) | NOT NULL | Employee's last name |
| email | VARCHAR(255) | UNIQUE, NOT NULL | Employee email |
| department_id | BIGINT | FK, NOT NULL | Department reference |
| role_id | BIGINT | FK, NOT NULL | Role reference |
| date_of_birth | DATE | NULLABLE | Date of birth |
| hire_date | DATE | NOT NULL | Date hired |
| qualifications | TEXT | NULLABLE | Qualifications/certifications |
| photo | VARCHAR(255) | NULLABLE | Path to employee photo |
| status | ENUM | NOT NULL, DEFAULT 'active' | active, inactive, on_leave |
| created_at | TIMESTAMP | | Creation timestamp |
| updated_at | TIMESTAMP | | Last update timestamp |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete timestamp |

**Relationships:**
- Belongs to User (user_id, nullable)
- Belongs to Department (department_id)
- Belongs to EmployeeRole (role_id)
- Has many Classes
- Has many PhoneNumbers (polymorphic)

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (employee_id)
- UNIQUE (email)
- INDEX (user_id)
- INDEX (department_id)
- INDEX (role_id)
- INDEX (status)
- INDEX (deleted_at)

**Auto-Generated Fields:**
- `employee_id` is auto-generated using format: EMP-{YEAR}-{SEQUENCE}
  - Example: EMP-2026-001, EMP-2026-002
  - Sequence resets each year

**Constraints:**
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
- FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE RESTRICT
- FOREIGN KEY (role_id) REFERENCES employee_roles(id) ON DELETE RESTRICT

---

#### **student_notes**
Department-scoped notes written by staff about students.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| student_id | BIGINT | FK, NOT NULL | Student reference (cascade delete) |
| employee_id | BIGINT | FK, NULLABLE | Author employee reference (null on delete) |
| department_id | BIGINT | FK, NOT NULL | Department that owns the note |
| title | VARCHAR(255) | NOT NULL | Note title |
| body | TEXT | NOT NULL | Note body |
| created_at | TIMESTAMP | | Creation timestamp |
| updated_at | TIMESTAMP | | Last update timestamp |

**Relationships:**
- Belongs to Student (student_id)
- Belongs to Employee (employee_id, nullable — preserved if employee deleted)
- Belongs to Department (department_id)

**Indexes:**
- PRIMARY KEY (id)
- INDEX (student_id, department_id) — composite for department-scoped queries

**Constraints:**
- FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
- FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE SET NULL
- FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE RESTRICT

**Access Control:**
- Admin users: see and manage all notes across all departments
- Employee users: see and manage only notes belonging to their department
- Notes survive employee deletion (employee_id set to null)

---

#### **phone_numbers**
Polymorphic phone number storage for any model.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| phoneable_type | VARCHAR(255) | NOT NULL | Owning model class |
| phoneable_id | BIGINT UNSIGNED | NOT NULL | Owning model ID |
| area_code | VARCHAR(3) | NULLABLE | Area code |
| number | VARCHAR(7) | NOT NULL | 7-digit phone number |
| type | VARCHAR(20) | NOT NULL | primary, mobile, home, work, emergency |
| label | VARCHAR(50) | NULLABLE | Custom label |
| is_primary | BOOLEAN | DEFAULT false | Primary phone flag |
| created_at | TIMESTAMP | | Creation timestamp |
| updated_at | TIMESTAMP | | Last update timestamp |

**Relationships:**
- Belongs to phoneable (Employee, Student, Guardian, etc.)

**Indexes:**
- PRIMARY KEY (id)
- INDEX (phoneable_type, phoneable_id)

---

#### **counties**
Georgia county reference data.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | UNIQUE, NOT NULL | County name |
| created_at | TIMESTAMP | | Creation timestamp |
| updated_at | TIMESTAMP | | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (name)

---

### Phase 2 Tables ✅ Implemented

#### **classes**
Class records (redesigned in Phase 9; further updated 2026-02-25).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| academic_year_id | BIGINT | FK, NOT NULL | Academic year reference |
| name | VARCHAR(255) | NULLABLE | Human-readable class name (e.g., "Cohort Alpha / Bravo") |
| class_number | VARCHAR(255) | NOT NULL | Class number (e.g., "42") |
| ngb_number | VARCHAR(255) | UNIQUE, NOT NULL | NGB identifier (e.g., "NGB-2025-042") |
| status | ENUM | DEFAULT 'forming' | forming, active, completed |
| start_date | DATE | NULLABLE | Class start date |
| end_date | DATE | NULLABLE | Class end date |
| created_at | TIMESTAMP | | Creation timestamp |
| updated_at | TIMESTAMP | | Last update timestamp |

> **Note:** Alpha/Bravo cohort designation is captured via the `name` field. The `cohorts` table still exists as an intermediary for `cohort_courses`, but cohort-specific dates and UI distinction have been removed from the class management screens.

---

#### **enrollments**
Student enrollment in classes.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| student_id | BIGINT | FK, NOT NULL | Student reference |
| class_id | BIGINT | FK, NOT NULL | Class reference |
| enrollment_date | DATE | NOT NULL | Date enrolled |
| status | ENUM | DEFAULT 'enrolled' | enrolled, dropped, completed, failed |
| weighted_average | DECIMAL(5,2) | NULLABLE | Calculated weighted average (0-100) |
| final_letter_grade | VARCHAR(10) | NULLABLE | Final letter grade (A, B, C, D, F) |
| grade_points | DECIMAL(5,2) | NULLABLE | GPA points for this enrollment |
| created_at | TIMESTAMP | | Creation timestamp |
| updated_at | TIMESTAMP | | Last update timestamp |

**Indexes:**
- UNIQUE (student_id, class_id) - prevent duplicate enrollments

---

### Phase 3 Tables ✅ Implemented (3A — Core Grading)

#### **grading_scales**
Configurable grading scales with letter grade thresholds and GPA points.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | NOT NULL | Scale name (e.g., "Standard") |
| is_default | BOOLEAN | DEFAULT false | Default scale flag |
| scale | JSON | NOT NULL | Array of `{letter, min_percentage, gpa_points}` |
| created_at | TIMESTAMP | | Creation timestamp |
| updated_at | TIMESTAMP | | Last update timestamp |

**JSON `scale` Format:**
```json
[
  { "letter": "A", "min_percentage": 90, "gpa_points": 4.0 },
  { "letter": "B", "min_percentage": 80, "gpa_points": 3.0 },
  { "letter": "C", "min_percentage": 70, "gpa_points": 2.0 },
  { "letter": "D", "min_percentage": 60, "gpa_points": 1.0 },
  { "letter": "F", "min_percentage": 0, "gpa_points": 0.0 }
]
```

**Business Rules:**
- Only one grading scale can have `is_default = true` at a time
- Default scale is used by `GradeCalculationService` for letter grade lookups

---

#### **assessment_categories**
Assessment categories per course with configurable weights (e.g., Homework 15%, Quizzes 15%, Exams 30%).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| course_id | BIGINT | FK, NULLABLE | Course reference (NULL = global default) |
| name | VARCHAR(255) | NOT NULL | Category name |
| weight | DECIMAL(5,2) | NOT NULL | Weight as decimal (0.15 = 15%) |
| description | TEXT | NULLABLE | Description |
| created_at | TIMESTAMP | | Creation timestamp |
| updated_at | TIMESTAMP | | Last update timestamp |

**Relationships:**
- Belongs to Course (course_id, nullable — NULL means global/reusable template)
- Has many Assessments

**Constraints:**
- FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL

**Design Note:** Originally planned as `assessment_types` (global-only). Implemented as `assessment_categories` with optional `course_id` FK, enabling both global templates (course_id = NULL) and course-specific categories.

---

#### **assessments**
Individual assessment instances within a class.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| class_id | BIGINT | FK, NOT NULL | Class reference |
| assessment_category_id | BIGINT | FK, NOT NULL | Category reference |
| name | VARCHAR(255) | NOT NULL | Assessment name |
| max_score | DECIMAL(8,2) | NOT NULL | Maximum possible score |
| due_date | DATE | NULLABLE | Due date |
| weight | DECIMAL(5,2) | NULLABLE | Override category weight |
| is_extra_credit | BOOLEAN | DEFAULT false | Extra credit flag |
| status | VARCHAR(255) | DEFAULT 'draft' | draft, published |
| created_at | TIMESTAMP | | Creation timestamp |
| updated_at | TIMESTAMP | | Last update timestamp |

**Indexes:**
- INDEX (class_id)

**Relationships:**
- Belongs to ClassModel (class_id)
- Belongs to AssessmentCategory (assessment_category_id)
- Has many Grades

**Constraints:**
- FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
- FOREIGN KEY (assessment_category_id) REFERENCES assessment_categories(id) ON DELETE CASCADE

---

#### **grades**
Individual student grades for assessments with late submission tracking.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| enrollment_id | BIGINT | FK, NOT NULL | Enrollment reference |
| assessment_id | BIGINT | FK, NOT NULL | Assessment reference |
| score | DECIMAL(8,2) | NOT NULL | Score earned |
| notes | TEXT | NULLABLE | Grading notes |
| is_late | BOOLEAN | DEFAULT false | Late submission flag |
| late_penalty | DECIMAL(5,2) | NULLABLE | Penalty percentage (e.g., 10.00 = 10%) |
| graded_by | BIGINT | FK, NULLABLE | User who graded |
| graded_at | TIMESTAMP | NULLABLE | Grading timestamp |
| created_at | TIMESTAMP | | Creation timestamp |
| updated_at | TIMESTAMP | | Last update timestamp |

**Indexes:**
- UNIQUE (enrollment_id, assessment_id) — one grade per student per assessment

**Constraints:**
- FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE CASCADE
- FOREIGN KEY (assessment_id) REFERENCES assessments(id) ON DELETE CASCADE
- FOREIGN KEY (graded_by) REFERENCES users(id) ON DELETE SET NULL

---

### Phase 4 Tables (To Be Implemented)

#### **attendance_records**
Daily attendance tracking.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| student_id | BIGINT | FK, NOT NULL | Student reference |
| class_id | BIGINT | FK, NOT NULL | Class reference |
| date | DATE | NOT NULL | Attendance date |
| status | ENUM | NOT NULL | present, absent, late, excused |
| notes | TEXT | NULLABLE | Notes |
| marked_by | BIGINT | FK, NOT NULL | User who marked |
| created_at | TIMESTAMP | | Creation timestamp |
| updated_at | TIMESTAMP | | Last update timestamp |

**Indexes:**
- UNIQUE (student_id, class_id, date) - one record per student per class per day

---

### Phase 5 Tables (To Be Implemented)

#### **calendar_events**
Academic calendar events.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| title | VARCHAR(255) | NOT NULL | Event title |
| description | TEXT | NULLABLE | Event description |
| event_type | ENUM | NOT NULL | holiday, exam, meeting, event, deadline |
| start_date | DATE | NOT NULL | Start date |
| end_date | DATE | NULLABLE | End date (for multi-day events) |
| all_day | BOOLEAN | DEFAULT true | All-day event flag |
| location | VARCHAR(255) | NULLABLE | Event location |
| created_by | BIGINT | FK, NOT NULL | Creator user ID |
| created_at | TIMESTAMP | | Creation timestamp |
| updated_at | TIMESTAMP | | Last update timestamp |

---

#### **documents**
Polymorphic document storage.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| documentable_type | VARCHAR(255) | NOT NULL | Model type (Student, Teacher, etc.) |
| documentable_id | BIGINT | NOT NULL | Model ID |
| title | VARCHAR(255) | NOT NULL | Document title |
| description | TEXT | NULLABLE | Description |
| file_path | VARCHAR(255) | NOT NULL | Storage path |
| file_type | VARCHAR(255) | NOT NULL | MIME type |
| file_size | BIGINT | NOT NULL | File size in bytes |
| uploaded_by | BIGINT | FK, NOT NULL | Uploader user ID |
| created_at | TIMESTAMP | | Creation timestamp |
| updated_at | TIMESTAMP | | Last update timestamp |

**Indexes:**
- INDEX (documentable_type, documentable_id) - polymorphic relationship

---

#### **settings**
System-wide settings (including theme).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| key | VARCHAR(255) | UNIQUE, NOT NULL | Setting key |
| value | TEXT | NULLABLE | Setting value (JSON or string) |
| created_at | TIMESTAMP | | Creation timestamp |
| updated_at | TIMESTAMP | | Last update timestamp |

**Current Settings:**
- `theme_primary_color` - Primary theme color (hex)
- `theme_secondary_color` - Secondary theme color (hex)
- `theme_accent_color` - Accent theme color (hex)
- `theme_background_color` - Background color (hex)
- `theme_text_color` - Text color (hex)

---

## Table Relationships

### Entity-Relationship Diagram

```
users
├── has one → students (user_id)
├── has one → guardians (user_id)
└── has one → employees (user_id)

students
├── belongs to → users (user_id)
├── has many → guardians (through guardian_student)
├── has many → enrollments
├── has many → attendance_records
└── has many → student_notes

guardians
├── belongs to → users (user_id)
└── has many → students (through guardian_student)

departments
├── has many → employee_roles
└── has many → employees

employee_roles
├── belongs to → departments
└── has many → employees (via role_id)

employees
├── belongs to → users (user_id, nullable)
├── belongs to → departments
├── belongs to → employee_roles (role_id)
├── has many → classes
└── has many → phone_numbers (polymorphic)

phone_numbers (polymorphic)
└── belongs to → phoneable (Employee, Student, Guardian, etc.)

counties
└── (reference data, no relationships defined)

academic_years
└── has many → terms

terms
├── belongs to → academic_years
└── has many → classes

courses
├── has many → classes
└── has many → assessment_categories

classes
├── belongs to → courses
├── belongs to → employees (instructor, via employee_id)
├── belongs to → academic_years
├── belongs to → terms
├── has many → enrollments
├── has many → assessments
└── has many → attendance_records (future)

enrollments
├── belongs to → students
├── belongs to → classes
└── has many → grades

grading_scales
└── (standalone, referenced by GradeCalculationService)

assessment_categories
├── belongs to → courses (nullable, NULL = global)
└── has many → assessments

assessments
├── belongs to → classes
├── belongs to → assessment_categories
└── has many → grades

grades
├── belongs to → enrollments
├── belongs to → assessments
└── belongs to → users (graded_by)

attendance_records (future)
├── belongs to → students
├── belongs to → classes
└── belongs to → users (marked_by)

student_notes
├── belongs to → students
├── belongs to → employees (nullable)
└── belongs to → departments

documents (polymorphic)
├── belongs to → documentable (Student, Employee, Course, etc.)
└── belongs to → users (uploaded_by)
```

---

## Indexes

### Performance-Critical Indexes

**Phase 1 (Implemented):**
- `students.student_id` - UNIQUE index for fast lookups
- `students.status` - Filter active students
- `students.deleted_at` - Soft delete queries
- `employees.employee_id` - UNIQUE index for fast lookups
- `employees.status` - Filter active employees
- `employees.department_id` - Filter by department
- `employee_roles.(department_id, name)` - Composite UNIQUE for role names per dept
- `phone_numbers.(phoneable_type, phoneable_id)` - Polymorphic relationship index
- `courses.course_code` - UNIQUE index for fast lookups
- `courses.department` - Filter by department
- `courses.is_active` - Filter active courses

**Phase 2 (Implemented):**
- `classes.course_id, classes.term_id` - Composite index for class queries
- `enrollments.student_id, enrollments.class_id` - UNIQUE composite to prevent duplicates
- `enrollments.status` - Filter by enrollment status

**Phase 3 (Implemented):**
- `grades.(enrollment_id, assessment_id)` - UNIQUE composite for grade lookups
- `assessments.class_id` - Filter assessments by class

**Phase 4 (To Be Implemented):**
- `attendance_records.student_id, attendance_records.date` - Composite for date-based queries
- `attendance_records.class_id, attendance_records.date` - Composite for class attendance

---

## Constraints

### Foreign Key Constraints

**Phase 1:**
- `students.user_id` → `users.id` (ON DELETE SET NULL)
- `guardians.user_id` → `users.id` (ON DELETE SET NULL)
- `guardian_student.guardian_id` → `guardians.id` (ON DELETE CASCADE)
- `guardian_student.student_id` → `students.id` (ON DELETE CASCADE)
- `terms.academic_year_id` → `academic_years.id` (ON DELETE CASCADE)
- `employee_roles.department_id` → `departments.id` (ON DELETE CASCADE)
- `employees.user_id` → `users.id` (ON DELETE SET NULL)
- `employees.department_id` → `departments.id` (ON DELETE RESTRICT)
- `employees.role_id` → `employee_roles.id` (ON DELETE RESTRICT)

**Phase 2:**
- `classes.course_id` → `courses.id` (ON DELETE RESTRICT)
- `classes.employee_id` → `employees.id` (ON DELETE RESTRICT)
- `classes.academic_year_id` → `academic_years.id` (ON DELETE RESTRICT)
- `classes.term_id` → `terms.id` (ON DELETE RESTRICT)
- `enrollments.student_id` → `students.id` (ON DELETE CASCADE)
- `enrollments.class_id` → `classes.id` (ON DELETE CASCADE)

**Phase 3:**
- `assessment_categories.course_id` → `courses.id` (ON DELETE SET NULL)
- `assessments.class_id` → `classes.id` (ON DELETE CASCADE)
- `assessments.assessment_category_id` → `assessment_categories.id` (ON DELETE CASCADE)
- `grades.enrollment_id` → `enrollments.id` (ON DELETE CASCADE)
- `grades.assessment_id` → `assessments.id` (ON DELETE CASCADE)
- `grades.graded_by` → `users.id` (ON DELETE SET NULL)

### Check Constraints

**Business Rules:**
- `students.enrollment_date <= CURRENT_DATE` - Cannot enroll in the future
- `students.date_of_birth < CURRENT_DATE` - Date of birth must be in the past
- `academic_years.end_date > start_date` - End date must be after start date
- `terms.end_date > start_date` - End date must be after start date
- `classes.max_students > 0` - Must have at least 1 student capacity

---

## Migration History

### Executed Migrations (Phase 0-2)

**Phase 0: Core Laravel + Settings**
1. **2014_10_12_000000_create_users_table.php** - Laravel default users table
2. **2014_10_12_100000_create_password_reset_tokens_table.php** - Password resets
3. **2019_08_19_000000_create_failed_jobs_table.php** - Queue failed jobs
4. **2019_12_14_000001_create_personal_access_tokens_table.php** - API tokens
5. **2026_02_08_195420_create_settings_table.php** - System settings

**Phase 1: Student, Course & Employee Foundation**
6. **2026_02_10_012729_create_students_table.php** - Students
7. **2026_02_10_012757_create_guardians_table.php** - Guardians
8. **2026_02_10_012758_create_guardian_student_table.php** - Guardian-student pivot
9. **2026_02_10_012823_create_academic_years_table.php** - Academic years
10. **2026_02_10_012823_create_terms_table.php** - Terms/semesters
11. **2026_02_10_012854_create_courses_table.php** - Courses
12. **2026_02_10_012900_create_departments_table.php** - Departments
13. **2026_02_10_012905_create_employee_roles_table.php** - Employee roles
14. **2026_02_10_012912_create_employees_table.php** - Employees
15. **2026_02_10_020000_create_phone_numbers_table.php** - Phone numbers (polymorphic)
16. **2026_02_10_021400_create_counties_table.php** - Counties (reference data)

**Phase 2: Class Scheduling & Enrollment**
17. **2026_02_10_021337_create_classes_table.php** - Class sections
18. **2026_02_10_021345_create_enrollments_table.php** - Student enrollments

**Phase 3A: Core Grading System**
19. **2026_02_15_000001_create_grading_scales_table.php** - Grading scales (JSON scale data)
20. **2026_02_15_000002_create_assessment_categories_table.php** - Assessment categories (per-course or global)
21. **2026_02_15_000003_create_assessments_table.php** - Assessments (per-class)
22. **2026_02_15_000004_create_grades_table.php** - Student grades with late tracking

**Session (February 23, 2026): Student Notes & Operations Dept**
- **2026_02_23_240000_create_student_notes_table.php** — Student notes with department scoping
- **2026_02_23_250000_seed_operations_department.php** — Operations department + 5 roles

**Session (February 25, 2026): Academic Year Status & Course Grading Type**
- **2026_02_25_213911_add_grading_type_to_courses_table.php** — Adds `grading_type` varchar ('credit_system' | 'pass_fail') to courses; defaults to 'credit_system'
- **2026_02_25_220000_add_status_to_academic_years.php** — Replaces `is_current` boolean with `status` varchar ('forming' | 'current' | 'completed'); migrates existing data

### Pending Migrations (Future Phases)

- Phase 3B/3C: No additional tables (report card/transcript generation, import/export)
- Phase 4: attendance_records
- Phase 5: calendar_events, documents, notifications

---

## Seeding Strategy

### Seeder Execution Order

```php
// database/seeders/DatabaseSeeder.php
public function run(): void
{
    // Create test user
    User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    // Phase 1 seeders
    $this->call([
        CountySeeder::class,              // 1. Georgia counties (reference data)
        DepartmentSeeder::class,          // 2. Departments and roles
        EmployeeSeeder::class,            // 3. 20 teachers + 3 non-teaching staff
        AcademicYearSeeder::class,        // 4. Academic years and terms
        CourseSeeder::class,              // 5. 20 courses across 10 departments
        StudentSeeder::class,             // 6. 100 students (3 named + 97 factory)
        ClassSeeder::class,               // 7. 40 classes (20 Fall + 20 Spring)
        EnrollmentSeeder::class,          // 8. ~500 enrollments (5 per student)
    ]);

    // Phase 3 seeders
    $this->call([
        GradingScaleSeeder::class,        // 9. Default grading scale (A-F)
        AssessmentCategorySeeder::class,  // 10. Default assessment categories with weights
        AssessmentSeeder::class,          // 11. 120 assessments for Fall classes
        GradeSeeder::class,              // 12. ~2,500 grades for enrolled students
    ]);
}
```

### Seed Data Examples

**AcademicYearSeeder:**
- Current year: 2025-2026
- Terms: Fall 2025, Spring 2026

**CourseSeeder (20 courses):**
- Mathematics: MATH-101 Algebra I, MATH-201 Geometry
- English: ENG-101 English Literature, ENG-102 English Composition
- Science: SCI-101 General Science, SCI-201 Biology, SCI-202 Chemistry
- History: HIST-101 World History, HIST-201 U.S. History
- Computer Science: CS-101 Intro to Programming, CS-201 Web Development
- Physical Education: PE-101 Physical Fitness, PE-102 Team Sports
- Health: HLT-101 Health & Wellness
- Art: ART-101 Visual Arts, ART-201 Digital Media
- Music: MUS-101 Music Fundamentals
- Foreign Language: SPAN-101 Spanish I, SPAN-201 Spanish II
- Life Skills: LIFE-101 Financial Literacy

**DepartmentSeeder:**
- 6 departments: Education, Administration, Counseling, Cadre, Health Services, Operations
- Roles per department: Teacher/Instructor, Administrator/Coordinator, Counselor/Case Manager, Drill Instructor/Platoon Sergeant, Nurse/Health Aide
- Operations: Director, Deputy Director, Commandant, Administrative Assistant, Site Administrator

**EmployeeSeeder (23 employees: 20 teachers + 3 staff):**
- 20 teachers (Education / Teacher role), each assigned to one course:
  - Marcus Williams (Math), Patricia Johnson (English), David Thompson (Science), Angela Davis (History)
  - Karen Lee (Geometry), Thomas Brown (Composition), Linda Garcia (Chemistry), Charles Martinez (Biology)
  - Jennifer Taylor (U.S. History), Daniel Anderson (CS), Michelle Robinson (Web Dev)
  - Kevin Clark (PE), Lisa Walker (Team Sports), Brian Hall (Health)
  - Stephanie Young (Art), Richard King (Digital Media), Amanda Wright (Music)
  - Maria Hernandez (Spanish I), Christopher Lopez (Spanish II), Nicole Scott (Financial Literacy)
- 3 non-teaching staff:
  - Robert Harris — Administration / Administrator
  - Sandra Mitchell — Counseling / Counselor
  - James Washington — Cadre / Drill Instructor

**CountySeeder:**
- All 159 Georgia counties for address/demographic data

**ClassSeeder (40 classes: 20 Fall + 20 Spring):**
- One class per teacher per term (20 unique courses × 2 terms)
- Each teacher has a unique room and schedule (no conflicts)
- Fall 2025 classes: status `in_progress`
- Spring 2026 classes: status `open`
- Max students: 25–30 per class

**StudentSeeder (100 students):**
- 3 named students (John Doe, Emma Smith, Michael Johnson) with guardians, phone numbers, and detailed demographics
- 97 additional students generated via `Student::factory()`
- All with status `active`

**EnrollmentSeeder (~500 enrollments):**
- Each of 100 students enrolled in 5 random Fall 2025 classes
- Respects `max_students` capacity per class
- All with status `enrolled` and enrollment date `2025-08-15`

**GradingScaleSeeder (Phase 3A):**
- Default grading scale: A=90/4.0, B=80/3.0, C=70/2.0, D=60/1.0, F=0/0.0
- Marked as `is_default = true`

**AssessmentCategorySeeder (Phase 3A):**
- 5 global categories (course_id = NULL):
  - Homework (0.15), Quizzes (0.15), Midterm Exam (0.25), Final Exam (0.30), Projects (0.15)

**AssessmentSeeder (120 assessments):**
- 6 assessments per Fall class (Homework 1 & 2, Quiz 1, Midterm, Research Project, Final Exam)
- 5 published + 1 draft (Final Exam) per class

**GradeSeeder (~2,500 grades):**
- Grades for all enrolled students on all published assessments
- Random scores (45–100), 15% chance of late submission with 10% penalty
- Triggers `GradeCalculationService` to populate `weighted_average`, `final_letter_grade`, and `grade_points` on enrollments

---

## Query Optimization

### N+1 Query Prevention

**Always use eager loading for relationships:**

```php
// BAD - N+1 queries
$students = Student::all();
foreach ($students as $student) {
    echo $student->guardians->count(); // Separate query per student
}

// GOOD - Eager loading
$students = Student::with('guardians')->get();
foreach ($students as $student) {
    echo $student->guardians->count(); // Single query
}
```

**Common Eager Loading Patterns:**

```php
// Students with guardians
Student::with('guardians')->paginate(10);

// Students with user accounts
Student::with('user')->get();

// Enrollments with student and class
Enrollment::with(['student', 'class.course', 'class.employee'])->get();

// Classes with all related data
Class::with(['course', 'employee', 'term.academicYear'])->get();
```

### Query Scopes

**Use model scopes for reusable queries:**

```php
// Student scopes
Student::active()->get(); // WHERE status = 'active'
Student::search('John')->get(); // WHERE name LIKE '%John%'
Student::status('graduated')->get(); // WHERE status = 'graduated'

// Course scopes
Course::department('Mathematics')->get();
Course::level('Beginner')->get();
Course::active()->get();

// Employee scopes
Employee::active()->get();
Employee::department(1)->get(); // by department_id
Employee::search('Smith')->get();
```

### Pagination

**Always paginate large result sets:**

```php
// Paginate with 10 items per page (default)
$students = Student::with('guardians')->paginate(10);

// Custom pagination
$courses = Course::active()->paginate(15);

// Simple pagination (no page count)
$employees = Employee::active()->simplePaginate(10);
```

### Indexing Strategy

**Multi-column indexes for composite queries:**

```php
// When querying classes by course and term together
$table->index(['course_id', 'term_id']);

// When querying enrollments by student and status
$table->index(['student_id', 'status']);

// When querying attendance by student and date range
$table->index(['student_id', 'date']);
```

---

## Backup and Recovery

### Backup Strategy

**Daily backups:**
```bash
# PostgreSQL backup
pg_dump sms > backup_$(date +%Y%m%d).sql

# Compress backup
gzip backup_$(date +%Y%m%d).sql
```

**Restore from backup:**
```bash
# Decompress
gunzip backup_20260209.sql.gz

# Restore
psql sms < backup_20260209.sql
```

### Data Retention

- Soft deletes enabled on: students, employees
- Retention period: 7 years for student records (compliance)
- Hard delete after retention period via scheduled command

---

## Security Considerations

### SQL Injection Prevention

- **Always use Eloquent ORM or query builder** (automatic parameterization)
- **Never concatenate user input into raw queries**

```php
// BAD - SQL injection vulnerability
DB::select("SELECT * FROM students WHERE name = '" . $request->name . "'");

// GOOD - Parameterized query
DB::select("SELECT * FROM students WHERE name = ?", [$request->name]);

// BEST - Eloquent ORM
Student::where('name', $request->name)->get();
```

### Mass Assignment Protection

- **Define fillable or guarded properties on all models**
- **Never use unguarded models in production**

```php
// Student model
protected $fillable = [
    'first_name',
    'last_name',
    // ... explicit list
];

// Auto-generated fields NOT in fillable (student_id, employee_id)
```

### Database Credentials

- Store credentials in `.env` file (never in version control)
- Use environment-specific credentials
- Rotate passwords regularly

---

## Performance Monitoring

### Slow Query Log

**Enable PostgreSQL slow query logging:**
```sql
ALTER DATABASE sms SET log_min_duration_statement = 1000; -- Log queries > 1 second
```

### Query Analysis

**Use EXPLAIN to analyze query performance:**
```sql
EXPLAIN ANALYZE
SELECT s.*, g.*
FROM students s
LEFT JOIN guardian_student gs ON s.id = gs.student_id
LEFT JOIN guardians g ON gs.guardian_id = g.id
WHERE s.status = 'active';
```

---

## Future Enhancements

### Potential Schema Additions

1. **Audit Tables** - Track all data changes
   - student_history, grade_history, etc.

2. **Notification Preferences** - User notification settings

3. **File Metadata** - Enhanced document management

4. **Report Templates** - Custom report definitions

5. **Integration Tables** - Third-party system integrations

---

**© 2026 Brian K. Rich. All rights reserved.**
