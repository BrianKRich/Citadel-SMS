<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q', '');

        $employees = Employee::active()
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%");
            })
            ->orderBy('first_name')
            ->limit(10)
            ->get(['id', 'first_name', 'last_name']);

        return response()->json($employees->map(fn ($e) => [
            'id'         => $e->id,
            'first_name' => $e->first_name,
            'last_name'  => $e->last_name,
            'full_name'  => "{$e->first_name} {$e->last_name}",
        ]));
    }
}
