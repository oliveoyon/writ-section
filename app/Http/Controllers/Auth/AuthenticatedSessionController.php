<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        $request->session()->put('last_activity_at', time());

        return redirect()->to($this->redirectPathFor($request->user()));
    }

    public function proximityLogin(Request $request): RedirectResponse
    {
        $request->validate([
            'login_id' => ['required', 'string', 'max:255'],
        ]);

        $user = User::where('login_id', trim((string) $request->login_id))
            ->where('is_active', true)
            ->first();

        if (!$user) {
            return back()->withErrors([
                'login_id' => 'Invalid proximity card login.',
            ])->onlyInput('login_id');
        }

        Auth::login($user, true);
        $request->session()->regenerate();
        $request->session()->put('last_activity_at', time());

        return redirect()->to($this->redirectPathFor($user));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->forget('last_activity_at');
        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }

    private function redirectPathFor(?User $user): string
    {
        if (!$user) {
            return route('login');
        }

        if ($user->user_type === 'lawyer') {
            return route('lawyer.dashboard');
        }

        $department = strtolower((string) ($user->departmentRelation?->name ?? ''));
        $isStaff = $user->user_type === 'staff';

        if (str_contains($department, 'filing')) {
            return route('admin.tracking.filing.scan-temp');
        }

        if (str_contains($department, 'office assistant')) {
            return route('admin.tracking.section.receive');
        }

        if (
            str_contains($department, 'affidavit') ||
            str_contains($department, 'requisite') ||
            str_contains($department, 'put-up') ||
            str_contains($department, 'put up') ||
            str_contains($department, 'typing') ||
            str_contains($department, 'compare') ||
            str_contains($department, 'superintendent') ||
            str_contains($department, 'ready table') ||
            str_contains($department, 'record room') ||
            str_contains($department, 'court')
        ) {
            return route('admin.tracking.section.receive');
        }

        if (str_contains($department, 'registrar')) {
            return route('admin.tracking.lookup');
        }

        return $isStaff ? route('admin.tracking.register-report') : route('admin.dashboard');
    }
}
