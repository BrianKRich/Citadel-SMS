# Citadel SMS - Complete Project Roadmap

## Planning Session Documentation

**Date:** February 8, 2026
**Session Type:** Complete Project Roadmap Planning

### Questions & Answers

**Q1: What aspect of the Citadel SMS project would you like to plan?**

**Options Provided:**
1. Complete project roadmap - Plan the overall development roadmap including all major features (students, courses, grades, attendance, teachers, etc.) with priorities and phases
2. Student management feature - Design the student records system including profiles, enrollment, demographics, and student portal
3. Academic management - Plan courses, classes, schedules, grades, assessments, and academic calendar features
4. Database architecture - Design the complete database schema for all SMS entities (students, courses, grades, attendance, etc.)

**User Answer:** Complete project roadmap

**Rationale:** User wants a comprehensive development plan covering all major SMS features organized into implementation phases with clear priorities, timelines, and technical details.

### Additional Requirements & Clarifications

**Requirement 1:** The application will NOT include payment or fee management functionality.
- Payment processing is out of scope
- Fee tracking is not required
- Billing features will not be implemented

**Requirement 2:** Database and backend development will be completed BEFORE frontend work.
- Implementation approach: Database → Backend (Models, Controllers, API) → Frontend (Components, Pages)
- This allows for thorough testing of business logic independent of UI
- Enables clear separation of concerns
- Facilitates future frontend flexibility (web, mobile, etc.)

**User Note:** "I need to give this some thought before proceeding. I would like to build out the database and backend before moving on."

**Requirement 3:** The existing theme system needs to be fully integrated into application components.
- **Current State**: Theme infrastructure exists (database, API, color picker) but CSS variables are NOT applied to actual components
- **Required**: All components should use theme colors (--color-primary, --color-secondary, etc.) instead of hardcoded Tailwind colors
- **Scope**: Update buttons, cards, navigation, and all UI elements to respect theme settings
- **Priority**: Should be implemented early so new components follow theme patterns

---

## Context

Citadel SMS is a Student Management System built with Laravel 12, Vue 3, and PostgreSQL. The application currently has foundational features (user management, authentication, admin dashboard, theme customization, dark mode) and needs a comprehensive roadmap to implement core SMS functionality for managing students, courses, grades, attendance, and academic operations.

**Current State:**
- ✅ User authentication & management with role-based access (admin/user)
- ✅ Admin dashboard with statistics
- ✅ Theme customization system
- ✅ Dark mode support
- ✅ Responsive component architecture
- ✅ Settings storage infrastructure

**Goal:** Design a phased implementation roadmap that transforms Citadel from a basic admin system into a full-featured Student Management System while maintaining the existing architecture patterns and code quality.

---

## 🎯 Core SMS Features to Implement

Based on README.md planned features, we need to implement:

1. **Student Management** - Student records, profiles, demographics, enrollment
2. **Course Management** - Courses, subjects, curriculum
3. **Class Management** - Class sections, schedules, room assignments
4. **Teacher/Instructor Management** - Teacher profiles, assignments, qualifications
5. **Grade & Assessment Tracking** - Grading system, assessments, report cards
6. **Attendance Management** - Daily attendance, tracking, reports
7. **Enrollment & Registration** - Student enrollment workflow, academic year management
8. **Academic Reporting** - Reports, analytics, performance tracking
9. **Parent/Guardian Portal** - Parent accounts, student progress viewing
10. **Academic Calendar** - Terms, semesters, holidays, events
11. **Document Management** - File uploads, transcripts, certificates

---

## 📊 Database Schema Design

### Phase 1 Tables (Student & Course Foundation)

**students**
```
id (bigint, PK)
student_id (string, unique) - e.g., "STU-2026-001"
first_name (string)
last_name (string)
middle_name (string, nullable)
date_of_birth (date)
gender (enum: male, female, other)
email (string, unique, nullable)
phone (string, nullable)
address (text, nullable)
city (string, nullable)
state (string, nullable)
postal_code (string, nullable)
country (string, default: 'USA')
emergency_contact_name (string, nullable)
emergency_contact_phone (string, nullable)
enrollment_date (date)
status (enum: active, inactive, graduated, withdrawn, suspended)
photo (string, nullable) - path to student photo
notes (text, nullable)
user_id (bigint, FK to users, nullable) - for student portal access
created_at, updated_at
soft_deletes
```

**guardians**
```
id (bigint, PK)
first_name (string)
last_name (string)
relationship (enum: mother, father, guardian, grandparent, other)
email (string, nullable)
phone (string)
address (text, nullable)
occupation (string, nullable)
user_id (bigint, FK to users, nullable) - for parent portal access
created_at, updated_at
```

**guardian_student** (pivot table)
```
id (bigint, PK)
guardian_id (bigint, FK)
student_id (bigint, FK)
is_primary (boolean, default: false)
created_at, updated_at
```

**academic_years**
```
id (bigint, PK)
name (string) - e.g., "2025-2026"
start_date (date)
end_date (date)
is_current (boolean, default: false)
created_at, updated_at
```

