<?php

namespace App\Http\Middleware;

// app/Http/Middleware/Localization.php

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if 'locale' is set in the session
        if (Session::has('locale')) {
            // Set the locale for the application
            App::setLocale(Session::get('locale'));
        }
        
        return $next($request);
    }
}