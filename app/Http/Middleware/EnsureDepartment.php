<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDepartment
{
    public function handle(Request $request, Closure $next, ...$allowedDepartments): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->hasRole('Super Admin')) {
            return $next($request);
        }

        $departmentName = $user->departmentRelation?->name ?? $user->department;

        if (!$departmentName) {
            abort(403, 'Department is not assigned.');
        }

        $normalizedCurrent = $this->normalize($departmentName);
        $allowed = array_map(fn ($d) => $this->normalize($d), $allowedDepartments);

        if (!in_array($normalizedCurrent, $allowed, true)) {
            return redirect()->route($this->landingRouteName($user));
        }

        return $next($request);
    }

    private function normalize(string $value): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', str_replace(['_', '-'], ' ', $value))));
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

        if (str_contains($department, 'office assistant') || str_contains($department, 'dealing assistant')) {
            return 'admin.tracking.section.receive';
        }

        if (
            str_contains($department, 'affidavit') ||
            str_contains($department, 'requisite') ||
            str_contains($department, 'put-up') ||
            str_contains($department, 'put up') ||
            str_contains($department, 'dealing assistant') ||
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