**terms** (semesters/quarters)
```
id (bigint, PK)
academic_year_id (bigint, FK)
name (string) - e.g., "Fall 2025", "Spring 2026"
start_date (date)
end_date (date)
is_current (boolean, default: false)
created_at, updated_at
```

**courses**
```
id (bigint, PK)
course_code (string, unique) - e.g., "MATH-101"
name (string) - e.g., "Algebra I"
description (text, nullable)
credits (decimal, nullable)
department (string, nullable)
level (string, nullable) - e.g., "Beginner", "Intermediate", "Advanced"
is_active (boolean, default: true)
created_at, updated_at
```

**teachers**
```
id (bigint, PK)
teacher_id (string, unique) - e.g., "TCH-2026-001"
user_id (bigint, FK to users)
first_name (string)
last_name (string)
email (string, unique)
phone (string, nullable)
date_of_birth (date, nullable)
hire_date (date)
department (string, nullable)
specialization (string, nullable)
qualifications (text, nullable)
photo (string, nullable)
status (enum: active, inactive, on_leave)
created_at, updated_at
soft_deletes
```

### Phase 2 Tables (Classes & Enrollment)

**classes**
```
id (bigint, PK)
course_id (bigint, FK)
teacher_id (bigint, FK)
academic_year_id (bigint, FK)
term_id (bigint, FK)
section_name (string) - e.g., "A", "B", "Morning"
room (string, nullable)
schedule (json) - [{"day": "Monday", "start": "09:00", "end": "10:00"}, ...]
max_students (integer, default: 30)
status (enum: open, closed, in_progress, completed)
created_at, updated_at
```

**enrollments**
```
id (bigint, PK)
student_id (bigint, FK)
class_id (bigint, FK)
enrollment_date (date)
status (enum: enrolled, dropped, completed, failed)
final_grade (string, nullable) - e.g., "A", "B+", "92"
grade_points (decimal, nullable)
created_at, updated_at
```

### Phase 3 Tables (Grades & Assessments)

**assessment_types**
```
id (bigint, PK)
name (string) - e.g., "Quiz", "Midterm", "Final Exam", "Homework"
weight (decimal) - percentage of final grade (e.g., 0.20 for 20%)
description (text, nullable)
created_at, updated_at
```

**assessments**
```
id (bigint, PK)
class_id (bigint, FK)
assessment_type_id (bigint, FK)
name (string) - e.g., "Midterm Exam - Chapter 1-5"
description (text, nullable)
max_score (decimal)
due_date (date, nullable)
weight (decimal, nullable) - override type weight for specific assessment
created_at, updated_at
```

**grades**
```
id (bigint, PK)
enrollment_id (bigint, FK)
assessment_id (bigint, FK)
score (decimal)
notes (text, nullable)
graded_by (bigint, FK to users)
graded_at (timestamp, nullable)
created_at, updated_at
```

### Phase 4 Tables (Attendance)

**attendance_records**
```
id (bigint, PK)
student_id (bigint, FK)
class_id (bigint, FK)
date (date)
status (enum: present, absent, late, excused)
notes (text, nullable)
marked_by (bigint, FK to users)
created_at, updated_at

UNIQUE INDEX on (student_id, class_id, date)
```

### Phase 5 Tables (Advanced Features)

**calendar_events**
```
id (bigint, PK)
title (string)
description (text, nullable)
event_type (enum: holiday, exam, meeting, event, deadline)
start_date (date)
end_date (date, nullable)
all_day (boolean, default: true)
location (string, nullable)
created_by (bigint, FK to users)
created_at, updated_at
```

**documents**
```
id (bigint, PK)
documentable_type (string) - polymorphic (Student, Teacher, Course, etc.)
documentable_id (bigint) - polymorphic
title (string)
description (text, nullable)
file_path (string)
file_type (string)
file_size (integer) - in bytes
uploaded_by (bigint, FK to users)
created_at, updated_at
```

**notifications**
```
id (bigint, PK)
user_id (bigint, FK)
type (string) - e.g., "grade_posted", "attendance_alert", "assignment_due"
title (string)
message (text)
data (json, nullable) - additional data payload
read_at (timestamp, nullable)
created_at, updated_at
```

---

## 🚀 Implementation Roadmap

### **Phase 0: Theme System Integration** (Week 0 - Foundation)
**Priority: HIGH - Must complete before building new features**

**Scope:** Fully integrate the existing theme system into all application components.

**Current State:**
- Theme infrastructure exists: database storage, API endpoint, color picker, CSS variables defined
- CSS variables are set (--color-primary, --color-secondary, etc.) but NOT used in components
- All components use hardcoded Tailwind colors (bg-indigo-600, text-gray-900, etc.)

**Required Changes:**

**Backend:** (Already complete)
- ✅ Settings table and model
- ✅ ThemeController with update/get methods
- ✅ API endpoint /api/theme

**Frontend - Tailwind CSS Integration:**

**Approach: Extend Tailwind with theme color variables**

