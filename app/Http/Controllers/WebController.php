<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Lawyer; // your lawyer table model
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WebController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->user_type === 'lawyer') {
            return redirect()->route('lawyer.dashboard');
        }

        return view('website.index');
    }

    public function login(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->user_type === 'lawyer') {
            return redirect()->route('lawyer.dashboard');
        }

        return view('website.login');
    }

    public function lawyerLoginSubmit(Request $request)
    {
        $request->validate([
            'email_or_phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = null;

        // Try email first
        $user = User::where('email', $request->email_or_phone)
            ->where('user_type', 'lawyer')
            ->first();

        // If not found, check phone in lawyers table
        if (!$user) {
            $lawyer = Lawyer::where('phone', $request->email_or_phone)->first();
            if ($lawyer) {
                $user = User::where('id', $lawyer->user_id)
                    ->where('user_type', 'lawyer')
                    ->first();
            }
        }

        if ($user && Hash::check($request->password, $user->password)) {
            Auth::login($user, $request->has('remember'));
            return redirect()->route('lawyer.dashboard'); // your dashboard route
        }

        return back()->withErrors([
            'email_or_phone' => __('writ.auth.invalid_credentials'),
        ])->withInput();
    }
}
