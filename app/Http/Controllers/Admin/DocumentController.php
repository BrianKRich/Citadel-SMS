<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Employee;
use App\Models\Setting;
use App\Models\Student;
use App\Models\TrainingRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DocumentController extends Controller
{
    private function requireDocumentsEnabled(): void
    {
        abort_if(Setting::get('feature_documents_enabled', '0') !== '1', 403);
    }

    public function index(Request $request)
    {
        $this->requireDocumentsEnabled();
        abort_unless(auth()->user()->isAdmin(), 403);

        $hasFilter = $request->filled('search') || $request->filled('entity_type') || $request->filled('category');

        $documents = null;

        if ($hasFilter) {
            $query = Document::with('uploader');

            if ($request->filled('entity_type')) {
                $query->where('entity_type', $request->entity_type);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('original_name', 'ilike', "%{$search}%")
                      ->orWhere('category', 'ilike', "%{$search}%")
                      ->orWhere('description', 'ilike', "%{$search}%");
                });
            }

            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            $documents = $query->latest()->paginate(20)->withQueryString();
        }

        $categories = Document::whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return Inertia::render('Admin/Documents/Index', [
            'documents'  => $documents,
            'categories' => $categories,
            'filters'    => $request->only(['search', 'entity_type', 'category']),
            'searched'   => $hasFilter,
            'employees'       => Employee::orderBy('last_name')->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'employee_id']),
            'students'        => Student::orderBy('last_name')->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'student_id']),
            'trainingRecords' => TrainingRecord::with(['employee', 'trainingCourse'])->orderBy('date_completed', 'desc')->get(['id', 'employee_id', 'training_course_id', 'date_completed']),
        ]);
    }

    public function store(Request $request)
    {
        $this->requireDocumentsEnabled();
        abort_unless(auth()->user()->isAdmin(), 403);

        $validated = $request->validate([
            'entity_type' => ['required', 'string', 'in:Student,Employee,Institution,TrainingRecord'],
            'entity_id'   => ['required', 'integer', 'min:0'],
            'file'        => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,gif,webp'],
            'category'    => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);

        // Verify entity exists
        if ($validated['entity_type'] === 'Student') {
            abort_unless(Student::where('id', $validated['entity_id'])->exists(), 422, 'Student not found.');
        } elseif ($validated['entity_type'] === 'Employee') {
            abort_unless(Employee::where('id', $validated['entity_id'])->exists(), 422, 'Employee not found.');
        } elseif ($validated['entity_type'] === 'TrainingRecord') {
            abort_unless(TrainingRecord::where('id', $validated['entity_id'])->exists(), 422, 'Training record not found.');
        }
        // Institution: entity_id must be 0
        if ($validated['entity_type'] === 'Institution') {
            $validated['entity_id'] = 0;
        }

        $file     = $request->file('file');
        $uuid     = (string) \Illuminate\Support\Str::uuid();
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
        $path     = "documents/{$validated['entity_type']}/{$validated['entity_id']}/{$uuid}_{$safeName}";

        Storage::disk('local')->putFileAs(
            "documents/{$validated['entity_type']}/{$validated['entity_id']}",
            $file,
            "{$uuid}_{$safeName}"
        );

        Document::create([
            'entity_type'   => $validated['entity_type'],
            'entity_id'     => $validated['entity_id'],
            'uploaded_by'   => auth()->id(),
            'uuid'          => $uuid,
            'original_name' => $file->getClientOriginalName(),
            'stored_path'   => $path,
            'mime_type'     => $file->getMimeType(),
            'size_bytes'    => $file->getSize(),
            'category'      => $validated['category'] ?? null,
            'description'   => $validated['description'] ?? null,
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function download(Document $document)
    {
        $this->requireDocumentsEnabled();

        abort_unless(Storage::disk('local')->exists($document->stored_path), 404);

        return Storage::disk('local')->download(
            $document->stored_path,
            $document->original_name,
            ['Content-Type' => $document->mime_type]
        );
    }

    public function destroy(Document $document)
    {
        $this->requireDocumentsEnabled();
        abort_unless(auth()->user()->isAdmin(), 403);

        Storage::disk('local')->delete($document->stored_path);
        $document->delete();

        return back()->with('success', 'Document deleted.');
    }
}
