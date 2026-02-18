<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\AssessmentCategoryController;
use App\Http\Controllers\Admin\AssessmentController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\GradeController;
use App\Http\Controllers\Admin\GradingScaleController;
use App\Http\Controllers\Admin\GuardianController;
use App\Http\Controllers\Admin\ReportCardController;
use App\Http\Controllers\Admin\TranscriptController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin routes
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/theme', [ThemeController::class, 'index'])->name('admin.theme');
    Route::post('/admin/theme', [ThemeController::class, 'update'])->name('admin.theme.update');

    // User management routes
    Route::resource('admin/users', UserManagementController::class)->names([
        'index' => 'admin.users.index',
        'create' => 'admin.users.create',
        'store' => 'admin.users.store',
        'edit' => 'admin.users.edit',
        'update' => 'admin.users.update',
        'destroy' => 'admin.users.destroy',
    ]);

    // Phase 1: Student & Course Management
    // Student trash management (must precede resource route to avoid {student} binding)
    Route::get('admin/students/trashed', [StudentController::class, 'trashed'])
        ->name('admin.students.trashed');
    Route::post('admin/students/{student}/restore', [StudentController::class, 'restore'])
        ->name('admin.students.restore')
        ->withTrashed();
    Route::delete('admin/students/{student}/force-delete', [StudentController::class, 'forceDelete'])
        ->name('admin.students.force-delete')
        ->withTrashed();

    Route::resource('admin/students', StudentController::class)->names([
        'index' => 'admin.students.index',
        'create' => 'admin.students.create',
        'store' => 'admin.students.store',
        'show' => 'admin.students.show',
        'edit' => 'admin.students.edit',
        'update' => 'admin.students.update',
        'destroy' => 'admin.students.destroy',
    ]);

    Route::resource('admin/guardians', GuardianController::class)->names([
        'index' => 'admin.guardians.index',
        'create' => 'admin.guardians.create',
        'store' => 'admin.guardians.store',
        'show' => 'admin.guardians.show',
        'edit' => 'admin.guardians.edit',
        'update' => 'admin.guardians.update',
        'destroy' => 'admin.guardians.destroy',
    ]);

    Route::resource('admin/courses', CourseController::class)->names([
        'index' => 'admin.courses.index',
        'create' => 'admin.courses.create',
        'store' => 'admin.courses.store',
        'show' => 'admin.courses.show',
        'edit' => 'admin.courses.edit',
        'update' => 'admin.courses.update',
        'destroy' => 'admin.courses.destroy',
    ]);

    // Employee trash management (must precede resource route to avoid {employee} binding)
    Route::get('admin/employees/trashed', [EmployeeController::class, 'trashed'])
        ->name('admin.employees.trashed');
    Route::post('admin/employees/{employee}/restore', [EmployeeController::class, 'restore'])
        ->name('admin.employees.restore')
        ->withTrashed();
    Route::delete('admin/employees/{employee}/force-delete', [EmployeeController::class, 'forceDelete'])
        ->name('admin.employees.force-delete')
        ->withTrashed();

    Route::resource('admin/employees', EmployeeController::class)->names([
        'index' => 'admin.employees.index',
        'create' => 'admin.employees.create',
        'store' => 'admin.employees.store',
        'show' => 'admin.employees.show',
        'edit' => 'admin.employees.edit',
        'update' => 'admin.employees.update',
        'destroy' => 'admin.employees.destroy',
    ]);

    Route::resource('admin/academic-years', AcademicYearController::class)->names([
        'index' => 'admin.academic-years.index',
        'create' => 'admin.academic-years.create',
        'store' => 'admin.academic-years.store',
        'show' => 'admin.academic-years.show',
        'edit' => 'admin.academic-years.edit',
        'update' => 'admin.academic-years.update',
        'destroy' => 'admin.academic-years.destroy',
    ]);

    // Additional Academic Year routes for term management
    Route::post('admin/academic-years/{academicYear}/set-current', [AcademicYearController::class, 'setCurrent'])
        ->name('admin.academic-years.set-current');
    Route::post('admin/academic-years/{academicYear}/terms', [AcademicYearController::class, 'storeTerm'])
        ->name('admin.academic-years.terms.store');
    Route::put('admin/academic-years/{academicYear}/terms/{term}', [AcademicYearController::class, 'updateTerm'])
        ->name('admin.academic-years.terms.update');
    Route::delete('admin/academic-years/{academicYear}/terms/{term}', [AcademicYearController::class, 'destroyTerm'])
        ->name('admin.academic-years.terms.destroy');
    Route::post('admin/academic-years/{academicYear}/terms/{term}/set-current', [AcademicYearController::class, 'setCurrentTerm'])
        ->name('admin.academic-years.terms.set-current');

    // Phase 2: Class & Enrollment Management
    Route::resource('admin/classes', ClassController::class)->names([
        'index' => 'admin.classes.index',
        'create' => 'admin.classes.create',
        'store' => 'admin.classes.store',
        'show' => 'admin.classes.show',
        'edit' => 'admin.classes.edit',
        'update' => 'admin.classes.update',
        'destroy' => 'admin.classes.destroy',
    ]);

    // Enrollment routes
    Route::prefix('admin/enrollment')->group(function () {
        Route::get('/', [EnrollmentController::class, 'index'])->name('admin.enrollment.index');
        Route::get('/create', [EnrollmentController::class, 'create'])->name('admin.enrollment.create');
        Route::post('/enroll', [EnrollmentController::class, 'enroll'])->name('admin.enrollment.enroll');
        Route::delete('/{enrollment}', [EnrollmentController::class, 'drop'])->name('admin.enrollment.drop');
        Route::get('/student/{student}/schedule', [EnrollmentController::class, 'studentSchedule'])->name('admin.enrollment.student-schedule');
    });

    // Phase 3: Grading & Assessments
    Route::resource('admin/assessment-categories', AssessmentCategoryController::class)->names('admin.assessment-categories');
    Route::resource('admin/assessments', AssessmentController::class)->names('admin.assessments');
    Route::resource('admin/grading-scales', GradingScaleController::class)->names('admin.grading-scales');
    Route::post('admin/grading-scales/{gradingScale}/set-default', [GradingScaleController::class, 'setDefault'])
        ->name('admin.grading-scales.set-default');

    Route::prefix('admin/grades')->group(function () {
        Route::get('/', [GradeController::class, 'index'])->name('admin.grades.index');
        Route::get('/class/{classModel}', [GradeController::class, 'classGrades'])->name('admin.grades.class');
        Route::get('/enter/{assessment}', [GradeController::class, 'enter'])->name('admin.grades.enter');
        Route::post('/store', [GradeController::class, 'store'])->name('admin.grades.store');
        Route::get('/student/{student}', [GradeController::class, 'studentGrades'])->name('admin.grades.student');
    });

    // Phase 3B: Report Cards
    Route::prefix('admin/report-cards')->group(function () {
        Route::get('/', [ReportCardController::class, 'index'])->name('admin.report-cards.index');
        Route::get('/{student}', [ReportCardController::class, 'show'])->name('admin.report-cards.show');
        Route::get('/{student}/pdf', [ReportCardController::class, 'pdf'])->name('admin.report-cards.pdf');
    });

    // Phase 3B: Transcripts
    Route::prefix('admin/transcripts')->group(function () {
        Route::get('/', [TranscriptController::class, 'index'])->name('admin.transcripts.index');
        Route::get('/{student}', [TranscriptController::class, 'show'])->name('admin.transcripts.show');
        Route::get('/{student}/pdf', [TranscriptController::class, 'pdf'])->name('admin.transcripts.pdf');
    });

    // Feature settings toggle
    Route::post('/admin/feature-settings', [AdminController::class, 'updateFeatureSettings'])
        ->name('admin.feature-settings.update');

    // Phase 4: Attendance (student route BEFORE classModel wildcard)
    Route::get('admin/attendance/student/{student}', [AttendanceController::class, 'studentHistory'])
        ->name('admin.attendance.student');

    Route::prefix('admin/attendance')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('admin.attendance.index');
        Route::get('/{classModel}/take', [AttendanceController::class, 'take'])->name('admin.attendance.take');
        Route::post('/store', [AttendanceController::class, 'store'])->name('admin.attendance.store');
        Route::get('/{classModel}/summary', [AttendanceController::class, 'classSummary'])->name('admin.attendance.summary');
    });
});

// Public route to get current theme
Route::get('/api/theme', [ThemeController::class, 'getTheme']);

require __DIR__.'/auth.php';