1. **Update Tailwind Config** (`tailwind.config.js`)
   - Extend Tailwind's color palette with CSS variables:
   ```js
   theme: {
     extend: {
       colors: {
         primary: {
           DEFAULT: 'rgb(var(--color-primary) / <alpha-value>)',
           50: 'rgb(var(--color-primary) / 0.05)',
           100: 'rgb(var(--color-primary) / 0.1)',
           500: 'rgb(var(--color-primary) / 0.5)',
           600: 'rgb(var(--color-primary) / 1)',
           700: 'rgb(var(--color-primary) / 0.9)',
         },
         secondary: {
           DEFAULT: 'rgb(var(--color-secondary) / <alpha-value>)',
           600: 'rgb(var(--color-secondary) / 1)',
           700: 'rgb(var(--color-secondary) / 0.9)',
         },
         accent: {
           DEFAULT: 'rgb(var(--color-accent) / <alpha-value>)',
           600: 'rgb(var(--color-accent) / 1)',
           700: 'rgb(var(--color-accent) / 0.9)',
         },
       }
     }
   }
   ```

2. **Update useTheme Composable** (`resources/js/composables/useTheme.js`)
   - Convert hex colors to RGB format for Tailwind:
   ```js
   const applyTheme = () => {
     const root = document.documentElement;
     // Convert hex to RGB (e.g., #6366f1 → 99 102 241)
     root.style.setProperty('--color-primary', hexToRgb(theme.value.primary_color));
     root.style.setProperty('--color-secondary', hexToRgb(theme.value.secondary_color));
     root.style.setProperty('--color-accent', hexToRgb(theme.value.accent_color));
   };

   function hexToRgb(hex) {
     const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
     return result ?
       `${parseInt(result[1], 16)} ${parseInt(result[2], 16)} ${parseInt(result[3], 16)}`
       : '99 102 241'; // fallback
   }
   ```

3. **Update Existing Components** (Replace hardcoded colors with Tailwind theme classes):
   - `PrimaryButton.vue` - Change `bg-indigo-600 hover:bg-indigo-700` to `bg-primary-600 hover:bg-primary-700`
   - `SecondaryButton.vue` - Use `bg-secondary-600 hover:bg-secondary-700`
   - `DangerButton.vue` - Keep as `bg-red-600` (semantic color)
   - `NavLink.vue` - Use `text-primary-600` for active states
   - `AdminActionCard.vue` - Replace color variants with theme colors
   - `StatCard.vue` - Use `text-accent-600` for trends (optional)

4. **Component Pattern Examples:**
   ```vue
   <!-- OLD (hardcoded Tailwind): -->
   <button class="bg-indigo-600 hover:bg-indigo-700 text-white">

   <!-- NEW (themed Tailwind): -->
   <button class="bg-primary-600 hover:bg-primary-700 text-white">

   <!-- With opacity: -->
   <div class="bg-primary-500/20 border-primary-600">

   <!-- Dark mode support: -->
   <button class="bg-primary-600 dark:bg-primary-500">
   ```

**Benefits of Tailwind Approach:**
- ✅ Native Tailwind syntax (familiar to developers)
- ✅ Full Tailwind utilities work (hover, focus, dark mode, opacity)
- ✅ Autocomplete in IDE
- ✅ No custom CSS classes needed
- ✅ Plays well with existing Tailwind setup

**Files to Update:**
- `tailwind.config.js` - Extend colors with CSS variable references
- `resources/js/composables/useTheme.js` - Add hexToRgb conversion function
- `resources/js/Components/PrimaryButton.vue` - Change to `bg-primary-600`
- `resources/js/Components/SecondaryButton.vue` - Change to `bg-secondary-600`
- `resources/js/Components/NavLink.vue` - Use `text-primary-600`
- `resources/js/Components/Admin/AdminActionCard.vue` - Replace color variants
- Any other components using hardcoded indigo/purple/blue colors

**Verification:**
- [ ] Change theme colors in admin panel
- [ ] Verify all buttons update to new colors
- [ ] Verify navigation updates
- [ ] Verify admin cards update
- [ ] Test with light and dark mode
- [ ] Ensure theme persists after page reload

**Notes:**
- Semantic colors (danger, success, warning) should remain fixed (red, green, yellow)
- Gray scales for text/backgrounds can stay Tailwind default or be added to theme
- This creates a consistent theming system for all future components

---

### **Phase 1: Student & Course Foundation** (Week 1-2)
**Priority: HIGH**

**Scope:** Core student and course management functionality.

**Database:**
- Create migrations for: students, guardians, guardian_student, academic_years, terms, courses, teachers
- Seed initial data (current academic year, sample students)

**Backend:**
- `StudentController` - CRUD for students with search/filter
- `GuardianController` - CRUD for guardians, link to students
- `CourseController` - CRUD for courses
- `TeacherController` - CRUD for teachers
- `AcademicYearController` - Manage academic years and terms

**Models:**
- Student (relationships: guardians, enrollments, attendances)
- Guardian (relationships: students)
- Course (relationships: classes)
- Teacher (relationships: user, classes)
- AcademicYear (relationships: terms, classes)
- Term (relationships: academic_year, classes)

**Frontend Components:**
- `Students/StudentList.vue` - Paginated table (desktop) + cards (mobile)
- `Students/StudentForm.vue` - Create/edit student with photo upload
- `Students/StudentProfile.vue` - Detailed student view
- `Courses/CourseList.vue` - Course listing
- `Courses/CourseForm.vue` - Create/edit courses
- `Teachers/TeacherList.vue` - Teacher directory
- `Teachers/TeacherForm.vue` - Create/edit teachers

