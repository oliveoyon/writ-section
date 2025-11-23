<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.index'); // We'll create this view later
    }

    public function test()
    {
        return view('dashboard.layouts.testhome');
    }
}
