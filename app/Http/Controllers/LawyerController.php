<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Lawyer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LawyerController extends Controller
{
    // Show registration form
    public function dashboard()
    {
        return view('website.lawyer.profile');
    }

    public function myCases()
    {
        return view('website.lawyer.profile');
    }

    public function notifications()
    {
        return view('website.lawyer.test');
    }

    public function messages()
    {
        return view('website.lawyer.profile');
    }

    public function documents()
    {
        return view('website.lawyer.profile');
    }

    public function settings()
    {
        $user = auth()->user();
        $lawyer = $user->lawyer;

        return view('website.lawyer.settings', compact('user', 'lawyer'));
    }

    public function settingsUpdate(Request $request)
    {
        $user = auth()->user();
        $lawyer = $user->lawyer;

        $messages = [
            'full_name.required' => __('The full name field is required.'),
            'email.required' => __('The email field is required.'),
            'email.email' => __('The email must be a valid email address.'),
            'email.unique' => __('This email is already taken.'),
            'phone.required' => __('The phone field is required.'),
            'phone.regex' => __('The phone must start with 01 and contain 11 digits.'),
            'new_password.confirmed' => __('The new password confirmation does not match.'),
            'new_password.min' => __('The new password must be at least :min characters.'),
            'picture.image' => __('The picture must be an image.'),
            'picture.max' => __('The picture may not be greater than 2MB.'),
        ];

        $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['required', 'regex:/^01\d{9}$/'],
            'old_password' => ['nullable', 'required_with:new_password'],
            'new_password' => ['nullable', 'confirmed', 'min:6'],
            'picture' => ['nullable', 'image', 'max:2048'],
        ], $messages);

        // Update basic info
        $lawyer->full_name = $request->full_name;
        $user->email = $request->email;
        $lawyer->phone = $request->phone;

        // Change password
        if ($request->filled('new_password')) {
            if (! $request->filled('old_password') || ! Hash::check($request->old_password, $user->password)) {
                return back()->withErrors(['old_password' => __('The current password is incorrect.')])->withInput();
            }
            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        // Handle picture upload
        if ($request->hasFile('picture')) {
            $file = $request->file('picture');
            $stamp = now()->format('YmdHis');
            $filename = ($lawyer->bar_council_id ?? 'lawyer_' . $lawyer->id) . '_' . $stamp . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('lawyers', $filename, 'public');

            // Delete old picture if stored locally
            if ($lawyer->picture && ! str_starts_with($lawyer->picture, 'http')) {
                $oldPath = str_replace('storage/', '', $lawyer->picture);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $lawyer->picture = 'storage/' . $path;
        }

        $lawyer->save();

        return back()->with('success', __('Settings updated successfully.'));
    }
}
