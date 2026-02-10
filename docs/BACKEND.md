# Student Management System - Backend Architecture

**Version:** 2.0 (Phase 0-2 Complete)
**Last Updated:** February 9, 2026
**Framework:** Laravel 12.x

---

## Table of Contents

- [Overview](#overview)
- [Directory Structure](#directory-structure)
- [Models](#models)
- [Controllers](#controllers)
- [Routes](#routes)
- [Middleware](#middleware)
- [Validation](#validation)
- [Services](#services)
- [Events & Listeners](#events--listeners)
- [File Uploads](#file-uploads)
- [Authentication & Authorization](#authentication--authorization)
- [Testing](#testing)

---

## Overview

Student Management System backend is built with Laravel 12, following modern Laravel conventions and best practices. The architecture emphasizes:

- **MVC Pattern** - Separation of concerns
- **Eloquent ORM** - Database abstraction and relationships
- **Inertia.js** - Seamless SPA without separate API
- **Form Requests** - Validation encapsulation
- **Service Layer** - Complex business logic
- **Event-Driven** - Decoupled notification and logging

**Key Features:**
- RESTful resource controllers for CRUD operations
- Auto-generated unique IDs (student_id, teacher_id)
- File upload handling (student/teacher photos)
- Soft deletes for data retention
- Query scopes for reusable filters
- Eager loading to prevent N+1 queries
- Role-based access control (future)

---

## Directory Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── AcademicYearController.php
│   │   │   ├── ClassController.php          # Phase 2
│   │   │   ├── CourseController.php
│   │   │   ├── EnrollmentController.php     # Phase 2
│   │   │   ├── GuardianController.php
│   │   │   ├── StudentController.php
│   │   │   └── TeacherController.php
│   │   ├── AdminController.php
│   │   ├── ProfileController.php
│   │   ├── ThemeController.php
│   │   └── UserManagementController.php
│   ├── Middleware/
│   │   ├── HandleInertiaRequests.php
│   │   └── (custom middleware - future)
│   └── Requests/
│       └── (form requests - future)
├── Models/
│   ├── AcademicYear.php
│   ├── ClassModel.php                       # Phase 2
│   ├── Course.php
│   ├── Enrollment.php                       # Phase 2
│   ├── Guardian.php
│   ├── Setting.php
│   ├── Student.php
│   ├── Teacher.php
│   ├── Term.php
│   └── User.php
├── Providers/
│   ├── AppServiceProvider.php
│   └── (custom service providers - future)
└── Services/
    └── (service classes - future)
```

---

## Models

### Model Conventions

All models follow Laravel conventions:
- Extend `Illuminate\Database\Eloquent\Model`
- Use `HasFactory` trait for testing
- Define `$fillable` or `$guarded` for mass assignment protection
- Define relationships using Eloquent relationship methods
- Define query scopes for reusable filters
- Use `$casts` for type casting
- Use `SoftDeletes` trait where appropriate

---

### Student Model

**File:** `app/Models/Student.php`

**Purpose:** Represents student records with demographics, enrollment, and relationships.

**Key Features:**
- Auto-generates unique `student_id` (STU-YYYY-###)
- Many-to-many relationship with guardians
- Soft deletes enabled
- Query scopes for filtering

**Properties:**

```php
protected $fillable = [
    'first_name', 'last_name', 'middle_name',
    'date_of_birth', 'gender', 'email', 'phone',
    'address', 'city', 'state', 'postal_code', 'country',
    'emergency_contact_name', 'emergency_contact_phone',
    'enrollment_date', 'status', 'photo', 'notes', 'user_id'
];

protected $casts = [
    'date_of_birth' => 'date',
    'enrollment_date' => 'date',
];
```

**Auto-Generated ID:**

```php
protected static function boot()
{
    parent::boot();

    static::creating(function ($student) {
        if (empty($student->student_id)) {
            $student->student_id = static::generateStudentId();
        }
    });
}

public static function generateStudentId(): string
{
    $year = date('Y');
    $lastStudent = static::whereYear('created_at', $year)
        ->orderBy('id', 'desc')
        ->first();

    $number = $lastStudent ? intval(substr($lastStudent->student_id, -3)) + 1 : 1;

    return sprintf('STU-%s-%03d', $year, $number);
}
```

**Relationships:**

```php
// Student belongs to User (for portal access)
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}

// Student has many guardians (many-to-many)
public function guardians(): BelongsToMany
{
    return $this->belongsToMany(Guardian::class, 'guardian_student')
        ->withPivot('is_primary')
        ->withTimestamps();
}

// Future relationships:
// public function enrollments(): HasMany
// public function attendanceRecords(): HasMany
```

**Computed Attributes:**

```php
public function getFullNameAttribute(): string
{
    return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
}
```

**Query Scopes:**

```php
// Filter by status
public function scopeActive($query)
{
    return $query->where('status', 'active');
}

public function scopeStatus($query, $status)
{
    return $query->where('status', $status);
}

// Search by name, email, or student ID
public function scopeSearch($query, $search)
{
    return $query->where(function ($q) use ($search) {
        $q->where('student_id', 'like', "%{$search}%")
          ->orWhere('first_name', 'like', "%{$search}%")
          ->orWhere('last_name', 'like', "%{$search}%")
          ->orWhere('email', 'like', "%{$search}%");
    });
}
```

**Usage Examples:**

```php
// Get all active students
$students = Student::active()->get();

// Search students
$students = Student::search('John')->get();

// Get student with guardians (eager loading)
$student = Student::with('guardians')->find($id);

// Create new student (auto-generates student_id)
$student = Student::create([
    'first_name' => 'John',
    'last_name' => 'Doe',
    'date_of_birth' => '2008-05-15',
    'enrollment_date' => now(),
    'status' => 'active',
]);
// $student->student_id will be "STU-2026-001" (auto-generated)
```

---

### Guardian Model

**File:** `app/Models/Guardian.php`

**Purpose:** Represents parent/guardian information.

**Properties:**

```php
protected $fillable = [
    'first_name', 'last_name', 'relationship',
    'email', 'phone', 'address', 'occupation', 'user_id'
];
```

**Relationships:**

```php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}

public function students(): BelongsToMany
{
    return $this->belongsToMany(Student::class, 'guardian_student')
        ->withPivot('is_primary')
        ->withTimestamps();
}
```

**Usage Examples:**

```php
// Create guardian and link to student
$guardian = Guardian::create([
    'first_name' => 'Jane',
    'last_name' => 'Doe',
    'relationship' => 'mother',
    'phone' => '555-1234',
]);

$student->guardians()->attach($guardian->id, ['is_primary' => true]);

// Get all students for a guardian
$students = $guardian->students;
```

---

### Course Model

**File:** `app/Models/Course.php`

**Purpose:** Represents course catalog (course definitions).

**Properties:**

```php
protected $fillable = [
    'course_code', 'name', 'description',
    'credits', 'department', 'level', 'is_active'
];

protected $casts = [
    'credits' => 'decimal:2',
    'is_active' => 'boolean',
];
```

**Query Scopes:**

```php
public function scopeDepartment($query, $department)
{
    return $query->where('department', $department);
}

public function scopeLevel($query, $level)
{
    return $query->where('level', $level);
}

public function scopeActive($query)
{
    return $query->where('is_active', true);
}
```

**Usage Examples:**

```php
// Get all Math courses
$mathCourses = Course::department('Mathematics')->get();

// Get all beginner courses
$beginnerCourses = Course::level('Beginner')->get();

// Get active courses only
$activeCourses = Course::active()->get();
```

---

### Teacher Model

**File:** `app/Models/Teacher.php`

**Purpose:** Represents teacher profiles and qualifications.

**Key Features:**
- Auto-generates unique `teacher_id` (TCH-YYYY-###)
- Belongs to User for authentication
- Soft deletes enabled

**Properties:**

```php
protected $fillable = [
    'user_id', 'first_name', 'last_name', 'email', 'phone',
    'date_of_birth', 'hire_date', 'department', 'specialization',
    'qualifications', 'photo', 'status'
];

protected $casts = [
    'date_of_birth' => 'date',
    'hire_date' => 'date',
];
```

**Auto-Generated ID:**

```php
protected static function boot()
{
    parent::boot();

    static::creating(function ($teacher) {
        if (empty($teacher->teacher_id)) {
            $teacher->teacher_id = static::generateTeacherId();
        }
    });
}

public static function generateTeacherId(): string
{
    $year = date('Y');
    $lastTeacher = static::whereYear('created_at', $year)
        ->orderBy('id', 'desc')
        ->first();

    $number = $lastTeacher ? intval(substr($lastTeacher->teacher_id, -3)) + 1 : 1;

    return sprintf('TCH-%s-%03d', $year, $number);
}
```

**Relationships:**

```php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}

// Future: public function classes(): HasMany
```

**Query Scopes:**

```php
public function scopeActive($query)
{
    return $query->where('status', 'active');
}

public function scopeDepartment($query, $department)
{
    return $query->where('department', $department);
}

public function scopeSearch($query, $search)
{
    return $query->where(function ($q) use ($search) {
        $q->where('teacher_id', 'like', "%{$search}%")
          ->orWhere('first_name', 'like', "%{$search}%")
          ->orWhere('last_name', 'like', "%{$search}%")
          ->orWhere('email', 'like', "%{$search}%");
    });
}
```

---

### AcademicYear & Term Models

**File:** `app/Models/AcademicYear.php`, `app/Models/Term.php`

**Purpose:** Manage academic years and terms/semesters.

**AcademicYear Properties:**

```php
protected $fillable = ['name', 'start_date', 'end_date', 'is_current'];

protected $casts = [
    'start_date' => 'date',
    'end_date' => 'date',
    'is_current' => 'boolean',
];
```

**AcademicYear Relationships:**

```php
public function terms(): HasMany
{
    return $this->hasMany(Term::class);
}
```

**Term Properties:**

```php
protected $fillable = [
    'academic_year_id', 'name', 'start_date', 'end_date', 'is_current'
];

protected $casts = [
    'start_date' => 'date',
    'end_date' => 'date',
    'is_current' => 'boolean',
];
```

**Term Relationships:**

```php
public function academicYear(): BelongsTo
{
    return $this->belongsTo(AcademicYear::class);
}
```

**Usage Examples:**

```php
// Get current academic year
$currentYear = AcademicYear::where('is_current', true)->first();

// Get all terms for an academic year
$terms = $currentYear->terms;

// Create new term
$currentYear->terms()->create([
    'name' => 'Fall 2026',
    'start_date' => '2026-08-15',
    'end_date' => '2026-12-20',
    'is_current' => true,
]);
```

---

### ClassModel (Phase 2)

**File:** `app/Models/ClassModel.php`

**Purpose:** Represents class sections (instances of courses) with schedules, capacity, and enrollment tracking.

**Key Features:**
- JSON schedule storage for flexible scheduling
- Capacity enforcement (max_students)
- Schedule conflict detection
- Enrollment count tracking
- Query scopes for availability

**Properties:**

```php
protected $table = 'classes'; // 'Class' is a reserved keyword

protected $fillable = [
    'course_id', 'teacher_id', 'academic_year_id', 'term_id',
    'section_name', 'room', 'schedule', 'max_students', 'status'
];

protected $casts = [
    'schedule' => 'array',
    'max_students' => 'integer',
];
```

**Relationships:**

```php
public function course(): BelongsTo
{
    return $this->belongsTo(Course::class);
}

public function teacher(): BelongsTo
{
    return $this->belongsTo(Teacher::class);
}

public function academicYear(): BelongsTo
{
    return $this->belongsTo(AcademicYear::class);
}

public function term(): BelongsTo
{
    return $this->belongsTo(Term::class);
}

public function enrollments(): HasMany
{
    return $this->hasMany(Enrollment::class, 'class_id');
}

public function students()
{
    return $this->hasManyThrough(
        Student::class,
        Enrollment::class,
        'class_id', 'id', 'id', 'student_id'
    );
}
```

**Computed Attributes:**

```php
public function getEnrolledCountAttribute(): int
{
    return $this->enrollments()->where('status', 'enrolled')->count();
}

public function getAvailableSeatsAttribute(): int
{
    return max(0, $this->max_students - $this->enrolled_count);
}

public function isFull(): bool
{
    return $this->enrolled_count >= $this->max_students;
}
```

**Query Scopes:**

```php
// Get open classes
public function scopeOpen($query)
{
    return $query->where('status', 'open');
}

// Get classes with available seats
public function scopeAvailable($query)
{
    return $query->where('status', 'open')
        ->whereRaw("(SELECT COUNT(*) FROM enrollments WHERE class_id = classes.id AND status = 'enrolled') < max_students");
}

// Filter by term, course, teacher, status
public function scopeTerm($query, $termId)
public function scopeCourse($query, $courseId)
public function scopeTeacher($query, $teacherId)
public function scopeStatus($query, $status)

// Search classes
public function scopeSearch($query, $search)
{
    return $query->whereHas('course', function ($q) use ($search) {
        $q->where('name', 'like', "%{$search}%")
          ->orWhere('course_code', 'like', "%{$search}%");
    })->orWhereHas('teacher', function ($q) use ($search) {
        $q->where('first_name', 'like', "%{$search}%")
          ->orWhere('last_name', 'like', "%{$search}%");
    })->orWhere('section_name', 'like', "%{$search}%")
      ->orWhere('room', 'like', "%{$search}%");
}
```

**Usage Examples:**

```php
// Create class with schedule
$class = ClassModel::create([
    'course_id' => 1,
    'teacher_id' => 1,
    'academic_year_id' => 1,
    'term_id' => 1,
    'section_name' => 'A',
    'room' => '101',
    'schedule' => [
        ['day' => 'Monday', 'start_time' => '09:00', 'end_time' => '10:30'],
        ['day' => 'Wednesday', 'start_time' => '09:00', 'end_time' => '10:30'],
    ],
    'max_students' => 30,
    'status' => 'open',
]);

// Get available classes for a term
$classes = ClassModel::available()
    ->term($termId)
    ->with(['course', 'teacher'])
    ->get();

// Check if class is full
if ($class->isFull()) {
    // Cannot enroll more students
}

// Get enrolled students
$students = $class->students;
```

---

### Enrollment Model (Phase 2)

**File:** `app/Models/Enrollment.php`

**Purpose:** Represents student enrollments in classes with status tracking.

**Key Features:**
- Unique constraint (student_id, class_id) prevents duplicates
- Status tracking (enrolled, dropped, completed, failed)
- Future: Grade storage (final_grade, grade_points)

**Properties:**

```php
protected $fillable = [
    'student_id', 'class_id', 'enrollment_date',
    'status', 'final_grade', 'grade_points'
];

protected $casts = [
    'enrollment_date' => 'date',
    'grade_points' => 'decimal:2',
];
```

**Relationships:**

```php
public function student(): BelongsTo
{
    return $this->belongsTo(Student::class);
}

public function class(): BelongsTo
{
    return $this->belongsTo(ClassModel::class, 'class_id');
}

// Future: Phase 3
public function grades(): HasMany
{
    return $this->hasMany(Grade::class);
}
```

**Query Scopes:**

```php
// Filter active enrollments
public function scopeEnrolled($query)
{
    return $query->where('status', 'enrolled');
}

// Filter by student, class, or status
public function scopeStudent($query, $studentId)
public function scopeClass($query, $classId)
public function scopeStatus($query, $status)

// Filter by term (through class relationship)
public function scopeTerm($query, $termId)
{
    return $query->whereHas('class', function ($q) use ($termId) {
        $q->where('term_id', $termId);
    });
}
```

**Methods:**

```php
public function isActive(): bool
{
    return $this->status === 'enrolled';
}

// Future: Phase 3
public function calculateFinalGrade(): void
{
    // Will aggregate assessment grades
}
```

**Usage Examples:**

```php
// Enroll student in class
$enrollment = Enrollment::create([
    'student_id' => $student->id,
    'class_id' => $class->id,
    'enrollment_date' => now(),
    'status' => 'enrolled',
]);

// Get all enrollments for a student in current term
$enrollments = Enrollment::enrolled()
    ->student($studentId)
    ->term($currentTermId)
    ->with('class.course')
    ->get();

// Drop student from class
$enrollment->update(['status' => 'dropped']);
```

---

### Setting Model

**File:** `app/Models/Setting.php`

**Purpose:** Store system-wide settings (including theme).

**Properties:**

```php
protected $fillable = ['key', 'value'];
```

**Helper Methods:**

```php
public static function get(string $key, $default = null)
{
    $setting = static::where('key', $key)->first();
    return $setting ? $setting->value : $default;
}

public static function set(string $key, $value): void
{
    static::updateOrCreate(['key' => $key], ['value' => $value]);
}
```

**Usage Examples:**

```php
// Get theme color
$primaryColor = Setting::get('theme_primary_color', '#1B3A6B');

// Set theme color
Setting::set('theme_primary_color', '#1B3A6B');
```

---

## Controllers

### Controller Conventions

All controllers follow Laravel conventions:
- Extend `Illuminate\Http\Controller` (or `App\Http\Controllers\Controller`)
- Use Inertia for rendering views
- Return Inertia responses (not JSON for web routes)
- Follow RESTful naming (index, create, store, show, edit, update, destroy)
- Use route model binding where possible
- Validate input using `$request->validate()` or FormRequest classes

---

### StudentController

**File:** `app/Http/Controllers/Admin/StudentController.php`

**Purpose:** CRUD operations for students.

**Methods:**

#### `index()` - List All Students

```php
public function index(Request $request)
{
    $students = Student::query()
        ->with('guardians')
        ->when($request->search, function ($query, $search) {
            $query->search($search);
        })
        ->when($request->status, function ($query, $status) {
            $query->status($status);
        })
        ->paginate(10)
        ->withQueryString();

    return Inertia::render('Admin/Students/Index', [
        'students' => $students,
        'filters' => $request->only(['search', 'status']),
    ]);
}
```

**Features:**
- Eager loads guardians to prevent N+1 queries
- Supports search filter
- Supports status filter
- Pagination with 10 items per page
- Preserves query string in pagination links

#### `create()` - Show Create Form

```php
public function create()
{
    return Inertia::render('Admin/Students/Create');
}
```

#### `store()` - Create New Student

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'first_name' => ['required', 'string', 'max:255'],
        'last_name' => ['required', 'string', 'max:255'],
        'middle_name' => ['nullable', 'string', 'max:255'],
        'date_of_birth' => ['required', 'date', 'before:today'],
        'gender' => ['required', 'in:male,female,other'],
        'email' => ['nullable', 'email', 'unique:students,email'],
        'phone' => ['nullable', 'string', 'max:20'],
        'address' => ['nullable', 'string'],
        'city' => ['nullable', 'string', 'max:255'],
        'state' => ['nullable', 'string', 'max:255'],
        'postal_code' => ['nullable', 'string', 'max:20'],
        'country' => ['nullable', 'string', 'max:255'],
        'emergency_contact_name' => ['nullable', 'string', 'max:255'],
        'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
        'enrollment_date' => ['required', 'date'],
        'status' => ['required', 'in:active,inactive,graduated,withdrawn,suspended'],
        'photo' => ['nullable', 'image', 'max:2048'], // 2MB max
        'notes' => ['nullable', 'string'],
    ]);

    // Handle photo upload
    if ($request->hasFile('photo')) {
        $validated['photo'] = $request->file('photo')->store('students/photos', 'public');
    }

    $student = Student::create($validated);

    return redirect()->route('admin.students.show', $student)
        ->with('success', 'Student created successfully.');
}
```

**Features:**
- Comprehensive validation
- Photo upload handling (stored in `storage/app/public/students/photos/`)
- Auto-generates student_id via model boot event
- Redirects to show page with success message

#### `show()` - Display Student Details

```php
public function show(Student $student)
{
    $student->load('guardians', 'user');

    return Inertia::render('Admin/Students/Show', [
        'student' => $student,
    ]);
}
```

**Features:**
- Route model binding automatically loads student
- Eager loads guardians and user

#### `edit()` - Show Edit Form

```php
public function edit(Student $student)
{
    return Inertia::render('Admin/Students/Edit', [
        'student' => $student,
    ]);
}
```

#### `update()` - Update Student

```php
public function update(Request $request, Student $student)
{
    $validated = $request->validate([
        // Same validation rules as store()
        'email' => ['nullable', 'email', 'unique:students,email,' . $student->id],
    ]);

    // Handle photo upload
    if ($request->hasFile('photo')) {
        // Delete old photo
        if ($student->photo) {
            Storage::disk('public')->delete($student->photo);
        }
        $validated['photo'] = $request->file('photo')->store('students/photos', 'public');
    }

    $student->update($validated);

    return redirect()->route('admin.students.show', $student)
        ->with('success', 'Student updated successfully.');
}
```

**Features:**
- Validates email uniqueness (excluding current student)
- Deletes old photo before uploading new one
- Updates student record

#### `destroy()` - Delete Student (Soft Delete)

```php
public function destroy(Student $student)
{
    $student->delete(); // Soft delete

    return redirect()->route('admin.students.index')
        ->with('success', 'Student deleted successfully.');
}
```

**Features:**
- Soft delete (record not actually removed)
- Can be restored later if needed

---

### GuardianController

**File:** `app/Http/Controllers/Admin/GuardianController.php`

**Purpose:** CRUD operations for guardians.

**Key Methods:**

```php
public function index(Request $request)
{
    $guardians = Guardian::query()
        ->with('students')
        ->when($request->search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        })
        ->paginate(10)
        ->withQueryString();

    return Inertia::render('Admin/Guardians/Index', [
        'guardians' => $guardians,
        'filters' => $request->only(['search']),
    ]);
}

public function store(Request $request)
{
    $validated = $request->validate([
        'first_name' => ['required', 'string', 'max:255'],
        'last_name' => ['required', 'string', 'max:255'],
        'relationship' => ['required', 'in:mother,father,guardian,grandparent,other'],
        'email' => ['nullable', 'email', 'unique:guardians,email'],
        'phone' => ['required', 'string', 'max:20'],
        'address' => ['nullable', 'string'],
        'occupation' => ['nullable', 'string', 'max:255'],
    ]);

    $guardian = Guardian::create($validated);

    // Optionally link to student
    if ($request->student_id) {
        $guardian->students()->attach($request->student_id, [
            'is_primary' => $request->is_primary ?? false
        ]);
    }

    return redirect()->route('admin.guardians.show', $guardian)
        ->with('success', 'Guardian created successfully.');
}
```

---

### CourseController

**File:** `app/Http/Controllers/Admin/CourseController.php`

**Purpose:** CRUD operations for courses.

**Key Methods:**

```php
public function index(Request $request)
{
    $courses = Course::query()
        ->when($request->department, function ($query, $department) {
            $query->department($department);
        })
        ->when($request->level, function ($query, $level) {
            $query->level($level);
        })
        ->when($request->search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('course_code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        })
        ->paginate(10)
        ->withQueryString();

    $departments = Course::distinct()->pluck('department');
    $levels = Course::distinct()->pluck('level');

    return Inertia::render('Admin/Courses/Index', [
        'courses' => $courses,
        'departments' => $departments,
        'levels' => $levels,
        'filters' => $request->only(['search', 'department', 'level']),
    ]);
}

public function store(Request $request)
{
    $validated = $request->validate([
        'course_code' => ['required', 'string', 'unique:courses,course_code', 'max:50'],
        'name' => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string'],
        'credits' => ['nullable', 'numeric', 'min:0', 'max:10'],
        'department' => ['nullable', 'string', 'max:255'],
        'level' => ['nullable', 'string', 'max:255'],
        'is_active' => ['boolean'],
    ]);

    $course = Course::create($validated);

    return redirect()->route('admin.courses.show', $course)
        ->with('success', 'Course created successfully.');
}
```

**Features:**
- Filters by department, level, and search
- Provides distinct departments and levels for filter dropdowns
- Validates course_code uniqueness

---

### TeacherController

**File:** `app/Http/Controllers/Admin/TeacherController.php`

**Purpose:** CRUD operations for teachers.

**Key Methods:**

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'user_id' => ['required', 'exists:users,id'],
        'first_name' => ['required', 'string', 'max:255'],
        'last_name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'unique:teachers,email'],
        'phone' => ['nullable', 'string', 'max:20'],
        'date_of_birth' => ['nullable', 'date', 'before:today'],
        'hire_date' => ['required', 'date'],
        'department' => ['nullable', 'string', 'max:255'],
        'specialization' => ['nullable', 'string', 'max:255'],
        'qualifications' => ['nullable', 'string'],
        'photo' => ['nullable', 'image', 'max:2048'],
        'status' => ['required', 'in:active,inactive,on_leave'],
    ]);

    // Handle photo upload
    if ($request->hasFile('photo')) {
        $validated['photo'] = $request->file('photo')->store('teachers/photos', 'public');
    }

    $teacher = Teacher::create($validated);

    return redirect()->route('admin.teachers.show', $teacher)
        ->with('success', 'Teacher created successfully.');
}
```

**Features:**
- Links teacher to existing user account
- Photo upload handling
- Auto-generates teacher_id via model boot event

---

### AcademicYearController

**File:** `app/Http/Controllers/Admin/AcademicYearController.php`

**Purpose:** Manage academic years and terms.

**Key Methods:**

```php
public function index()
{
    $academicYears = AcademicYear::with('terms')->get();

    return Inertia::render('Admin/AcademicYears/Index', [
        'academicYears' => $academicYears,
    ]);
}

public function store(Request $request)
{
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'start_date' => ['required', 'date'],
        'end_date' => ['required', 'date', 'after:start_date'],
        'is_current' => ['boolean'],
    ]);

    // If marking as current, unset other current years
    if ($validated['is_current'] ?? false) {
        AcademicYear::where('is_current', true)->update(['is_current' => false]);
    }

    $academicYear = AcademicYear::create($validated);

    return redirect()->route('admin.academic-years.index')
        ->with('success', 'Academic year created successfully.');
}

public function storeTerm(Request $request, AcademicYear $academicYear)
{
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'start_date' => ['required', 'date'],
        'end_date' => ['required', 'date', 'after:start_date'],
        'is_current' => ['boolean'],
    ]);

    // If marking as current, unset other current terms
    if ($validated['is_current'] ?? false) {
        Term::where('is_current', true)->update(['is_current' => false]);
    }

    $academicYear->terms()->create($validated);

    return redirect()->route('admin.academic-years.index')
        ->with('success', 'Term created successfully.');
}
```

**Features:**
- Ensures only one academic year/term is marked as current
- Nested relationship for creating terms within academic year
- Validates end_date is after start_date

---

### ThemeController

**File:** `app/Http/Controllers/ThemeController.php`

**Purpose:** Manage theme settings.

**Methods:**

```php
public function update(Request $request)
{
    $validated = $request->validate([
        'primary_color' => ['required', 'string', 'regex:/^#[0-9A-F]{6}$/i'],
        'secondary_color' => ['required', 'string', 'regex:/^#[0-9A-F]{6}$/i'],
        'accent_color' => ['required', 'string', 'regex:/^#[0-9A-F]{6}$/i'],
        'background_color' => ['required', 'string', 'regex:/^#[0-9A-F]{6}$/i'],
        'text_color' => ['required', 'string', 'regex:/^#[0-9A-F]{6}$/i'],
    ]);

    Setting::set('theme_primary_color', $validated['primary_color']);
    Setting::set('theme_secondary_color', $validated['secondary_color']);
    Setting::set('theme_accent_color', $validated['accent_color']);
    Setting::set('theme_background_color', $validated['background_color']);
    Setting::set('theme_text_color', $validated['text_color']);

    return back()->with('success', 'Theme updated successfully.');
}

public function show()
{
    return response()->json([
        'primary_color' => Setting::get('theme_primary_color', '#1B3A6B'),
        'secondary_color' => Setting::get('theme_secondary_color', '#FFB81C'),
        'accent_color' => Setting::get('theme_accent_color', '#C8102E'),
        'background_color' => Setting::get('theme_background_color', '#ffffff'),
        'text_color' => Setting::get('theme_text_color', '#1f2937'),
    ]);
}
```

**Features:**
- Validates hex color format
- Returns JSON for API endpoint (`/api/theme`)
- Uses Setting model for storage

---

### ClassController (Phase 2)

**File:** `app/Http/Controllers/Admin/ClassController.php`

**Purpose:** Manage class sections with scheduling and capacity control.

**Key Features:**
- Schedule conflict detection for teachers
- Capacity validation (cannot reduce below enrolled count)
- Prevents deletion if enrollments exist
- Eager loads relationships for performance

#### `index()` - List All Classes

```php
public function index(Request $request)
{
    $classes = ClassModel::query()
        ->with(['course', 'teacher', 'term.academicYear'])
        ->when($request->term_id, fn($q, $id) => $q->term($id))
        ->when($request->course_id, fn($q, $id) => $q->course($id))
        ->when($request->teacher_id, fn($q, $id) => $q->teacher($id))
        ->when($request->status, fn($q, $status) => $q->status($status))
        ->when($request->search, fn($q, $search) => $q->search($search))
        ->paginate(10)
        ->withQueryString();

    return Inertia::render('Admin/Classes/Index', [
        'classes' => $classes,
        'terms' => Term::with('academicYear')->get(),
        'courses' => Course::active()->get(),
        'teachers' => Teacher::active()->get(),
        'filters' => $request->only(['search', 'term_id', 'course_id', 'teacher_id', 'status']),
    ]);
}
```

#### `store()` - Create New Class

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'course_id' => ['required', 'exists:courses,id'],
        'teacher_id' => ['required', 'exists:teachers,id'],
        'academic_year_id' => ['required', 'exists:academic_years,id'],
        'term_id' => ['required', 'exists:terms,id'],
        'section_name' => ['required', 'string', 'max:255'],
        'room' => ['nullable', 'string', 'max:255'],
        'schedule' => ['nullable', 'array'],
        'schedule.*.day' => ['required', 'string', 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday'],
        'schedule.*.start_time' => ['required', 'string'],
        'schedule.*.end_time' => ['required', 'string'],
        'max_students' => ['required', 'integer', 'min:1', 'max:500'],
        'status' => ['required', 'in:open,closed,in_progress,completed'],
    ]);

    // Check for schedule conflicts
    if (!empty($validated['schedule'])) {
        $conflict = $this->checkScheduleConflict(
            $validated['teacher_id'],
            $validated['term_id'],
            $validated['schedule']
        );

        if ($conflict) {
            return back()->withErrors([
                'schedule' => "Schedule conflict detected with {$conflict->course->name}"
            ])->withInput();
        }
    }

    $class = ClassModel::create($validated);

    return redirect()->route('admin.classes.show', $class)
        ->with('success', 'Class created successfully.');
}
```

#### Schedule Conflict Detection

```php
private function checkScheduleConflict($teacherId, $termId, $schedule, $excludeClassId = null)
{
    $teacherClasses = ClassModel::where('teacher_id', $teacherId)
        ->where('term_id', $termId)
        ->when($excludeClassId, fn($q, $id) => $q->where('id', '!=', $id))
        ->with('course')
        ->get();

    foreach ($teacherClasses as $existingClass) {
        if (empty($existingClass->schedule)) continue;

        foreach ($schedule as $newSlot) {
            foreach ($existingClass->schedule as $existingSlot) {
                if ($newSlot['day'] === $existingSlot['day']) {
                    if ($this->timesOverlap(
                        $newSlot['start_time'], $newSlot['end_time'],
                        $existingSlot['start_time'], $existingSlot['end_time']
                    )) {
                        return $existingClass;
                    }
                }
            }
        }
    }

    return null;
}

private function timesOverlap($start1, $end1, $start2, $end2)
{
    return ($start1 < $end2) && ($end1 > $start2);
}
```

#### `update()` - Update Class

**Key Validations:**
- Schedule conflict detection (excluding current class)
- Cannot reduce max_students below current enrolled count
- Full validation same as store()

#### `destroy()` - Delete Class

```php
public function destroy(ClassModel $class)
{
    if ($class->enrollments()->count() > 0) {
        return back()->withErrors([
            'error' => 'Cannot delete class with existing enrollments.'
        ]);
    }

    $class->delete();

    return redirect()->route('admin.classes.index')
        ->with('success', 'Class deleted successfully.');
}
```

---

### EnrollmentController (Phase 2)

**File:** `app/Http/Controllers/Admin/EnrollmentController.php`

**Purpose:** Manage student enrollments with validation and conflict detection.

**Key Features:**
- Duplicate enrollment prevention
- Capacity enforcement
- Schedule conflict detection for students
- Drop/withdraw functionality

#### `index()` - List All Enrollments

```php
public function index(Request $request)
{
    $enrollments = Enrollment::query()
        ->with(['student', 'class.course', 'class.teacher', 'class.term'])
        ->when($request->student_id, fn($q, $id) => $q->student($id))
        ->when($request->class_id, fn($q, $id) => $q->class($id))
        ->when($request->term_id, fn($q, $id) => $q->term($id))
        ->when($request->status, fn($q, $status) => $q->status($status))
        ->when($request->search, fn($q, $search) => $q->whereHas('student', function ($query) use ($search) {
            $query->where('student_id', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
        }))
        ->orderBy('created_at', 'desc')
        ->paginate(10);

    return Inertia::render('Admin/Enrollment/Index', [
        'enrollments' => $enrollments,
        'students' => Student::active()->get(),
        'terms' => Term::with('academicYear')->get(),
        'filters' => $request->only(['search', 'student_id', 'class_id', 'term_id', 'status']),
    ]);
}
```

#### `enroll()` - Enroll Student in Class

```php
public function enroll(Request $request)
{
    $validated = $request->validate([
        'student_id' => ['required', 'exists:students,id'],
        'class_id' => ['required', 'exists:classes,id'],
        'enrollment_date' => ['nullable', 'date'],
    ]);

    $student = Student::findOrFail($validated['student_id']);
    $class = ClassModel::findOrFail($validated['class_id']);

    // Check if already enrolled
    $existing = Enrollment::where('student_id', $student->id)
        ->where('class_id', $class->id)
        ->first();

    if ($existing) {
        return back()->withErrors([
            'error' => 'Student is already enrolled in this class.'
        ]);
    }

    // Check if class is full
    if ($class->isFull()) {
        return back()->withErrors([
            'error' => 'Class is full. Cannot enroll more students.'
        ]);
    }

    // Check if class is open
    if ($class->status !== 'open') {
        return back()->withErrors([
            'error' => 'Class is not open for enrollment.'
        ]);
    }

    // Check for schedule conflicts
    $conflict = $this->checkStudentScheduleConflict($student->id, $class);
    if ($conflict) {
        return back()->withErrors([
            'error' => "Schedule conflict with {$conflict->course->name}"
        ]);
    }

    Enrollment::create([
        'student_id' => $student->id,
        'class_id' => $class->id,
        'enrollment_date' => $validated['enrollment_date'] ?? now(),
        'status' => 'enrolled',
    ]);

    return redirect()->route('admin.enrollment.index')
        ->with('success', "Successfully enrolled {$student->full_name}");
}
```

#### Student Schedule Conflict Detection

```php
private function checkStudentScheduleConflict($studentId, ClassModel $newClass)
{
    if (empty($newClass->schedule)) return null;

    $studentClasses = ClassModel::query()
        ->whereHas('enrollments', function ($query) use ($studentId) {
            $query->where('student_id', $studentId)
                  ->where('status', 'enrolled');
        })
        ->where('term_id', $newClass->term_id)
        ->where('id', '!=', $newClass->id)
        ->with('course')
        ->get();

    foreach ($studentClasses as $existingClass) {
        if (empty($existingClass->schedule)) continue;

        foreach ($newClass->schedule as $newSlot) {
            foreach ($existingClass->schedule as $existingSlot) {
                if ($newSlot['day'] === $existingSlot['day']) {
                    if ($this->timesOverlap(
                        $newSlot['start_time'], $newSlot['end_time'],
                        $existingSlot['start_time'], $existingSlot['end_time']
                    )) {
                        return $existingClass;
                    }
                }
            }
        }
    }

    return null;
}
```

#### `drop()` - Drop Student from Class

```php
public function drop(Enrollment $enrollment)
{
    $enrollment->update(['status' => 'dropped']);

    return redirect()->route('admin.enrollment.index')
        ->with('success', 'Student dropped from class successfully.');
}
```

#### `studentSchedule()` - View Student's Schedule

```php
public function studentSchedule(Student $student)
{
    $enrollments = $student->enrollments()
        ->with(['class.course', 'class.teacher', 'class.term'])
        ->enrolled()
        ->get();

    return Inertia::render('Admin/Enrollment/StudentSchedule', [
        'student' => $student,
        'enrollments' => $enrollments,
    ]);
}
```

---

## Routes

**File:** `routes/web.php`

### Resource Routes

```php
use App\Http\Controllers\Admin\{
    AcademicYearController,
    ClassController,
    CourseController,
    EnrollmentController,
    GuardianController,
    StudentController,
    TeacherController
};

Route::middleware(['auth'])->prefix('admin')->group(function () {
    // Phase 1: Student & Course Foundation

    // Student management
    Route::resource('students', StudentController::class);

    // Guardian management
    Route::resource('guardians', GuardianController::class);

    // Course management
    Route::resource('courses', CourseController::class);

    // Teacher management
    Route::resource('teachers', TeacherController::class);

    // Academic year management
    Route::resource('academic-years', AcademicYearController::class);

    // Additional Academic Year routes for term management
    Route::post('academic-years/{academicYear}/set-current', [AcademicYearController::class, 'setCurrent'])
        ->name('admin.academic-years.set-current');
    Route::post('academic-years/{academicYear}/terms', [AcademicYearController::class, 'storeTerm'])
        ->name('admin.academic-years.terms.store');
    Route::put('academic-years/{academicYear}/terms/{term}', [AcademicYearController::class, 'updateTerm'])
        ->name('admin.academic-years.terms.update');
    Route::delete('academic-years/{academicYear}/terms/{term}', [AcademicYearController::class, 'destroyTerm'])
        ->name('admin.academic-years.terms.destroy');
    Route::post('academic-years/{academicYear}/terms/{term}/set-current', [AcademicYearController::class, 'setCurrentTerm'])
        ->name('admin.academic-years.terms.set-current');

    // Phase 2: Class & Enrollment Management

    // Class management
    Route::resource('classes', ClassController::class);

    // Enrollment management
    Route::prefix('enrollment')->group(function () {
        Route::get('/', [EnrollmentController::class, 'index'])->name('admin.enrollment.index');
        Route::get('/create', [EnrollmentController::class, 'create'])->name('admin.enrollment.create');
        Route::post('/enroll', [EnrollmentController::class, 'enroll'])->name('admin.enrollment.enroll');
        Route::delete('/{enrollment}', [EnrollmentController::class, 'drop'])->name('admin.enrollment.drop');
        Route::get('/student/{student}/schedule', [EnrollmentController::class, 'studentSchedule'])
            ->name('admin.enrollment.student-schedule');
    });
});
```

### API Routes

**File:** `routes/api.php`

```php
Route::get('/theme', [ThemeController::class, 'show']);
```

### Generated Route Names

Resource routes generate the following named routes:

```
admin.students.index   - GET    /admin/students
admin.students.create  - GET    /admin/students/create
admin.students.store   - POST   /admin/students
admin.students.show    - GET    /admin/students/{student}
admin.students.edit    - GET    /admin/students/{student}/edit
admin.students.update  - PUT    /admin/students/{student}
admin.students.destroy - DELETE /admin/students/{student}
```

---

## Middleware

### HandleInertiaRequests

**File:** `app/Http/Middleware/HandleInertiaRequests.php`

**Purpose:** Share data with all Inertia views.

```php
public function share(Request $request): array
{
    return [
        ...parent::share($request),
        'auth' => [
            'user' => $request->user(),
        ],
        'flash' => [
            'success' => fn () => $request->session()->get('success'),
            'error' => fn () => $request->session()->get('error'),
        ],
    ];
}
```

**Shared Data:**
- `auth.user` - Current authenticated user
- `flash.success` - Success flash message
- `flash.error` - Error flash message

---

## Validation

### Inline Validation

All controllers currently use inline validation with `$request->validate()`.

**Example:**

```php
$validated = $request->validate([
    'first_name' => ['required', 'string', 'max:255'],
    'email' => ['nullable', 'email', 'unique:students,email'],
    'date_of_birth' => ['required', 'date', 'before:today'],
]);
```

### Form Request Classes (Future)

For complex validation, create FormRequest classes:

```php
// app/Http/Requests/StoreStudentRequest.php
class StoreStudentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            // ... all validation rules
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Please enter the student\'s first name.',
            // ... custom messages
        ];
    }
}

// Controller usage
public function store(StoreStudentRequest $request)
{
    $validated = $request->validated();
    // ...
}
```

---

## Services

### Service Layer Pattern (Future)

For complex business logic, create service classes:

```php
// app/Services/EnrollmentService.php
class EnrollmentService
{
    public function enrollStudent(Student $student, Class $class): Enrollment
    {
        // Check class capacity
        if ($class->enrollments()->count() >= $class->max_students) {
            throw new \Exception('Class is full');
        }

        // Check schedule conflicts
        if ($this->hasScheduleConflict($student, $class)) {
            throw new \Exception('Schedule conflict detected');
        }

        // Create enrollment
        return Enrollment::create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'enrollment_date' => now(),
            'status' => 'enrolled',
        ]);
    }

    private function hasScheduleConflict(Student $student, Class $class): bool
    {
        // Check if student's existing classes conflict with new class
        // ...
    }
}

// Controller usage
public function enroll(Request $request, EnrollmentService $enrollmentService)
{
    $student = Student::findOrFail($request->student_id);
    $class = Class::findOrFail($request->class_id);

    $enrollment = $enrollmentService->enrollStudent($student, $class);

    return redirect()->back()->with('success', 'Student enrolled successfully.');
}
```

---

## Events & Listeners

### Event-Driven Architecture (Future)

For decoupled notifications and logging:

```php
// app/Events/StudentEnrolled.php
class StudentEnrolled
{
    public function __construct(
        public Enrollment $enrollment
    ) {}
}

// app/Listeners/SendEnrollmentNotification.php
class SendEnrollmentNotification
{
    public function handle(StudentEnrolled $event): void
    {
        // Send notification to student
        // Send notification to guardians
    }
}

// Controller
use App\Events\StudentEnrolled;

$enrollment = Enrollment::create([...]);
event(new StudentEnrolled($enrollment));
```

---

## File Uploads

### Storage Configuration

**File:** `config/filesystems.php`

```php
'disks' => [
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
    ],
],
```

### Upload Handling

```php
// Upload file
if ($request->hasFile('photo')) {
    $path = $request->file('photo')->store('students/photos', 'public');
    // $path = 'students/photos/abc123.jpg'
}

// Delete file
if ($student->photo) {
    Storage::disk('public')->delete($student->photo);
}

// Get file URL
$url = Storage::disk('public')->url($student->photo);
// $url = 'http://localhost:8000/storage/students/photos/abc123.jpg'
```

### Storage Link

Create symbolic link for public access:

```bash
php artisan storage:link
```

This creates a symlink from `public/storage` to `storage/app/public`.

---

## Authentication & Authorization

### Current Authentication

- Uses Laravel Breeze for authentication scaffolding
- Session-based authentication
- Routes protected with `auth` middleware

### Role-Based Access Control (Future)

```php
// Add role column to users table
Schema::table('users', function (Blueprint $table) {
    $table->enum('role', ['admin', 'teacher', 'student', 'parent'])->default('student');
});

// Create middleware
// app/Http/Middleware/AdminMiddleware.php
class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }
        return $next($request);
    }
}

// Apply middleware
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    // Admin routes
});
```

---

## Testing

### Feature Tests

**Example:** `tests/Feature/StudentTest.php`

```php
class StudentTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_student(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.students.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'date_of_birth' => '2008-05-15',
            'gender' => 'male',
            'enrollment_date' => now()->format('Y-m-d'),
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('students', [
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
    }

    public function test_student_id_auto_generated(): void
    {
        $student = Student::factory()->create();

        $this->assertMatchesRegularExpression('/^STU-\d{4}-\d{3}$/', $student->student_id);
    }
}
```

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter StudentTest

# Run with coverage
php artisan test --coverage
```

---

## Best Practices

### 1. Always Use Eager Loading

```php
// BAD - N+1 queries
$students = Student::all();
foreach ($students as $student) {
    echo $student->guardians->count(); // Separate query
}

// GOOD - Eager loading
$students = Student::with('guardians')->get();
foreach ($students as $student) {
    echo $student->guardians->count(); // No additional query
}
```

### 2. Use Query Scopes

```php
// BAD - Repeated logic
$students = Student::where('status', 'active')->get();

// GOOD - Reusable scope
$students = Student::active()->get();
```

### 3. Use Route Model Binding

```php
// BAD - Manual lookup
public function show($id)
{
    $student = Student::findOrFail($id);
}

// GOOD - Route model binding
public function show(Student $student)
{
    // $student automatically loaded
}
```

### 4. Validate Input

```php
// ALWAYS validate user input
$validated = $request->validate([...]);
Student::create($validated);
```

### 5. Use Transactions for Multi-Step Operations

```php
use Illuminate\Support\Facades\DB;

DB::transaction(function () use ($request) {
    $student = Student::create($request->validated());
    $student->guardians()->attach($request->guardian_ids);
});
```

---

**© 2026 Brian K. Rich. All rights reserved.**