**Pages:**
- `Pages/Admin/Students/Index.vue`
- `Pages/Admin/Students/Create.vue`
- `Pages/Admin/Students/Edit.vue`
- `Pages/Admin/Students/Show.vue`
- Similar structure for Courses, Teachers, Guardians

**Routes:**
```php
Route::resource('admin/students', StudentController::class);
Route::resource('admin/courses', CourseController::class);
Route::resource('admin/teachers', TeacherController::class);
Route::resource('admin/guardians', GuardianController::class);
Route::resource('admin/academic-years', AcademicYearController::class);
```

**Update Admin Dashboard:**
- Add statistics: total_students, total_courses, total_teachers
- Update AdminActionCard links to new routes

**Verification:**
- Create, view, edit, delete students
- Upload and display student photos
- Link guardians to students
- Create courses and assign teachers
- Set current academic year

---

### **Phase 2: Class Scheduling & Enrollment** (Week 3-4)
**Priority: HIGH**

**Scope:** Class management, scheduling, student enrollment.

**Database:**
- Create migrations for: classes, enrollments

**Backend:**
- `ClassController` - CRUD for classes
- `EnrollmentController` - Enroll students, drop classes, view enrollments
- Add validation: prevent over-enrollment (max_students check)
- Add validation: prevent schedule conflicts for teachers

**Models:**
- Class (relationships: course, teacher, term, enrollments, grades, attendance)
- Enrollment (relationships: student, class, grades)

**Frontend Components:**
- `Classes/ClassList.vue` - List all classes with filters (term, teacher, course)
- `Classes/ClassForm.vue` - Create/edit class with schedule builder
- `Classes/ClassDetail.vue` - View class roster, schedule, enrollment stats
- `Classes/ScheduleBuilder.vue` - Weekly schedule grid component
- `Enrollment/EnrollmentForm.vue` - Bulk or individual enrollment
- `Enrollment/StudentSchedule.vue` - View student's enrolled classes

**Pages:**
- `Pages/Admin/Classes/Index.vue`
- `Pages/Admin/Classes/Create.vue`
- `Pages/Admin/Classes/Edit.vue`
- `Pages/Admin/Classes/Show.vue`
- `Pages/Admin/Enrollment/Index.vue`
- `Pages/Admin/Enrollment/Create.vue`

**Routes:**
```php
Route::resource('admin/classes', ClassController::class);
Route::prefix('admin/enrollment')->group(function () {
    Route::get('/', [EnrollmentController::class, 'index'])->name('admin.enrollment.index');
    Route::post('enroll', [EnrollmentController::class, 'enroll'])->name('admin.enrollment.enroll');
    Route::delete('{enrollment}', [EnrollmentController::class, 'drop'])->name('admin.enrollment.drop');
});
```

**Update Admin Dashboard:**
- Add statistics: total_classes, active_enrollments
- Add quick action for "Manage Classes" and "Student Enrollment"

**Verification:**
- Create classes with schedules
- Enroll students in multiple classes
- View student schedules
- Prevent over-enrollment
- Drop students from classes

---

### **Phase 3: Grading & Assessments** (Week 5-6)
**Priority: MEDIUM**

**Scope:** Assessment creation, grade entry, grade calculation, report cards.

**Database:**
- Create migrations for: assessment_types, assessments, grades

**Backend:**
- `AssessmentTypeController` - CRUD for assessment types
- `AssessmentController` - CRUD for assessments within classes
- `GradeController` - Enter grades, calculate final grades, generate reports
- Add grade calculation logic: weighted averages based on assessment types
- Add GPA calculation methods

**Models:**
- AssessmentType (relationships: assessments)
- Assessment (relationships: class, type, grades)
- Grade (relationships: enrollment, assessment, grader)
- Add methods to Enrollment model: calculateFinalGrade(), getGPA()

**Frontend Components:**
- `Assessments/AssessmentList.vue` - List assessments for a class
- `Assessments/AssessmentForm.vue` - Create/edit assessment
- `Grades/GradeEntryGrid.vue` - Spreadsheet-like grade entry (students × assessments)
- `Grades/GradeReport.vue` - Individual student grade report
- `Grades/ClassGrades.vue` - Class overview with statistics (average, median, etc.)
- `Reports/ReportCard.vue` - Printable report card

**Pages:**
- `Pages/Admin/Assessments/Index.vue` (per class)
- `Pages/Admin/Assessments/Create.vue`
- `Pages/Admin/Grades/Index.vue` (per class)
- `Pages/Admin/Grades/Entry.vue` (grade entry grid)
- `Pages/Admin/Reports/StudentReport.vue`

**Routes:**
```php
Route::prefix('admin/classes/{class}')->group(function () {
    Route::resource('assessments', AssessmentController::class);
    Route::get('grades', [GradeController::class, 'index'])->name('admin.grades.index');
    Route::post('grades', [GradeController::class, 'store'])->name('admin.grades.store');
});
Route::get('admin/students/{student}/report', [ReportController::class, 'show'])->name('admin.reports.student');
```

