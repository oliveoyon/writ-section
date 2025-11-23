<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        if (!in_array(Auth::user()->user_type, $types)) {
            abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
}
