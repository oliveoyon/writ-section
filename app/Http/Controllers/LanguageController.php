<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App; 

class LanguageController extends Controller
{
    public function setLocale(Request $request, $locale)
    {
        $supportedLocales = ['en', 'bn']; 
        
        if (! in_array($locale, $supportedLocales)) {
            abort(400, 'Unsupported language.'); 
        }

        Session::put('locale', $locale);

        App::setLocale($locale);

        return redirect()->back(); 
    }
}