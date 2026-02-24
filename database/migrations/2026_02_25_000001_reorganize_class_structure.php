<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Drop dependent tables (child → parent) ────────────────────────────
        Schema::dropIfExists('grades');
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('assessments');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('terms');

        // ── Create educational_institutions ───────────────────────────────────
        Schema::create('educational_institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['technical_college', 'university']);
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('contact_person')->nullable();
            $table->timestamps();
        });

        // ── Recreate classes (redesigned) ─────────────────────────────────────
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')
                  ->constrained('academic_years')
                  ->restrictOnDelete();
            $table->string('class_number');
            $table->string('ngb_number')->unique();
            $table->enum('status', ['forming', 'active', 'completed'])->default('forming');
            $table->timestamps();

            $table->index('academic_year_id');
        });

        // ── Create cohorts ────────────────────────────────────────────────────
        Schema::create('cohorts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')
                  ->constrained('classes')
                  ->cascadeOnDelete();
            $table->enum('name', ['alpha', 'bravo']);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();

            $table->unique(['class_id', 'name']);
        });

        // ── Create cohort_courses ─────────────────────────────────────────────
        Schema::create('cohort_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cohort_id')
                  ->constrained('cohorts')
                  ->restrictOnDelete();
            $table->foreignId('course_id')
                  ->constrained('courses')
                  ->restrictOnDelete();
            $table->enum('instructor_type', ['staff', 'technical_college', 'university']);
            $table->foreignId('employee_id')
                  ->nullable()
                  ->constrained('employees')
                  ->nullOnDelete();
            $table->foreignId('institution_id')
                  ->nullable()
                  ->constrained('educational_institutions')
                  ->nullOnDelete();
            $table->string('room')->nullable();
            $table->json('schedule')->nullable();
            $table->integer('max_students')->default(30);
            $table->enum('status', ['open', 'closed', 'in_progress', 'completed'])->default('open');
            $table->timestamps();

            $table->index(['cohort_id', 'course_id']);
        });

        // ── Recreate enrollments ──────────────────────────────────────────────
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                  ->constrained('students')
                  ->cascadeOnDelete();
            $table->foreignId('cohort_course_id')
                  ->constrained('cohort_courses')
                  ->cascadeOnDelete();
            $table->date('enrollment_date');
            $table->enum('status', ['enrolled', 'dropped', 'completed', 'failed'])->default('enrolled');
            $table->decimal('weighted_average', 5, 2)->nullable();
            $table->string('final_letter_grade')->nullable();
            $table->decimal('grade_points', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'cohort_course_id']);
        });

        // ── Recreate assessments ──────────────────────────────────────────────
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cohort_course_id')
                  ->constrained('cohort_courses')
                  ->cascadeOnDelete();
            $table->foreignId('assessment_category_id')
                  ->constrained('assessment_categories');
            $table->string('name');
            $table->decimal('max_score', 8, 2);
            $table->date('due_date')->nullable();
            $table->decimal('weight', 5, 4)->nullable();
            $table->boolean('is_extra_credit')->default(false);
            $table->enum('status', ['draft', 'published'])->default('published');
            $table->timestamps();
        });

        // ── Recreate attendance_records ───────────────────────────────────────
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                  ->constrained('students')
                  ->cascadeOnDelete();
            $table->foreignId('cohort_course_id')
                  ->constrained('cohort_courses')
                  ->cascadeOnDelete();
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'late', 'excused']);
            $table->text('notes')->nullable();
            $table->foreignId('marked_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamps();
        });

        // ── Recreate grades ───────────────────────────────────────────────────
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')
                  ->constrained('enrollments')
                  ->cascadeOnDelete();
            $table->foreignId('assessment_id')
                  ->constrained('assessments')
                  ->cascadeOnDelete();
            $table->decimal('score', 8, 2);
            $table->decimal('late_penalty', 5, 2)->nullable();
            $table->boolean('is_late')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('graded_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('graded_at')->nullable();
            $table->timestamps();

            $table->unique(['enrollment_id', 'assessment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('assessments');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('cohort_courses');
        Schema::dropIfExists('cohorts');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('educational_institutions');
    }
};
