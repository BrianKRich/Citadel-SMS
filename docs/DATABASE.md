# Student Management System - Database Architecture

**Version:** 2.0 (Phase 0-2 Complete)
**Last Updated:** February 9, 2026
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
- Teacher profiles
- Class scheduling
- Enrollment tracking
- Grade management
- Attendance records

**Design Principles:**
- Normalized schema (3NF) to reduce data redundancy
- Foreign key constraints for referential integrity
- Soft deletes for data retention
- Auto-generated unique IDs for students and teachers
- Polymorphic relationships for flexible associations
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
- Has one Teacher (user_id)

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
| is_current | BOOLEAN | DEFAULT false | Current year flag |
| created_at | TIMESTAMP | | Creation timestamp |
| updated_at | TIMESTAMP | | Last update timestamp |

**Relationships:**
- Has many Terms

**Indexes:**
- PRIMARY KEY (id)
- INDEX (is_current)

**Business Rules:**
- Only one academic year can have is_current = true at a time
- Managed by seeder and controller

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
| credits | DECIMAL(5,2) | NULLABLE | Credit hours |
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

#### **teachers**
Teacher profiles and qualifications.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| teacher_id | VARCHAR(255) | UNIQUE, NOT NULL | Auto-generated ID (TCH-YYYY-###) |
| user_id | BIGINT | FK, NOT NULL | Link to user account |
| first_name | VARCHAR(255) | NOT NULL | Teacher's first name |
| last_name | VARCHAR(255) | NOT NULL | Teacher's last name |
| email | VARCHAR(255) | UNIQUE, NOT NULL | Teacher email |
| phone | VARCHAR(255) | NULLABLE | Phone number |
| date_of_birth | DATE | NULLABLE | Date of birth |
| hire_date | DATE | NOT NULL | Date hired |
| department | VARCHAR(255) | NULLABLE | Department |
| specialization | VARCHAR(255) | NULLABLE | Specialization |
| qualifications | TEXT | NULLABLE | Qualifications/certifications |
| photo | VARCHAR(255) | NULLABLE | Path to teacher photo |
| status | ENUM | NOT NULL, DEFAULT 'active' | active, inactive, on_leave |
| created_at | TIMESTAMP | | Creation timestamp |
| updated_at | TIMESTAMP | | Last update timestamp |
| deleted_at | TIMESTAMP | NULLABLE | Soft delete timestamp |

**Relationships:**
- Belongs to User (user_id)
- Has many Classes

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (teacher_id)
- UNIQUE (email)
- INDEX (user_id)
- INDEX (status)
- INDEX (department)
- INDEX (deleted_at)

**Auto-Generated Fields:**
- `teacher_id` is auto-generated using format: TCH-{YEAR}-{SEQUENCE}
  - Example: TCH-2026-001, TCH-2026-002

**Constraints:**
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE

---

### Phase 2 Tables ✅ Implemented

#### **classes**
Class instances (course offerings in a specific term).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| course_id | BIGINT | FK, NOT NULL | Course reference |
| teacher_id | BIGINT | FK, NOT NULL | Teacher reference |
| academic_year_id | BIGINT | FK, NOT NULL | Academic year reference |
| term_id | BIGINT | FK, NOT NULL | Term reference |
| section_name | VARCHAR(255) | NOT NULL | Section (e.g., "A", "Morning") |
| room | VARCHAR(255) | NULLABLE | Room number |
| schedule | JSON | NULLABLE | Weekly schedule |
| max_students | INTEGER | DEFAULT 30 | Maximum enrollment |
| status | ENUM | DEFAULT 'open' | open, closed, in_progress, completed |
| created_at | TIMESTAMP | | Creation timestamp |
| updated_at | TIMESTAMP | | Last update timestamp |

**Schedule JSON Format:**
```json
[
  {
    "day": "Monday",
    "start_time": "09:00",
    "end_time": "10:00"
  },
  {
    "day": "Wednesday",
    "start_time": "09:00",
    "end_time": "10:00"
  }
]
```

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
| final_grade | VARCHAR(10) | NULLABLE | Final grade (A, B+, 92, etc.) |
| grade_points | DECIMAL(5,2) | NULLABLE | Numeric grade |
| created_at | TIMESTAMP | | Creation timestamp |
| updated_at | TIMESTAMP | | Last update timestamp |

**Indexes:**
- UNIQUE (student_id, class_id) - prevent duplicate enrollments

---

### Phase 3 Tables (To Be Implemented)

#### **assessment_types**
Types of assessments (Quiz, Exam, Homework, etc.).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | NOT NULL | Type name |
| weight | DECIMAL(5,2) | NOT NULL | Weight percentage (0.20 = 20%) |
| description | TEXT | NULLABLE | Description |
| created_at | TIMESTAMP | | Creation timestamp |
| updated_at | TIMESTAMP | | Last update timestamp |

---

#### **assessments**
Individual assessment instances.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| class_id | BIGINT | FK, NOT NULL | Class reference |
| assessment_type_id | BIGINT | FK, NOT NULL | Type reference |
| name | VARCHAR(255) | NOT NULL | Assessment name |
| description | TEXT | NULLABLE | Description |
| max_score | DECIMAL(8,2) | NOT NULL | Maximum score |
| due_date | DATE | NULLABLE | Due date |
| weight | DECIMAL(5,2) | NULLABLE | Override type weight |
| created_at | TIMESTAMP | | Creation timestamp |
| updated_at | TIMESTAMP | | Last update timestamp |

---

#### **grades**
Individual student grades for assessments.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PK, AUTO_INCREMENT | Primary key |
| enrollment_id | BIGINT | FK, NOT NULL | Enrollment reference |
| assessment_id | BIGINT | FK, NOT NULL | Assessment reference |
| score | DECIMAL(8,2) | NOT NULL | Score earned |
| notes | TEXT | NULLABLE | Grading notes |
| graded_by | BIGINT | FK, NOT NULL | User who graded |
| graded_at | TIMESTAMP | NULLABLE | Grading timestamp |
| created_at | TIMESTAMP | | Creation timestamp |
| updated_at | TIMESTAMP | | Last update timestamp |

**Indexes:**
- UNIQUE (enrollment_id, assessment_id) - one grade per student per assessment

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
└── has one → teachers (user_id)

students
├── belongs to → users (user_id)
├── has many → guardians (through guardian_student)
├── has many → enrollments
└── has many → attendance_records

guardians
├── belongs to → users (user_id)
└── has many → students (through guardian_student)

academic_years
└── has many → terms

terms
├── belongs to → academic_years
└── has many → classes

courses
└── has many → classes

teachers
├── belongs to → users (user_id)
└── has many → classes

classes
├── belongs to → courses
├── belongs to → teachers
├── belongs to → academic_years
├── belongs to → terms
├── has many → enrollments
├── has many → assessments
└── has many → attendance_records

enrollments
├── belongs to → students
├── belongs to → classes
└── has many → grades

assessments
├── belongs to → classes
├── belongs to → assessment_types
└── has many → grades

grades
├── belongs to → enrollments
├── belongs to → assessments
└── belongs to → users (graded_by)

attendance_records
├── belongs to → students
├── belongs to → classes
└── belongs to → users (marked_by)

documents (polymorphic)
├── belongs to → documentable (Student, Teacher, Course, etc.)
└── belongs to → users (uploaded_by)
```

---

## Indexes

### Performance-Critical Indexes

**Phase 1 (Implemented):**
- `students.student_id` - UNIQUE index for fast lookups
- `students.status` - Filter active students
- `students.deleted_at` - Soft delete queries
- `teachers.teacher_id` - UNIQUE index for fast lookups
- `teachers.status` - Filter active teachers
- `courses.course_code` - UNIQUE index for fast lookups
- `courses.department` - Filter by department
- `courses.is_active` - Filter active courses

**Phase 2 (To Be Implemented):**
- `classes.course_id, classes.term_id` - Composite index for class queries
- `enrollments.student_id, enrollments.class_id` - UNIQUE composite to prevent duplicates
- `enrollments.status` - Filter by enrollment status

**Phase 3 (To Be Implemented):**
- `grades.enrollment_id, grades.assessment_id` - UNIQUE composite for grade lookups
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
- `teachers.user_id` → `users.id` (ON DELETE CASCADE)

**Phase 2:**
- `classes.course_id` → `courses.id` (ON DELETE RESTRICT)
- `classes.teacher_id` → `teachers.id` (ON DELETE RESTRICT)
- `classes.academic_year_id` → `academic_years.id` (ON DELETE RESTRICT)
- `classes.term_id` → `terms.id` (ON DELETE RESTRICT)
- `enrollments.student_id` → `students.id` (ON DELETE CASCADE)
- `enrollments.class_id` → `classes.id` (ON DELETE CASCADE)

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

**Phase 1: Student & Course Foundation**
6. **2026_02_10_012729_create_students_table.php** - Students
7. **2026_02_10_012730_create_guardians_table.php** - Guardians
8. **2026_02_10_012758_create_guardian_student_table.php** - Guardian-student pivot
9. **2026_02_10_012731_create_academic_years_table.php** - Academic years
10. **2026_02_10_012732_create_terms_table.php** - Terms/semesters
11. **2026_02_10_012733_create_courses_table.php** - Courses
12. **2026_02_10_012734_create_teachers_table.php** - Teachers

**Phase 2: Class Scheduling & Enrollment**
13. **2026_02_10_021337_create_classes_table.php** - Class sections
14. **2026_02_10_021345_create_enrollments_table.php** - Student enrollments

### Pending Migrations (Future Phases)

- Phase 3: assessment_types, assessments, grades
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
        AcademicYearSeeder::class,  // 1. Academic years and terms
        CourseSeeder::class,        // 2. Course catalog
        TeacherSeeder::class,       // 3. Teachers
        StudentSeeder::class,       // 4. Students with guardians
    ]);

    // Phase 2 seeders
    $this->call([
        ClassSeeder::class,         // 5. Class sections
        EnrollmentSeeder::class,    // 6. Student enrollments
    ]);
}
```

### Seed Data Examples

**AcademicYearSeeder:**
- Current year: 2025-2026
- Terms: Fall 2025, Spring 2026

**CourseSeeder:**
- MATH-101 (Algebra I, Mathematics, Beginner, 3 credits)
- ENG-101 (English Literature, English, Beginner, 3 credits)
- SCI-101 (General Science, Science, Beginner, 4 credits)
- HIST-101 (U.S. History, History, Beginner, 3 credits)
- PE-101 (Physical Education, Physical Education, Beginner, 1 credit)
- 5 additional courses

**TeacherSeeder:**
- 5 teachers with auto-generated IDs (TCH-2026-001 through TCH-2026-005)
- Various departments: Mathematics, English, Science, History, Physical Education
- All with active status

**ClassSeeder (Phase 2):**
- 12 class sections across different courses
- Each with:
  - Assigned teacher
  - Section name (A, B, Morning, Afternoon)
  - Room number
  - Weekly schedule (JSON format)
  - Capacity (20-30 students)
  - Status (mostly "open")

**EnrollmentSeeder (Phase 2):**
- Sample enrollments for students
- Respects capacity limits
- No schedule conflicts
- All with "enrolled" status

**StudentSeeder:**
- 5 sample students with varied statuses
- Linked guardians with is_primary flags
- Realistic demographics and contact information

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
Enrollment::with(['student', 'class.course', 'class.teacher'])->get();

// Classes with all related data
Class::with(['course', 'teacher', 'term.academicYear'])->get();
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

// Teacher scopes
Teacher::active()->get();
Teacher::department('Science')->get();
Teacher::search('Smith')->get();
```

### Pagination

**Always paginate large result sets:**

```php
// Paginate with 10 items per page (default)
$students = Student::with('guardians')->paginate(10);

// Custom pagination
$courses = Course::active()->paginate(15);

// Simple pagination (no page count)
$teachers = Teacher::active()->simplePaginate(10);
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
pg_dump student_management_system > backup_$(date +%Y%m%d).sql

# Compress backup
gzip backup_$(date +%Y%m%d).sql
```

**Restore from backup:**
```bash
# Decompress
gunzip backup_20260209.sql.gz

# Restore
psql student_management_system < backup_20260209.sql
```

### Data Retention

- Soft deletes enabled on: students, teachers
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

// Auto-generated fields NOT in fillable (student_id, teacher_id)
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
ALTER DATABASE student_management_system SET log_min_duration_statement = 1000; -- Log queries > 1 second
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
