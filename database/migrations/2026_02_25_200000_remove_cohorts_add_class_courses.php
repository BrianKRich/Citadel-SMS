<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add class_id (nullable) to cohort_courses
        DB::statement('ALTER TABLE cohort_courses ADD COLUMN class_id BIGINT NULL');

        // 2. Populate class_id from cohorts.class_id
        DB::statement('UPDATE cohort_courses cc SET class_id = (SELECT c.class_id FROM cohorts c WHERE c.id = cc.cohort_id)');

        // 3. Make class_id NOT NULL, drop cohort_id FK and column
        DB::statement('ALTER TABLE cohort_courses ALTER COLUMN class_id SET NOT NULL');
        DB::statement('ALTER TABLE cohort_courses DROP CONSTRAINT cohort_courses_cohort_id_foreign');
        DB::statement('ALTER TABLE cohort_courses DROP COLUMN cohort_id');

        // 4. Drop old index and add new one
        DB::statement('DROP INDEX IF EXISTS cohort_courses_cohort_id_course_id_index');
        DB::statement('CREATE INDEX class_courses_class_id_course_id_index ON cohort_courses (class_id, course_id)');

        // 5. Rename table cohort_courses -> class_courses
        DB::statement('ALTER TABLE cohort_courses RENAME TO class_courses');

        // 6. Rename cohort_course_id -> class_course_id in enrollments
        DB::statement('ALTER TABLE enrollments DROP CONSTRAINT enrollments_student_id_cohort_course_id_unique');
        DB::statement('ALTER TABLE enrollments DROP CONSTRAINT enrollments_cohort_course_id_foreign');
        DB::statement('ALTER TABLE enrollments RENAME COLUMN cohort_course_id TO class_course_id');
        DB::statement('ALTER TABLE enrollments ADD CONSTRAINT enrollments_class_course_id_foreign FOREIGN KEY (class_course_id) REFERENCES class_courses(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE enrollments ADD CONSTRAINT enrollments_student_id_class_course_id_unique UNIQUE (student_id, class_course_id)');

        // 7. Rename cohort_course_id -> class_course_id in assessments
        DB::statement('ALTER TABLE assessments DROP CONSTRAINT assessments_cohort_course_id_foreign');
        DB::statement('ALTER TABLE assessments RENAME COLUMN cohort_course_id TO class_course_id');
        DB::statement('ALTER TABLE assessments ADD CONSTRAINT assessments_class_course_id_foreign FOREIGN KEY (class_course_id) REFERENCES class_courses(id) ON DELETE CASCADE');

        // 8. Rename cohort_course_id -> class_course_id in attendance_records
        DB::statement('ALTER TABLE attendance_records DROP CONSTRAINT attendance_records_cohort_course_id_foreign');
        DB::statement('ALTER TABLE attendance_records RENAME COLUMN cohort_course_id TO class_course_id');
        DB::statement('ALTER TABLE attendance_records ADD CONSTRAINT attendance_records_class_course_id_foreign FOREIGN KEY (class_course_id) REFERENCES class_courses(id) ON DELETE CASCADE');

        // 9. Drop cohorts table (no more children)
        Schema::dropIfExists('cohorts');
    }

    public function down(): void
    {
        // This migration is intentionally not reversible.
        throw new \RuntimeException('This migration cannot be reversed.');
    }
};
