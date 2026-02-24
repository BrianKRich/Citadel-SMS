<?php

namespace App\Providers;

use App\Models\Employee;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Student;
use App\Models\StudentNote;
use App\Observers\EmployeeObserver;
use App\Observers\EnrollmentObserver;
use App\Observers\GradeObserver;
use App\Observers\StudentNoteObserver;
use App\Observers\StudentObserver;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Student::observe(StudentObserver::class);
        Employee::observe(EmployeeObserver::class);
        Grade::observe(GradeObserver::class);
        Enrollment::observe(EnrollmentObserver::class);
        StudentNote::observe(StudentNoteObserver::class);
    }
}