**Update Admin Dashboard:**
- Add statistics: recent_grades_entered, average_class_gpa
- Add quick action for "Grade Entry"

**Verification:**
- Create assessment types (Quiz 20%, Midterm 30%, Final 50%)
- Create assessments for classes
- Enter grades for students
- View calculated final grades
- Generate report cards
- Export grades to PDF/Excel

---

### **Phase 4: Attendance Tracking** (Week 7)
**Priority: MEDIUM**

**Scope:** Daily attendance marking, reports, alerts.

**Database:**
- Create migrations for: attendance_records

**Backend:**
- `AttendanceController` - Mark attendance, view attendance records, generate reports
- Add attendance summary methods: monthly reports, student attendance rate
- Automatic absence alerts for parents/guardians

**Models:**
- AttendanceRecord (relationships: student, class, marker)
- Add methods: getAttendanceRate(), generateMonthlyReport()

**Frontend Components:**
- `Attendance/AttendanceSheet.vue` - Daily attendance marking (list of students with status toggles)
- `Attendance/AttendanceCalendar.vue` - Calendar view of student attendance
- `Attendance/AttendanceReport.vue` - Attendance statistics and charts
- `UI/AttendanceStatus.vue` - Badge component for attendance status

**Pages:**
- `Pages/Admin/Attendance/Index.vue` (select class and date)
- `Pages/Admin/Attendance/Mark.vue` (attendance marking sheet)
- `Pages/Admin/Attendance/Report.vue` (class/student reports)

**Routes:**
```php
Route::prefix('admin/attendance')->group(function () {
    Route::get('/', [AttendanceController::class, 'index'])->name('admin.attendance.index');
    Route::get('mark', [AttendanceController::class, 'mark'])->name('admin.attendance.mark');
    Route::post('store', [AttendanceController::class, 'store'])->name('admin.attendance.store');
    Route::get('report', [AttendanceController::class, 'report'])->name('admin.attendance.report');
});
```

**Update Admin Dashboard:**
- Add statistics: today_attendance_rate, absent_students_today
- Add quick action for "Mark Attendance"

**Verification:**
- Mark attendance for a class
- View attendance history for a student
- Generate monthly attendance reports
- View attendance rates and trends
- Send absence alerts to parents

---

### **Phase 5: Parent Portal** (Week 8-9)
**Priority: MEDIUM**

**Scope:** Parent account creation, student progress viewing, communication.

**Database:**
- Add `portal_access` column to guardians table (boolean)
- Link guardians to users table via user_id

**Backend:**
- `ParentPortalController` - View student grades, attendance, schedule
- Update `GuardianController` - Create user accounts for guardians
- Add ParentMiddleware to restrict portal access

**Frontend Components:**
- `Portal/StudentSelector.vue` - Select which child to view (if multiple)
- `Portal/StudentDashboard.vue` - Overview of student performance
- `Portal/StudentGrades.vue` - View grades and assessments
- `Portal/StudentAttendance.vue` - View attendance records
- `Portal/StudentSchedule.vue` - View class schedule

**Pages:**
- `Pages/Portal/Dashboard.vue` (parent landing page)
- `Pages/Portal/Student.vue` (selected student details)

**Layouts:**
- Create `PortalLayout.vue` - Separate layout for parent portal

**Routes:**
```php
Route::middleware(['auth', 'parent'])->prefix('portal')->group(function () {
    Route::get('/', [ParentPortalController::class, 'index'])->name('portal.dashboard');
    Route::get('student/{student}', [ParentPortalController::class, 'student'])->name('portal.student');
});
```

**Update Navigation:**
- Add portal link for guardian users

**Verification:**
- Create guardian account
- Link guardian to student(s)
- Login as guardian and view student information
- View grades, attendance, schedule
- Restrict access to other students

---

### **Phase 6: Academic Calendar & Events** (Week 10)
**Priority: MEDIUM**

**Scope:** Comprehensive academic calendar with events, holidays, exam schedules, and notifications.

**Database:**
- Create migrations for: calendar_events

**calendar_events Table:**
```
id (bigint, PK)
title (string) - Event name
description (text, nullable) - Event details
event_type (enum) - holiday, exam, meeting, event, deadline, sports, assembly
start_date (datetime) - Event start
end_date (datetime, nullable) - Event end (for multi-day events)
all_day (boolean, default: true) - All-day event flag
location (string, nullable) - Event location
color (string, nullable) - Calendar color (#hex)
is_recurring (boolean, default: false) - Recurring event flag
recurrence_pattern (json, nullable) - Recurrence rules
target_audience (enum, nullable) - all, students, teachers, parents, admin
is_published (boolean, default: true) - Visibility flag
notify_before (integer, nullable) - Minutes before to notify
created_by (bigint, FK to users)
created_at, updated_at
```

**Recurrence Pattern JSON:**
```json
{
  "frequency": "weekly",  // daily, weekly, monthly, yearly
  "interval": 1,          // Every N days/weeks/months
  "days_of_week": [1, 3, 5],  // Monday, Wednesday, Friday
  "end_date": "2026-12-31",
  "occurrences": 10       // OR number of occurrences
}
```

