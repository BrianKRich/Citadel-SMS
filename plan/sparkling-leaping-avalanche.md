# Phase 3B: Report Cards, Transcripts & Class Rank

## Context

Phase 3A (core grading) is complete with 66 tests. Phase 3B adds PDF report cards, transcripts, class rank, and fixes a data-mapping gap where `ClassGrades.vue` references `enrollment.weighted_average` and `enrollment.final_letter_grade` but neither exists on the Enrollment model/DB yet.

---

## Step 1 — Foundation, Class Rank & DomPDF

**Install dependency:**
```bash
composer require barryvdh/laravel-dompdf
```

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

---

## Step 2 — Blade PDF Templates

**Create `resources/views/pdf/report-card.blade.php`:**
- Inline CSS only (DomPDF limitation), DejaVu Sans font
- Header: school name, "Report Card", student name/ID, term
- Table: Course | Section | Teacher | Avg % | Grade | GPA | Credits
- Footer: Term GPA, Cumulative GPA, date

**Create `resources/views/pdf/transcript.blade.php`:**
- Official/unofficial conditional header + watermark (CSS `position: fixed; opacity: 0.1`)
- Per-term sections with course tables
- Cumulative GPA, total credits, signature line if official

---

## Step 3 — Services

**Create `app/Services/ReportCardService.php`** (injects `GradeCalculationService`):
- `generateForStudent(Student, Term): array` — enrollment grades, term GPA, cumulative GPA
- `generatePdf(Student, Term): string` — DomPDF render via `pdf.report-card` Blade
- `generateBatchPdf(Term): string` — single Blade view with `@foreach` + `page-break-after: always` per student

**Create `app/Services/TranscriptService.php`** (injects `GradeCalculationService`):
- `generateForStudent(Student): array` — all terms grouped, cumulative GPA, total credits
- `generatePdf(Student, bool $official): string` — DomPDF render via `pdf.transcript` Blade

---

## Step 4 — Controllers & Routes

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

---

## Step 5 — Vue Pages

**Create `resources/js/Pages/Admin/ReportCards/Index.vue`:**
- Term dropdown, student table with View/Download actions, batch download button
- Pattern: AuthenticatedLayout + Card + PageHeader + pagination

**Create `resources/js/Pages/Admin/ReportCards/Show.vue`:**
- Student info, enrollment table, term GPA, cumulative GPA, download button

**Create `resources/js/Pages/Admin/Transcripts/Index.vue`:**
- Student search, table with View/Download/Download Official actions

**Create `resources/js/Pages/Admin/Transcripts/Show.vue`:**
- Student info, per-term course tables, cumulative summary, download buttons

---

## Step 6 — Update Existing Pages

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

---

## Step 7 — Tests

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

---

## Files Summary

| Action | File |
|--------|------|
| Install | `barryvdh/laravel-dompdf` via composer |
| Create | Migration: `update_enrollments_for_grade_display` |
| Modify | `app/Models/Enrollment.php` |
| Modify | `app/Services/GradeCalculationService.php` |
| Create | `app/Services/ReportCardService.php` |
| Create | `app/Services/TranscriptService.php` |
| Create | `app/Http/Controllers/Admin/ReportCardController.php` |
| Create | `app/Http/Controllers/Admin/TranscriptController.php` |
| Modify | `routes/web.php` |
| Create | `resources/views/pdf/report-card.blade.php` |
| Create | `resources/views/pdf/transcript.blade.php` |
| Create | `resources/js/Pages/Admin/ReportCards/Index.vue` |
| Create | `resources/js/Pages/Admin/ReportCards/Show.vue` |
| Create | `resources/js/Pages/Admin/Transcripts/Index.vue` |
| Create | `resources/js/Pages/Admin/Transcripts/Show.vue` |
| Modify | `app/Http/Controllers/Admin/GradeController.php` |
| Modify | `resources/js/Pages/Admin/Grades/ClassGrades.vue` |
| Modify | `resources/js/Pages/Admin/Grades/StudentGrades.vue` |
| Modify | `resources/js/Pages/Admin/Dashboard.vue` |
| Modify | `tests/Unit/Services/GradeCalculationServiceTest.php` |
| Create | `tests/Unit/Models/EnrollmentTest.php` |
| Create | `tests/Feature/Admin/ReportCardTest.php` |
| Create | `tests/Feature/Admin/TranscriptTest.php` |
| Modify | `tests/Feature/Admin/GradeTest.php` |

---

## Verification

1. `php artisan migrate` — weighted_average added, final_grade renamed to final_letter_grade
2. `php artisan test` — all existing 66 + new ~28 tests pass
3. Manual: ClassGrades now shows Avg %, Grade, and Rank columns populated
4. Manual: Report Cards → select term → view preview → download PDF
5. Manual: Transcripts → search student → view preview → download official/unofficial PDF
6. Manual: Batch download creates combined PDF
7. `npm run build` — frontend compiles cleanly
