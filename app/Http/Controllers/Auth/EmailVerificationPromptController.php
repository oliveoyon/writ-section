<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        return $request->user()->hasVerifiedEmail()
                    ? redirect()->intended($this->dashboardPath($request->user()))
                    : view('auth.verify-email');
    }

    private function dashboardPath($user): string
    {
        return route($user?->user_type === 'lawyer' ? 'lawyer.dashboard' : 'admin.dashboard', absolute: false);
    }
}
