<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDepartment
{
    public function handle(Request $request, Closure $next, ...$allowedDepartments): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Unauthorized.');
        }

        $departmentName = $user->departmentRelation?->name ?? $user->department;

        if (!$departmentName) {
            abort(403, 'Department is not assigned.');
        }

        $normalizedCurrent = $this->normalize($departmentName);
        $allowed = array_map(fn ($d) => $this->normalize($d), $allowedDepartments);

        if (!in_array($normalizedCurrent, $allowed, true)) {
            abort(403, 'Unauthorized department access.');
        }

        return $next($request);
    }

    private function normalize(string $value): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', str_replace(['_', '-'], ' ', $value))));
    }
}