**Backend:**
- `CalendarController` - Full CRUD for calendar events
- `CalendarEventService` - Handle recurring event logic
- Add notification scheduling for events
- Export calendar to iCal format

**Models:**
- CalendarEvent (relationships: creator)
- Add methods:
  - `expandRecurringEvents($startDate, $endDate)` - Generate event instances
  - `getUpcomingEvents($limit = 5)` - For dashboard widget
  - `getEventsByMonth($year, $month)` - Monthly calendar data
  - `exportToICal()` - iCal export

**Frontend Components:**
- `Calendar/CalendarView.vue` - Full calendar with multiple views
  - Month view (default)
  - Week view
  - Day view
  - Agenda/list view
- `Calendar/EventForm.vue` - Create/edit events with rich features
  - Date/time picker
  - All-day toggle
  - Recurrence options
  - Color picker
  - Target audience selector
  - Notification settings
- `Calendar/EventList.vue` - Upcoming events list with filters
- `Calendar/EventDetail.vue` - Event details modal/page
- `Calendar/EventCard.vue` - Compact event display
- `Calendar/RecurrenceBuilder.vue` - UI for building recurrence patterns
- `Calendar/MiniCalendar.vue` - Small calendar for date selection

**Pages:**
- `Pages/Admin/Calendar/Index.vue` - Full calendar management
- `Pages/Admin/Calendar/Create.vue` - Create new event
- `Pages/Admin/Calendar/Edit.vue` - Edit existing event
- `Pages/Admin/Calendar/Show.vue` - View event details
- `Pages/Calendar/Public.vue` - Public-facing calendar

**Routes:**
```php
Route::prefix('admin/calendar')->middleware(['auth'])->group(function () {
    Route::get('/', [CalendarController::class, 'index'])->name('admin.calendar.index');
    Route::get('/create', [CalendarController::class, 'create'])->name('admin.calendar.create');
    Route::post('/', [CalendarController::class, 'store'])->name('admin.calendar.store');
    Route::get('/{event}/edit', [CalendarController::class, 'edit'])->name('admin.calendar.edit');
    Route::put('/{event}', [CalendarController::class, 'update'])->name('admin.calendar.update');
    Route::delete('/{event}', [CalendarController::class, 'destroy'])->name('admin.calendar.destroy');

    // Additional routes
    Route::get('/events/{year}/{month}', [CalendarController::class, 'getMonthEvents'])
        ->name('admin.calendar.month');
    Route::get('/export', [CalendarController::class, 'exportICal'])->name('admin.calendar.export');
});

// Public calendar routes
Route::get('/calendar', [CalendarController::class, 'publicIndex'])->name('calendar.public');
Route::get('/calendar/events', [CalendarController::class, 'publicEvents'])->name('calendar.events');
Route::get('/calendar/export', [CalendarController::class, 'publicExportICal'])->name('calendar.export');
```

**Calendar Libraries:**
- Consider integrating FullCalendar.js or Vue-Cal for rich calendar UI
- Or build custom calendar component with Vue 3

**Event Types:**
- **Holiday** - School holidays, breaks (shown prominently)
- **Exam** - Midterms, finals, quizzes
- **Meeting** - Staff meetings, parent-teacher conferences
- **Event** - General school events, assemblies
- **Deadline** - Registration deadlines, fee deadlines
- **Sports** - Games, practices, tournaments
- **Assembly** - School assemblies, special programs

**Features:**
- Color-coded events by type
- Recurring events (daily, weekly, monthly, yearly)
- Multi-day events (e.g., spring break)
- Event notifications (email/in-app)
- Filter by event type and target audience
- Export to iCal/Google Calendar
- Public vs. admin-only events
- Event reminders (1 day before, 1 hour before, etc.)

**Update Admin Dashboard:**
- Add "Upcoming Events" widget (next 5 events)
- Add mini calendar widget showing current month
- Add quick action for "Manage Calendar"
- Event count statistics

**Notification Integration:**
- Send email notifications for upcoming events
- In-app notifications (via notifications table)
- Reminders based on notify_before setting
- Notify target audience only (students, teachers, parents, all)

**Verification:**
- Create one-time events (holiday, exam, meeting)
- Create recurring events (weekly class, monthly meeting)
- Edit single instance of recurring event
- Delete recurring event series
- View calendar in month/week/day views
- Filter events by type and audience
- Export calendar to iCal format
- Receive event notifications
- View public calendar (non-authenticated users)
- Display upcoming events on dashboard

---

### **Phase 7: Document Management** (Week 11)
**Priority: LOW**

**Scope:** File uploads, transcripts, certificates, document storage.

**Database:**
- Create migrations for: documents

**Backend:**
- `DocumentController` - Upload, view, download, delete documents
- Use Laravel's Storage facade for file management
- Add polymorphic relationships for attaching documents to any model

**Models:**
- Document (polymorphic: documentable)

**Frontend Components:**
- `Documents/DocumentUpload.vue` - File upload with drag-and-drop
- `Documents/DocumentList.vue` - List documents with download links
- `Documents/DocumentViewer.vue` - Preview documents (if image/PDF)

**Pages:**
- `Pages/Admin/Documents/Index.vue` (per student/teacher/course)

