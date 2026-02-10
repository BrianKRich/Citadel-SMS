# Citadel SMS - Backend Architecture

**Version:** 1.0
**Last Updated:** February 2026
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

Citadel SMS backend is built with Laravel 12, following modern Laravel conventions and best practices. The architecture emphasizes:

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
│   │   │   ├── StudentController.php
│   │   │   ├── GuardianController.php
│   │   │   ├── CourseController.php
│   │   │   ├── TeacherController.php
│   │   │   └── AcademicYearController.php
│   │   ├── AdminController.php
│   │   ├── ProfileController.php
│   │   └── ThemeController.php
│   ├── Middleware/
│   │   ├── HandleInertiaRequests.php
│   │   └── (custom middleware - future)
│   └── Requests/
│       └── (form requests - future)
├── Models/
│   ├── Student.php
│   ├── Guardian.php
│   ├── Course.php
│   ├── Teacher.php
│   ├── AcademicYear.php
│   ├── Term.php
│   ├── Setting.php
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

## Routes

**File:** `routes/web.php`

### Resource Routes

```php
use App\Http\Controllers\Admin\{
    StudentController,
    GuardianController,
    CourseController,
    TeacherController,
    AcademicYearController
};

Route::middleware(['auth'])->prefix('admin')->group(function () {
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
    Route::post('academic-years/{academicYear}/terms', [AcademicYearController::class, 'storeTerm'])
        ->name('admin.academic-years.terms.store');
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
