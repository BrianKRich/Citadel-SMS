# Phase 3B: Report Cards, Transcripts & Class Rank

## Context

Phase 3A (core grading) is complete with 66 tests. Phase 3B adds PDF report cards, transcripts, class rank, and fixes a data-mapping gap where `ClassGrades.vue` references `enrollment.weighted_average` and `enrollment.final_letter_grade` but neither exists on the Enrollment model/DB yet.

---

## Step 1 — Foundation Fixes & DomPDF

**Install dependency:**
```bash
composer require barryvdh/laravel-dompdf
```

**New migration** — `add_weighted_average_to_enrollments_table.php`:
- Add `weighted_average decimal(5,2) nullable` to `enrollments`

**Modify `app/Models/Enrollment.php`:**
- Add `weighted_average` to `$fillable` and `$casts`
- Add `getFinalLetterGradeAttribute()` accessor (returns `$this->final_grade`)
- Add `$appends = ['final_letter_grade']`

**Modify `app/Services/GradeCalculationService.php` — `updateEnrollmentGrade()`:**
- Store computed average in `weighted_average` alongside `final_grade`/`grade_points`

---

## Step 2 — `calculateClassRank()`

**Modify `app/Services/GradeCalculationService.php`:**
- Add `calculateClassRank(ClassModel): Collection`
- Queries enrolled enrollments with `weighted_average`, sorts descending
- Standard ranking (ties share rank, next rank skips: 1,1,3)
- Returns collection of `[enrollment_id, student_id, weighted_average, rank]`

---

## Step 3 — ReportCardService & TranscriptService

**Create `app/Services/ReportCardService.php`:**
- `generateForStudent(Student, Term): array` — enrollments, term GPA, cumulative GPA
- `generatePdf(Student, Term): string` — DomPDF render via `pdf.report-card` Blade
- `generateBatchPdf(Term): string` — single PDF with all students (page-break-after per student)

**Create `app/Services/TranscriptService.php`:**
- `generateForStudent(Student): array` — all terms grouped, cumulative GPA, total credits
- `generatePdf(Student, bool $official): string` — DomPDF render via `pdf.transcript` Blade

---

## Step 4 — Blade PDF Templates

**Create `resources/views/pdf/report-card.blade.php`:**
- Inline CSS only (DomPDF limitation), DejaVu Sans font
- Header: school name, "Report Card", student name/ID, term
- Table: Course | Section | Teacher | Avg % | Grade | GPA | Credits
- Footer: Term GPA, Cumulative GPA, date

**Create `resources/views/pdf/transcript.blade.php`:**
- Same inline CSS approach
- Official/unofficial conditional header + watermark (CSS `position: fixed; opacity: 0.1`)
- Per-term sections with course tables
- Cumulative GPA, total credits, signature line if official

---

## Step 5 — Controllers & Routes

**Create `app/Http/Controllers/Admin/ReportCardController.php`:**
- `index(Request)` — term selector + paginated students for selected term
- `show(Student, Term)` — HTML preview via Inertia
- `download(Student, Term)` — PDF response
- `batchDownload(Term)` — combined PDF response

**Create `app/Http/Controllers/Admin/TranscriptController.php`:**
- `index(Request)` — student search, paginated
- `show(Student)` — HTML preview via Inertia
- `download(Student, Request)` — PDF, `?official=1` flag

**Modify `routes/web.php`** — add within auth middleware group:
```php
Route::prefix('admin/report-cards')->group(function () {
    Route::get('/', ...)->name('admin.report-cards.index');
    Route::get('/batch/{term}', ...)->name('admin.report-cards.batch');  // BEFORE {student}
    Route::get('/{student}/{term}', ...)->name('admin.report-cards.show');
    Route::get('/{student}/{term}/download', ...)->name('admin.report-cards.download');
});
Route::prefix('admin/transcripts')->group(function () {
    Route::get('/', ...)->name('admin.transcripts.index');
    Route::get('/{student}/download', ...)->name('admin.transcripts.download');  // BEFORE {student}
    Route::get('/{student}', ...)->name('admin.transcripts.show');
});
```

---

## Step 6 — Vue Pages

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

## Step 7 — Update Existing Pages

**Modify `app/Http/Controllers/Admin/GradeController.php`:**
- `classGrades()` — call `calculateClassRank()`, pass `rankMap` prop
- `studentGrades()` — compute rank per enrollment, pass `classRanks` prop + report card/transcript links

**Modify `resources/js/Pages/Admin/Grades/ClassGrades.vue`:**
- Add `rankMap` prop, add "Rank" column after "Grade"

**Modify `resources/js/Pages/Admin/Grades/StudentGrades.vue`:**
- Add `classRanks` prop, show rank per enrollment card
- Add links to report card and transcript

**Modify `resources/js/Pages/Admin/Dashboard.vue`:**
- Add "Report Cards" and "Transcripts" action cards

---

## Step 8 — Tests

**Modify `tests/Unit/Services/GradeCalculationServiceTest.php`** — add:
- `test_calculate_class_rank_orders_by_average`
- `test_calculate_class_rank_handles_tied_scores` (standard ranking: 1,1,3)

**Create `tests/Feature/Admin/ReportCardTest.php`** (~6 tests):
- Index renders, index with term lists students, show preview, download PDF (Content-Type), batch PDF, auth redirect

**Create `tests/Feature/Admin/TranscriptTest.php`** (~7 tests):
- Index renders, search filters, show preview, download PDF, official flag, includes all terms, auth redirect

**Create `tests/Feature/Services/ReportCardServiceTest.php`** (~3 tests):
- Includes enrolled courses, calculates term GPA, batch PDF starts with `%PDF`

**Create `tests/Feature/Services/TranscriptServiceTest.php`** (~4 tests):
- Includes all terms, calculates cumulative GPA, official/unofficial PDFs start with `%PDF`

---

## Files Summary

| Action | File |
|--------|------|
| Install | `barryvdh/laravel-dompdf` via composer |
| Create | Migration: `add_weighted_average_to_enrollments_table` |
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
| Create | `tests/Feature/Admin/ReportCardTest.php` |
| Create | `tests/Feature/Admin/TranscriptTest.php` |
| Create | `tests/Feature/Services/ReportCardServiceTest.php` |
| Create | `tests/Feature/Services/TranscriptServiceTest.php` |

---

## Verification

1. `php artisan migrate` — weighted_average column added
2. `php artisan test` — all existing 66 + new ~22 tests pass
3. Manual: ClassGrades now shows Avg % and Rank columns populated
4. Manual: Report Cards → select term → view preview → download PDF
5. Manual: Transcripts → search student → view preview → download official/unofficial PDF
6. Manual: Batch download creates combined PDF
7. `npm run build` — frontend compiles cleanly