**Routes:**
```php
Route::prefix('admin/documents')->group(function () {
    Route::post('upload', [DocumentController::class, 'upload'])->name('admin.documents.upload');
    Route::get('{document}/download', [DocumentController::class, 'download'])->name('admin.documents.download');
    Route::delete('{document}', [DocumentController::class, 'destroy'])->name('admin.documents.destroy');
});
```

**Verification:**
- Upload student transcripts
- Upload teacher certificates
- Download documents
- Delete documents
- View document previews

---

### **Phase 8: Analytics & Reporting** (Week 12)
**Priority: LOW**

**Scope:** Reports, charts, analytics, data export.

**Backend:**
- `ReportController` - Generate various reports
- `AnalyticsController` - Dashboard with charts and statistics

**Frontend Components:**
- `Analytics/Dashboard.vue` - Admin analytics overview
- `Charts/LineChart.vue` - Enrollment trends over time
- `Charts/BarChart.vue` - Grade distribution
- `Charts/PieChart.vue` - Attendance breakdown
- `Reports/ExportButton.vue` - Export to CSV/PDF/Excel

**Pages:**
- `Pages/Admin/Analytics/Dashboard.vue`
- `Pages/Admin/Reports/Index.vue`

**Libraries to Add:**
- Chart.js or ApexCharts for data visualization
- Laravel Excel for exports

**Routes:**
```php
Route::prefix('admin/analytics')->group(function () {
    Route::get('/', [AnalyticsController::class, 'index'])->name('admin.analytics.index');
});
Route::prefix('admin/reports')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('admin.reports.index');
    Route::post('export', [ReportController::class, 'export'])->name('admin.reports.export');
});
```

**Verification:**
- View enrollment trends
- View grade distribution charts
- Export student data to Excel
- Generate custom reports

---

## 🔧 Technical Considerations

### Architecture Decisions

**1. Role-Based Access Control (RBAC)**
- Extend the current simple role system to include: admin, teacher, student, parent
- Consider using Spatie Laravel Permission package for advanced permissions
- Middleware: AdminMiddleware, TeacherMiddleware, ParentMiddleware

**2. File Storage**
- Use Laravel Storage with local driver for development
- Configure S3 or DigitalOcean Spaces for production
- Store uploads in `storage/app/public/uploads/{type}/`
- Create symbolic link: `php artisan storage:link`

**3. Performance Optimization**
- Add database indexes on frequently queried columns (student_id, course_code, etc.)
- Implement eager loading to prevent N+1 queries
- Cache frequently accessed data (academic years, courses)
- Paginate all lists (follow existing pattern: 10 per page)

**4. Data Validation**
- Create FormRequest classes for complex validation
- Follow existing pattern: use Rule::in() for enum validation
- Add unique validation for student_id, course_code, etc.

**5. Notifications**
- Use Laravel's built-in notification system
- Channels: database, mail
- Notification types: GradePosted, AttendanceAlert, EnrollmentConfirmed

**6. Search & Filtering**
- Implement search on student names, IDs, emails
- Add filters for status, academic year, term, course
- Use query scopes in models for reusable filters

**7. Testing Strategy**
- Write Feature tests for all CRUD operations
- Write Unit tests for grade calculation logic
- Write Browser tests for critical workflows (enrollment, grade entry)

---

## 📋 Component Reuse Strategy

**Existing components to leverage:**
- `Alert.vue` - Success/error messages
- `Card.vue` - Wrapper for content sections
- `PageHeader.vue` - Page titles
- `TextInput, InputLabel, InputError` - Form fields
- `PrimaryButton, DangerButton` - Actions
- `Modal` - Confirmations and forms
- `Dropdown` - Action menus
- `StatCard.vue` - Dashboard statistics

**New reusable components to create:**
- `DataTable.vue` - Sortable, searchable table wrapper (reuse across all list pages)
- `FileUpload.vue` - Drag-and-drop file upload (reuse for photos, documents)
- `StatusBadge.vue` - Colored status indicators (active/inactive/enrolled/absent)
- `DatePicker.vue` - Date selection (reuse across forms)
- `SelectInput.vue` - Enhanced select dropdown
- `SearchBar.vue` - Search input with filters
- `Pagination.vue` - Custom pagination component

---

## ✅ Verification & Testing Plan

### Phase 1 Verification
- [ ] Create a new student with photo
- [ ] Edit student information
- [ ] Add guardian to student
- [ ] Create academic year and terms
- [ ] Create courses
- [ ] Create teacher accounts
- [ ] Search and filter students
- [ ] Delete student (soft delete)

### Phase 2 Verification
- [ ] Create class with schedule
- [ ] Enroll students in class
- [ ] View class roster
- [ ] View student schedule (all classes)
- [ ] Drop student from class
- [ ] Prevent over-enrollment (max_students)
- [ ] Detect schedule conflicts

### Phase 3 Verification
- [ ] Create assessment types
- [ ] Create assessments for a class
- [ ] Enter grades for students
- [ ] View calculated final grades
- [ ] Generate report card
- [ ] Export grades to PDF

### Phase 4 Verification
- [ ] Mark daily attendance
- [ ] View student attendance history
- [ ] Generate monthly attendance report
- [ ] Calculate attendance rate
- [ ] Send absence alert to parent

