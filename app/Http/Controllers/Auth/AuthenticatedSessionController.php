<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\FaceRecognitionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
            'pin' => ['nullable', 'string'],
        ]);

        $user = User::where('login_id', trim((string) $request->login_id))
            ->where('is_active', true)
            ->first();

        if (!$user) {
            return back()->withErrors([
                'login_id' => 'Invalid proximity card login.',
            ])->onlyInput('login_id');
        }

        if (in_array((string) $user->user_type, ['admin', 'staff'], true)) {
            return redirect()->route('login')->with([
                'show_admin_login' => true,
                'admin_login_id_prefill' => (string) $user->login_id,
                'admin_login_notice' => 'Admin/Staff detected. Continue with Login ID + Password or Login ID + Face.',
            ]);
        }

        if ($request->filled('pin') && !Hash::check((string) $request->pin, $user->password)) {
            return back()->withErrors([
                'login_id' => 'Card scanned but PIN is invalid.',
            ])->onlyInput('login_id');
        }

        Auth::login($user, true);
        $request->session()->regenerate();
        $request->session()->put('last_activity_at', time());

        return redirect()->to($this->redirectPathFor($user));
    }

    public function faceLogin(Request $request, FaceRecognitionService $faceRecognitionService): RedirectResponse
    {
        $request->validate([
            'login_id' => ['required', 'string', 'max:255'],
            'descriptor' => ['required', 'array', 'size:128'],
            'descriptor.*' => ['required', 'numeric'],
        ]);

        $user = User::query()
            ->where('login_id', trim((string) $request->input('login_id')))
            ->whereIn('user_type', ['admin', 'staff'])
            ->where('is_active', true)
            ->first();

        if (!$user) {
            return back()->withErrors([
                'face_login' => 'Invalid login ID for admin/staff user.',
            ])->withInput();
        }

        if (empty($user->face_descriptor) || !is_array($user->face_descriptor)) {
            return back()->withErrors([
                'face_login' => 'No face is enrolled for this user. Contact admin.',
            ])->withInput();
        }

        $distance = $faceRecognitionService->distance(
            (array) $request->input('descriptor', []),
            (array) $user->face_descriptor
        );

        $threshold = (float) config('face.match_threshold', 0.42);

        if ($distance === null || $distance >= $threshold) {
            return back()->withErrors([
                'face_login' => 'Face not recognized for this login ID.',
            ])->withInput();
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

        if (str_contains($department, 'filing')) {
            return route('admin.tracking.filing.scan-temp');
        }

        if (str_contains($department, 'office assistant')) {
            return route('admin.tracking.court.dispatch.index');
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

        return route('admin.dashboard');
    }
}
