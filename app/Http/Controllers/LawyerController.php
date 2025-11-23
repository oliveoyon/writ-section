<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Lawyer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class LawyerController extends Controller
{
    // Show registration form
    public function dashboard()
    {
        return view('website.lawyer.profile');
    }

    public function myCases()
    {
        return view('website.lawyer.profile');
    }

    public function notifications()
    {
        return view('website.lawyer.profile');
    }

    public function messages()
    {
        return view('website.lawyer.profile');
    }

    public function documents()
    {
        return view('website.lawyer.profile');
    }

    public function settings()
    {
        return view('website.lawyer.profile');
    }

    
}
