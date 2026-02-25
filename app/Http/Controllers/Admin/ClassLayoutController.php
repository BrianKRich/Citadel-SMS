<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class ClassLayoutController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        return Inertia::render('Admin/ClassLayout');
    }
}