### Phase 5 Verification
- [ ] Create guardian user account
- [ ] Login as guardian
- [ ] View student grades
- [ ] View student attendance
- [ ] View student schedule
- [ ] Restrict access to other students

### Phase 6 Verification
- [ ] Create calendar event
- [ ] View calendar (month/week/day)
- [ ] Filter events by type
- [ ] Display upcoming events on dashboard

### Phase 7 Verification
- [ ] Upload student document
- [ ] Download document
- [ ] Preview document (PDF/image)
- [ ] Delete document
- [ ] List all documents for a student

### Phase 8 Verification
- [ ] View enrollment trends chart
- [ ] View grade distribution chart
- [ ] Export student data to Excel
- [ ] Generate custom attendance report

---

## 🎨 UI/UX Consistency Guidelines

**Follow existing patterns:**
- Responsive design: desktop table, mobile cards (see `Admin/Users/Index.vue`)
- Pagination: 10 items per page
- Flash messages: use Alert component for success/error
- Forms: use UserForm.vue as template (two-column layout on desktop)
- Dark mode: add `dark:` variants to all new components
- Touch targets: minimum 44px on mobile
- Colors: use existing palette (indigo primary, red danger, green success)

**Navigation:**
- Add new features to Admin Dashboard as AdminActionCard components
- Update `resources/js/Layouts/AuthenticatedLayout.vue` navigation as needed
- Use route names consistently (admin.{feature}.{action})

**Loading States:**
- Add loading spinners for long operations (grade calculations, report generation)
- Disable buttons during form submission

---

## 🚀 Getting Started (Implementation Order)

**Per User Requirement: Database & Backend First, Then Frontend**

1. **Phase 0 (Theme Foundation):** Integrate theme system into existing components
2. **Database First:** Create all Phase 1 migrations
3. **Models Second:** Create models with relationships
4. **Controllers Third:** Implement CRUD operations with API endpoints
5. **Backend Testing:** Test all endpoints and business logic
6. **Components Fourth:** Build reusable components (using theme system)
7. **Pages Fifth:** Assemble pages using components
8. **Routes Sixth:** Register routes
9. **Dashboard Last:** Update admin dashboard with new statistics and actions

**Recommended Implementation Sequence:**
1. **Phase 0**: Theme Integration (foundation for all future components)
2. **Phase 1**: Students → Courses → Teachers (database & backend)
3. **Phase 2**: Classes → Enrollment (database & backend)
4. **Phase 3**: Grades (database & backend)
5. **Phase 4**: Attendance (database & backend)
6. **Phase 5**: Parent Portal (database & backend, then frontend)
7. **Phase 6**: Calendar (database & backend)
8. **Phase 7**: Documents (database & backend)
9. **Phase 8**: Analytics (backend calculations, then frontend charts)

---

## 📦 Dependencies to Add

**Backend:**
```bash
composer require spatie/laravel-permission  # Role & permission management
composer require maatwebsite/excel          # Excel export
composer require barryvdh/laravel-dompdf    # PDF generation
```

**Frontend:**
```bash
npm install chart.js vue-chartjs            # Charts for analytics
npm install @vuepic/vue-datepicker          # Date picker component
npm install @headlessui/vue                 # Additional UI components
```

---

## 📈 Success Metrics

**Phase 1 Complete:**
- Can manage 100+ students
- Can create courses and assign teachers
- All CRUD operations working smoothly

**Phase 2 Complete:**
- Can schedule classes for an entire term
- Can enroll students efficiently
- No schedule conflicts

**Phase 3 Complete:**
- Teachers can enter grades quickly
- Final grades calculate correctly
- Report cards generate successfully

**Phase 4 Complete:**
- Attendance marking takes < 2 minutes per class
- Attendance reports accessible instantly
- Parents receive absence notifications

**Phase 5 Complete:**
- Parents can access portal
- All student data displays correctly
- No unauthorized access issues

**Phases 6-8 Complete:**
- Full calendar functionality
- Documents uploading and downloading
- Analytics providing actionable insights

---

## 🎯 Long-Term Vision

**Future Enhancements (Post-Phase 8):**
- Online assignment submission
- Student portal with homework view
- SMS notifications for parents
- Mobile app (React Native or Flutter)
- Integration with learning management systems (LMS)
- Library management
- Transportation tracking
- Cafeteria management
- Behavior tracking and disciplinary records

**Scalability Considerations:**
- Database partitioning for large datasets (10,000+ students)
- Redis caching for frequently accessed data
- Queue workers for heavy operations (report generation, bulk emails)
- API rate limiting for external integrations

---

## End of Roadmap

This comprehensive roadmap provides a clear, phased approach to building Citadel SMS into a full-featured Student Management System. Each phase builds upon the previous one, following existing architectural patterns and maintaining code quality standards.

**Total Estimated Timeline:**
- Phase 0 (Theme Integration): 3-5 days
- Phases 1-8 (Core Features): 12 weeks
- **Total: ~13 weeks**

**Next Steps:**
1. Review this roadmap and finalize database structure requirements
2. Begin Phase 0 (Theme Integration) to establish foundation
3. Proceed with database and backend implementation (Phases 1-8)
