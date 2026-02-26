<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q', '');

        $students = Student::active()
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('student_id', 'like', "%{$query}%")
                  ->orWhereRaw("(first_name || ' ' || last_name) like ?", ["%{$query}%"]);
            })
            ->orderBy('first_name')
            ->limit(10)
            ->get(['id', 'student_id', 'first_name', 'last_name']);

        return response()->json($students->map(fn ($s) => [
            'id'         => $s->id,
            'student_id' => $s->student_id,
            'first_name' => $s->first_name,
            'last_name'  => $s->last_name,
            'full_name'  => "{$s->first_name} {$s->last_name}",
        ]));
    }
}
