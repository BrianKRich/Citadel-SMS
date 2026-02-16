# SMS Project Plan — Phase 3: Grading & Assessments

## Context

Phases 0-2 complete. Phase 3 implements grading & assessments per GitHub Issue #6. The `Enrollment` model already has `final_grade`, `grade_points` fields and a stub `grades()` hasMany.

**Scope note:** No payment/fee management (per original roadmap).

**Roadmap:** Phase 3 (Issue #6) → 4: Attendance (#5) → 5: Guardian Portal (#4) → 6: Calendar (#3) → 7: Documents (#2) → 8: Reporting (#1)

**Conventions:** Backend before frontend. PHPUnit with PostgreSQL (`sms_testing` database). `RefreshDatabase`, `test_snake_case`, `actingAs()`. `assertInertia()` for page responses.

---

## Execution Progress

**Status:** 3A complete. 66 tests passing (228 assertions). Next: 3B.

### Completed
- [x] **3A Step 1 — Factories (13 of 13):** All model factories created and verified.
- [x] **3A Step 1 — HasFactory trait:** Added to `ClassModel`, `Department`, `EmployeeRole`.
- [x] **3A Step 1 — Migrations:** 4 tables created (`grading_scales`, `assessment_categories`, `assessments`, `grades`).
- [x] **3A Step 1 — New Models:** `GradingScale`, `AssessmentCategory`, `Assessment`, `Grade` with scopes, relationships, accessors.
- [x] **3A Step 1 — Updated Models:** `ClassModel` → `assessments()`, `Course` → `assessmentCategories()`, `Enrollment` → `calculateFinalGrade()`.
- [x] **3A Step 2 — GradeCalculationService:** All 5 methods implemented.
- [x] **3A Step 3 — Routes & Controllers:** All Phase 3 routes + 4 controllers (AssessmentCategory, Assessment, Grade, GradingScale).
- [x] **3A Step 4 — Vue Pages:** 14 pages across 4 directories. Frontend builds cleanly.
- [x] **3A Step 5 — Seeders & Dashboard:** 4 seeders + DatabaseSeeder updated + dashboard grade stats + Grade Management action card.
- [x] **3A Step 6 — Tests:** 2 unit + 4 feature test files (41 new tests). All 66 tests passing.
- [x] **Test infrastructure:** Fixed `phpunit.xml` — PostgreSQL `sms_testing` database. Updated CLAUDE.md.

### Next Up
- [ ] **3B** — Report Cards, Transcripts & Class Rank
- [ ] **3C** — CSV/Excel Grade Import & Export

---

## Sub-Phases

- **3A** — Core: factories, schema, models, service, controllers, pages, seeders, dashboard, tests
- **3B** — PDF: DomPDF, report cards, transcripts, class rank, tests
- **3C** — Import/Export: Laravel Excel, CSV import, Excel export, tests

---

## 3A: Core Grading System

### Step 1: Database & Models

**Factories** (prerequisite for testing — no factories exist beyond `UserFactory`):

Create 13 factories with auto-wired FK dependencies (`Grade::factory()->create()` builds full chain):
- Pre-existing models: `AcademicYear`, `Term`, `Course`, `Department`, `EmployeeRole`, `Employee`, `ClassModel`, `Student`, `Enrollment`
- New models: `AssessmentCategory`, `Assessment`, `Grade`, `GradingScale`
- Add `HasFactory` trait to: `ClassModel`, `Department`, `EmployeeRole`

**4 Migrations:**

| Table | Key Columns |
|-------|-------------|
| `assessment_categories` | `course_id` (FK nullable, null=global default), `name`, `weight` (decimal 5,2), `description` (nullable) |
| `assessments` | `class_id` (FK), `assessment_category_id` (FK), `name`, `max_score` (decimal 8,2), `due_date` (nullable), `weight` (decimal 5,2 nullable, overrides category), `is_extra_credit` (bool), `status` (enum: draft/published). Index on `class_id` |
| `grades` | `enrollment_id` (FK), `assessment_id` (FK), `score` (decimal 8,2), `notes` (nullable), `is_late` (bool), `late_penalty` (decimal 5,2 nullable), `graded_by` (FK→users), `graded_at` (nullable). UNIQUE on `(enrollment_id, assessment_id)` |
| `grading_scales` | `name`, `is_default` (bool), `scale` (json: `[{letter, min_percentage, gpa_points}]`) |

**4 New Models:**

| Model | Key Details |
|-------|-------------|
| `AssessmentCategory` | Scopes: `search()`, `course()`, `global()`. Relationships: `course()`, `assessments()` |
| `Assessment` | Scopes: `class()`, `category()`, `published()`, `draft()`, `search()`. Accessor: `effective_weight` (own weight ?? category weight) |
| `Grade` | Relationships: `enrollment()`, `assessment()`, `gradedBy()`. Accessor: `adjusted_score` (applies late penalty) |
| `GradingScale` | Methods: `getLetterGrade($pct)`, `getGpaPoints($letter)`, `setDefault()`. Scope: `default()` |

**Update existing models:**
- `ClassModel` → add `assessments()` hasMany
- `Course` → add `assessmentCategories()` hasMany
- `Enrollment` → implement `calculateFinalGrade()` delegating to service

### Step 2: GradeCalculationService

`app/Services/GradeCalculationService.php` — all grade math, single source of truth:

- `calculateWeightedAverage(Enrollment): float` — by category, extra credit additive
- `calculateTermGpa(Student, Term): float` — GPA across term enrollments
- `calculateCumulativeGpa(Student): float` — GPA across all terms (reused by report cards + transcripts)
- `updateEnrollmentGrade(Enrollment): void` — average → GradingScale lookup → update `final_grade`/`grade_points`
- `updateAllClassGrades(ClassModel): void` — batch update all enrollments

`calculateClassRank()` deferred to 3B.

### Step 3: Routes & Controllers

**All Phase 3 routes** added to `routes/web.php` at once:

```php
Route::resource('admin/assessment-categories', AssessmentCategoryController::class)->names('admin.assessment-categories');
Route::resource('admin/assessments', AssessmentController::class)->names('admin.assessments');
Route::resource('admin/grading-scales', GradingScaleController::class)->names('admin.grading-scales');
Route::post('admin/grading-scales/{gradingScale}/set-default', [GradingScaleController::class, 'setDefault'])->name('admin.grading-scales.set-default');

Route::prefix('admin/grades')->group(function () {
    Route::get('/', [GradeController::class, 'index'])->name('admin.grades.index');
    Route::get('/class/{classModel}', [GradeController::class, 'classGrades'])->name('admin.grades.class');
    Route::get('/enter/{assessment}', [GradeController::class, 'enter'])->name('admin.grades.enter');
    Route::post('/store', [GradeController::class, 'store'])->name('admin.grades.store');
    Route::get('/student/{student}', [GradeController::class, 'studentGrades'])->name('admin.grades.student');
    Route::get('/import/{classModel}', [GradeController::class, 'importForm'])->name('admin.grades.import');
    Route::post('/import/{classModel}', [GradeController::class, 'import'])->name('admin.grades.import.store');
    Route::get('/export/{classModel}', [GradeController::class, 'export'])->name('admin.grades.export');
});

Route::prefix('admin/report-cards')->group(function () {  // 3B
    Route::get('/', [ReportCardController::class, 'index'])->name('admin.report-cards.index');
    Route::get('/{student}/{term}', [ReportCardController::class, 'show'])->name('admin.report-cards.show');
    Route::get('/{student}/{term}/download', [ReportCardController::class, 'download'])->name('admin.report-cards.download');
    Route::get('/batch/{term}', [ReportCardController::class, 'batchDownload'])->name('admin.report-cards.batch');
});

Route::prefix('admin/transcripts')->group(function () {  // 3B
    Route::get('/', [TranscriptController::class, 'index'])->name('admin.transcripts.index');
    Route::get('/{student}', [TranscriptController::class, 'show'])->name('admin.transcripts.show');
    Route::get('/{student}/download', [TranscriptController::class, 'download'])->name('admin.transcripts.download');
});
```

**4 Controllers (3A scope):**

| Controller | Actions |
|------------|---------|
| `AssessmentCategoryController` | Resource CRUD. Filter by course. Validate weight 0-1. Block destroy if linked assessments exist. |
| `AssessmentController` | Resource CRUD. Filter by class/category/status. Show includes grade stats (avg/min/max). |
| `GradeController` | `index` (filters), `classGrades` (matrix), `enter` (bulk form), `store` (bulk save + recalc), `studentGrades` (per-student) |
| `GradingScaleController` | Resource CRUD + `setDefault()` |

### Step 4: Vue Pages

| Directory | Pages |
|-----------|-------|
| `AssessmentCategories/` | Index, Create, Edit |
| `Assessments/` | Index, Create, Edit, Show |
| `GradingScales/` | Index, Create, Edit |
| `Grades/` | Index (filters), ClassGrades (matrix with weights/averages/letters), Enter (bulk: names, scores, late checkbox, notes), StudentGrades (per-class + cumulative GPA) |

### Step 5: Seeders & Dashboard

**Seeders:**
- `GradingScaleSeeder` — A=90/4.0, B=80/3.0, C=70/2.0, D=60/1.0, F=0/0.0
- `AssessmentCategorySeeder` — Homework (.15), Quizzes (.15), Midterm (.25), Final (.30), Projects (.15)
- `AssessmentSeeder` — sample assessments for seeded classes
- `GradeSeeder` — sample grades for seeded enrollments
- Update `DatabaseSeeder.php`

**Dashboard:**
- `AdminController::dashboard()` — add total assessments, grades this week, average GPA
- `Dashboard.vue` — add `AdminActionCard` for "Grade Management"

### Step 6: Tests

**Unit:**

| File | Tests |
|------|-------|
| `tests/Unit/Models/GradingScaleTest.php` | Letter grade boundaries (90→A, 89→B, 70→C, 60→D, 59→F), GPA point mapping |
| `tests/Unit/Services/GradeCalculationServiceTest.php` | Weighted average (single/multiple categories), extra credit, late penalty, update enrollment, term GPA, cumulative GPA |

**Feature:**

| File | Tests |
|------|-------|
| `tests/Feature/Admin/AssessmentCategoryTest.php` | Index, auth redirect, store (valid + validation), update, destroy (empty + linked) |
| `tests/Feature/Admin/AssessmentTest.php` | Index (+ filters by class/status), store (valid + validation), show with grades, update, destroy |
| `tests/Feature/Admin/GradeTest.php` | Class matrix, bulk entry form, bulk store (+ score validation + recalculation), student grades, unique constraint |
| `tests/Feature/Admin/GradingScaleTest.php` | Index, store (+ JSON validation), set default unsets others, prevent deleting default |

### 3A Verification

1. `php artisan migrate` — 4 tables created
2. `php artisan db:seed` — seed data populates
3. `php artisan test` — all tests pass
4. Manual: categories → assessments → enter grades → verify calculation → dashboard

---

## 3B: Report Cards, Transcripts & Class Rank

### Step 1: Foundation, Class Rank & DomPDF

`composer require barryvdh/laravel-dompdf`

**Data-mapping fix** (discovered during 3B planning): `ClassGrades.vue` references `enrollment.weighted_average` and `enrollment.final_letter_grade` but neither exists on the Enrollment model/DB.

**New migration** — `update_enrollments_for_grade_display`:
- Add `weighted_average decimal(5,2) nullable` to `enrollments`
- Rename `final_grade` → `final_letter_grade` (fixes root cause — no accessor/appends overhead)

**Modify `app/Models/Enrollment.php`:**
- Update `$fillable`: replace `final_grade` with `final_letter_grade`, add `weighted_average`
- Update `$casts`: add `weighted_average` as `decimal:2`
- No accessor or `$appends` needed (column rename handles it)

**Modify `app/Services/GradeCalculationService.php`:**
- `updateEnrollmentGrade()` — store `weighted_average`, update `final_letter_grade` (renamed from `final_grade`)
- Add `calculateClassRank(ClassModel): Collection` — queries enrolled enrollments with `weighted_average`, sorts descending, standard ranking (ties share rank, next skips: 1,1,3). Returns `[enrollment_id, student_id, weighted_average, rank]`

**Update any references** to `final_grade` across existing code (controllers, seeders, factories, tests, Vue pages) to use `final_letter_grade`.

### Step 2: Blade PDF Templates

**Create `resources/views/pdf/report-card.blade.php`:**
- Inline CSS only (DomPDF limitation), DejaVu Sans font
- Header: school name, "Report Card", student name/ID, term
- Table: Course | Section | Teacher | Avg % | Grade | GPA | Credits
- Footer: Term GPA, Cumulative GPA, date

**Create `resources/views/pdf/transcript.blade.php`:**
- Official/unofficial conditional header + watermark (CSS `position: fixed; opacity: 0.1`)
- Per-term sections with course tables
- Cumulative GPA, total credits, signature line if official

### Step 3: Services

**Create `app/Services/ReportCardService.php`** (injects `GradeCalculationService`):
- `generateForStudent(Student, Term): array` — enrollment grades, term GPA, cumulative GPA
- `generatePdf(Student, Term): string` — DomPDF render via `pdf.report-card` Blade
- `generateBatchPdf(Term): string` — single Blade view with `@foreach` + `page-break-after: always` per student

**Create `app/Services/TranscriptService.php`** (injects `GradeCalculationService`):
- `generateForStudent(Student): array` — all terms grouped, cumulative GPA, total credits
- `generatePdf(Student, bool $official): string` — DomPDF render via `pdf.transcript` Blade

### Step 4: Controllers & Routes

**Create `app/Http/Controllers/Admin/ReportCardController.php`** (injects `ReportCardService`):

| Action | Description |
|--------|-------------|
| `index(Request)` | Term selector dropdown + paginated students for selected term |
| `show(Student, Term)` | HTML preview via Inertia |
| `download(Student, Term)` | PDF response (`Content-Type: application/pdf`) |
| `batchDownload(Term)` | Combined PDF for all students in term |

**Create `app/Http/Controllers/Admin/TranscriptController.php`** (injects `TranscriptService`):

| Action | Description |
|--------|-------------|
| `index(Request)` | Student search, paginated |
| `show(Student)` | HTML preview via Inertia |
| `download(Student, Request)` | PDF, `?official=1` flag |

**Modify `routes/web.php`** — route ordering critical:
```php
Route::prefix('admin/report-cards')->group(function () {
    Route::get('/', ...)->name('admin.report-cards.index');
    Route::get('/batch/{term}', ...)->name('admin.report-cards.batch');        // BEFORE {student}
    Route::get('/{student}/{term}', ...)->name('admin.report-cards.show');
    Route::get('/{student}/{term}/download', ...)->name('admin.report-cards.download');
});
Route::prefix('admin/transcripts')->group(function () {
    Route::get('/', ...)->name('admin.transcripts.index');
    Route::get('/{student}/download', ...)->name('admin.transcripts.download'); // BEFORE {student}
    Route::get('/{student}', ...)->name('admin.transcripts.show');
});
```

### Step 5: Vue Pages

**Create `resources/js/Pages/Admin/ReportCards/Index.vue`:**
- Term dropdown, student table (Name, ID, Status, View/Download), batch download button

**Create `resources/js/Pages/Admin/ReportCards/Show.vue`:**
- Student info header, enrollment table, term/cumulative GPA, download button

**Create `resources/js/Pages/Admin/Transcripts/Index.vue`:**
- Student search, table with View/Download/Download Official actions

**Create `resources/js/Pages/Admin/Transcripts/Show.vue`:**
- Student info, per-term course tables, cumulative summary, download buttons

### Step 6: Update Existing Pages

**Modify `app/Http/Controllers/Admin/GradeController.php`:**
- `classGrades()` — call `calculateClassRank()`, pass `rankMap` prop
- `studentGrades()` — collect unique class IDs, batch-compute ranks per class in a single loop, build `classRanks` map (avoids N+1 by caching rank results per class)

**Modify `resources/js/Pages/Admin/Grades/ClassGrades.vue`:**
- Add `rankMap` prop, add "Rank" column after "Grade"

**Modify `resources/js/Pages/Admin/Grades/StudentGrades.vue`:**
- Add `classRanks` prop, show rank per enrollment card
- Add links to report card and transcript

**Modify `resources/js/Pages/Admin/Dashboard.vue`:**
- Add "Report Cards" and "Transcripts" action cards

### Step 7: Tests

**Unit — GradeCalculationService** (add to `tests/Unit/Services/GradeCalculationServiceTest.php`):
- `test_calculate_class_rank_orders_by_average`
- `test_calculate_class_rank_handles_tied_scores` (standard ranking: 1,1,3)
- `test_update_enrollment_grade_stores_weighted_average` — verify `weighted_average` is populated

**Unit — Enrollment model** (create `tests/Unit/Models/EnrollmentTest.php`):
- `test_final_letter_grade_is_fillable`
- `test_weighted_average_is_fillable`

**Feature — Report Cards** (`tests/Feature/Admin/ReportCardTest.php`, ~9 tests):
- Index renders, index with term lists students, show preview, download PDF (content-type), batch PDF, auth redirect
- Service integration: includes enrolled courses, calculates term GPA, batch PDF starts with `%PDF`

**Feature — Transcripts** (`tests/Feature/Admin/TranscriptTest.php`, ~11 tests):
- Index renders, search filters, show preview, download PDF, official flag, includes all terms, auth redirect
- Service integration: includes all terms, calculates cumulative GPA, official/unofficial PDFs start with `%PDF`

**Feature — Updated controllers** (add to existing `tests/Feature/Admin/GradeTest.php`):
- `test_class_grades_includes_rank_map` — verify `classGrades()` returns `rankMap` prop via `assertInertia`
- `test_student_grades_includes_class_ranks` — verify `studentGrades()` returns `classRanks` prop via `assertInertia`

### 3B Verification

1. `php artisan migrate` — weighted_average added, final_grade renamed to final_letter_grade
2. `php artisan test` — all existing 66 + ~28 new tests pass
3. ClassGrades shows Avg %, Grade, and Rank columns populated
4. Report card: select term → preview → PDF download → batch download
5. Transcript: search student → preview → official/unofficial PDF
6. `npm run build` — frontend compiles cleanly

---

## 3C: CSV/Excel Grade Import & Export

### Step 1: Implementation

`composer require maatwebsite/excel`

**Add to `GradeController`:**
- `importForm(ClassModel)` — upload form with expected format
- `import(Request, ClassModel)` — parse via Laravel Excel, validate, bulk upsert, trigger recalculation
- `export(ClassModel)` — export grade book to Excel/CSV

CSV format: `student_id, assessment_name, score, notes`

**Vue:**
- `Pages/Admin/Grades/Import.vue` — upload, preview rows, validation errors, confirm
- `ClassGrades.vue` — add export button

### Step 2: Tests

`tests/Feature/Admin/GradeImportExportTest.php`:
- Import: form displays, valid CSV creates grades, upsert updates existing, triggers recalc, rejects invalid format/student IDs/scores exceeding max
- Export: returns Excel file (content-type), contains all students and assessments

### 3C Verification

1. `php artisan test` — all pass
2. Valid CSV import → grades created
3. Invalid CSV → error messages
4. Export → opens in Excel/Sheets

---

## Files to Modify

| File | Change | Sub-Phase |
|------|--------|-----------|
| `app/Models/ClassModel.php` | Add `HasFactory`, `assessments()` | 3A |
| `app/Models/Course.php` | Add `assessmentCategories()` | 3A |
| `app/Models/Enrollment.php` | Implement `calculateFinalGrade()` | 3A |
| `app/Models/Department.php` | Add `HasFactory` | 3A |
| `app/Models/EmployeeRole.php` | Add `HasFactory` | 3A |
| `app/Http/Controllers/AdminController.php` | Grade stats on dashboard | 3A |
| `resources/js/Pages/Admin/Dashboard.vue` | Grade management action card | 3A |
| `routes/web.php` | All Phase 3 routes | 3A |
| `database/seeders/DatabaseSeeder.php` | New seeders | 3A |
| `app/Models/Enrollment.php` | Rename `final_grade` → `final_letter_grade` in fillable, add `weighted_average` to fillable/casts | 3B |
| `app/Services/GradeCalculationService.php` | Add `calculateClassRank()`, store `weighted_average`, use renamed `final_letter_grade` | 3B |
| `app/Http/Controllers/Admin/GradeController.php` | Pass `rankMap` and `classRanks` props (batch-computed, no N+1) | 3B |
| `resources/js/Pages/Admin/Grades/ClassGrades.vue` | Rank column (3B), export button (3C) | 3B, 3C |
| `resources/js/Pages/Admin/Grades/StudentGrades.vue` | Rank + report card/transcript links | 3B |
| `resources/js/Pages/Admin/Dashboard.vue` | Report Cards + Transcripts action cards | 3B |
| `composer.json` | `barryvdh/laravel-dompdf` (3B), `maatwebsite/excel` (3C) | 3B, 3C |

## New Files

| Sub-Phase | Count | Files |
|-----------|-------|-------|
| 3A | ~32 | 13 factories, 4 migrations, 4 models, 1 service, 4 controllers, ~13 Vue pages, 4 seeders, 6 test files |
| 3B | ~16 | 1 migration, 2 services, 2 controllers, 4 Vue pages, 2 Blade templates, 4 test files, 1 composer dep |
| 3C | ~2 | 1 Vue page, 1 test file (+ 3 methods on GradeController) |

## Post-Completion

- Update `docs/DATABASE.md` — mark Phase 3 implemented
- Update `PROJECT_SUMMARY.md` — reflect completion
- Close GitHub Issue #6

## Issue #6 Alignment

| Feature | Location |
|---------|----------|
| Assessment categories per course with weights | 3A |
| Configurable weight percentages | 3A |
| Reusable templates across classes | 3A (global categories, course_id null) |
| Point-based and percentage-based scoring | 3A (score vs max_score) |
| Extra credit | 3A |
| Per-class grade entry | 3A (Enter.vue) |
| Assessment creation with due dates | 3A |
| Bulk grade entry (spreadsheet-style) | 3A (Enter.vue) |
| Draft/published states | 3A |
| Late submission tracking & penalties | 3A |
| Weighted average calculation | 3A (service) |
| Configurable letter grade mapping | 3A (grading_scales) |
| GPA (per term and cumulative) | 3A service, 3B display |
| Class rank | 3B |
| Report cards (per-student, per-term, PDF, batch) | 3B |
| Transcripts (cumulative, PDF, official/unofficial) | 3B |
| Grade book matrix view | 3A (ClassGrades.vue) |
| Grade import CSV/Excel | 3C |
| Grade export Excel/CSV | 3C |
| Audit trail | Deferred (timestamps + graded_by for now) |
