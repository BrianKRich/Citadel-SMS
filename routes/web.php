<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\GuardianController;
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
});

// Public route to get current theme
Route::get('/api/theme', [ThemeController::class, 'getTheme']);

require __DIR__.'/auth.php';
