<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PurgeLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        if ($modelType = $request->input('model_type')) {
            $query->modelType($modelType);
        }
        if ($userId = $request->input('user_id')) {
            $query->actor((int) $userId);
        }
        if ($action = $request->input('action')) {
            $query->action($action);
        }
        if ($dateFrom = $request->input('date_from')) {
            $query->dateFrom($dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->dateTo($dateTo);
        }

        return Inertia::render('Admin/AuditLog/Index', [
            'logs'        => $query->paginate(20)->withQueryString(),
            'filters'     => $request->only(['model_type', 'user_id', 'action', 'date_from', 'date_to']),
            'users'       => User::orderBy('name')->get(['id', 'name']),
            'purgeLogs'   => PurgeLog::with('user')->latest()->get(),
            'canPurge'    => auth()->user()->isSiteAdmin(),
        ]);
    }

    public function purge(Request $request)
    {
        abort_unless(auth()->user()->isSiteAdmin(), 403);

        $validated = $request->validate([
            'older_than' => ['required', Rule::in(['30', '90', '180', '365', 'all'])],
            'reason'     => ['required', 'string', 'max:1000'],
        ]);

        if ($validated['older_than'] === 'all') {
            $deleted = AuditLog::query()->delete();
        } else {
            $deleted = AuditLog::where('created_at', '<', now()->subDays((int) $validated['older_than']))->delete();
        }

        $actor = Auth::user();

        PurgeLog::create([
            'user_id'      => $actor?->id,
            'user_name'    => $actor?->name ?? 'Unknown',
            'older_than'   => $validated['older_than'],
            'purged_count' => $deleted,
            'reason'       => $validated['reason'],
        ]);

        return redirect()->route('admin.audit-log.index')
            ->with('success', number_format($deleted) . ' audit log ' . str('entry')->plural($deleted) . ' purged.');
    }

    public function show(AuditLog $auditLog)
    {
        $auditLog->load('user');

        return Inertia::render('Admin/AuditLog/Show', [
            'log' => $auditLog,
        ]);
    }
}
