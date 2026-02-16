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

### Step 1: Dependencies & Services

`composer require barryvdh/laravel-dompdf`

**Add to GradeCalculationService:**
- `calculateClassRank(ClassModel): Collection` — rank students by weighted average

**`app/Services/ReportCardService.php`:**
- `generateForStudent(Student, Term): array` — enrollment grades, term GPA (via `calculateTermGpa`), cumulative GPA (via `calculateCumulativeGpa`)
- `generatePdf(Student, Term): string` — DomPDF render
- `generateBatchPdf(Term): string` — all students in term

**`app/Services/TranscriptService.php`:**
- `generateForStudent(Student): array` — all terms/courses, cumulative GPA (via `calculateCumulativeGpa`), credits
- `generatePdf(Student, bool $official): string` — with official/unofficial watermark

### Step 2: Controllers & Pages

**Controllers:**

| Controller | Actions |
|------------|---------|
| `ReportCardController` | `index` (select term, list students), `show` (HTML preview), `download` (PDF), `batchDownload` (all students) |
| `TranscriptController` | `index` (student search), `show` (HTML preview), `download` (PDF, `?official=true`) |

**Vue Pages:**
- `Pages/Admin/ReportCards/` — Index.vue, Show.vue
- `Pages/Admin/Transcripts/` — Index.vue, Show.vue

**Blade PDF templates:**
- `resources/views/pdf/report-card.blade.php`
- `resources/views/pdf/transcript.blade.php`

**Update existing pages:**
- `ClassGrades.vue` — add rank column
- `StudentGrades.vue` — add rank, links to report card/transcript

### Step 3: Tests

**Unit** (add to `GradeCalculationServiceTest.php`):
- `test_calculate_class_rank_orders_by_average`, `test_calculate_class_rank_handles_tied_scores`

**Feature:**

| File | Tests |
|------|-------|
| `tests/Feature/Admin/ReportCardTest.php` | Index lists students, show renders preview, download returns PDF (content-type), batch download, auth redirect |
| `tests/Feature/Admin/TranscriptTest.php` | Index search, show preview, download PDF, official watermark flag, includes all terms |
| `tests/Feature/Services/ReportCardServiceTest.php` | Includes all enrolled courses, calculates term GPA, batch creates combined doc |
| `tests/Feature/Services/TranscriptServiceTest.php` | Includes all terms, calculates cumulative GPA, official flag |

### 3B Verification

1. `php artisan test` — all pass
2. Report card: preview → PDF download → batch download
3. Transcript: preview → official/unofficial PDF
4. Class rank on grade book

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
| `app/Services/GradeCalculationService.php` | Add `calculateClassRank()` | 3B |
| `resources/js/Pages/Admin/Grades/ClassGrades.vue` | Rank column (3B), export button (3C) | 3B, 3C |
| `resources/js/Pages/Admin/Grades/StudentGrades.vue` | Rank + report card/transcript links | 3B |
| `composer.json` | `barryvdh/laravel-dompdf` (3B), `maatwebsite/excel` (3C) | 3B, 3C |

## New Files

| Sub-Phase | Count | Files |
|-----------|-------|-------|
| 3A | ~32 | 13 factories, 4 migrations, 4 models, 1 service, 4 controllers, ~13 Vue pages, 4 seeders, 6 test files |
| 3B | ~14 | 2 services, 2 controllers, 4 Vue pages, 2 Blade templates, 4 test files |
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
