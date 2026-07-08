<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CheckUserType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed ...$types  Allowed user types
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$types)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (!in_array($user->user_type, $types)) {
            return redirect()->route($this->landingRouteName($user));
        }

        return $next($request);
    }

    private function landingRouteName(User $user): string
    {
        if ($user->user_type === 'lawyer') {
            return 'lawyer.dashboard';
        }

        $department = strtolower((string) ($user->departmentRelation?->name ?? ''));
        $isStaff = $user->user_type === 'staff';

        if (str_contains($department, 'filing')) {
            return 'admin.tracking.filing.scan-temp';
        }

        if (str_contains($department, 'office assistant')) {
            return 'admin.tracking.section.receive';
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
            return 'admin.tracking.section.receive';
        }

        if (str_contains($department, 'registrar')) {
            return 'admin.tracking.lookup';
        }

        return $isStaff && $department !== ''
            ? 'admin.tracking.section.receive'
            : ($isStaff ? 'admin.tracking.register-report' : 'admin.dashboard');
    }
}
