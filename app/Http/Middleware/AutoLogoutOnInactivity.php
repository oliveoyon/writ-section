<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AutoLogoutOnInactivity
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $timeoutMinutes = max((int) env('AUTO_LOGOUT_MINUTES', 10), 0);

        if ($timeoutMinutes === 0) {
            return $next($request);
        }

        $now = time();
        $lastActivity = (int) $request->session()->get('last_activity_at', 0);

        if ($lastActivity > 0 && ($now - $lastActivity) > ($timeoutMinutes * 60)) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('status', 'Session expired due to inactivity. Please login again.');
        }

        $request->session()->put('last_activity_at', $now);

        return $next($request);
    }
}
