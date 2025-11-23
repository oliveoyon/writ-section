<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Lawyer;
use Illuminate\Support\Facades\Hash;

class LawyerRegistrationController extends Controller
{
    public function showForm()
    {
        return view('website.lawyer.register');
    }

    public function checkMember(Request $request)
    {
        $request->validate(['member_id' => 'required'], [
            'member_id.required' => __('writ.lawyer.validation_member_id')
        ]);

        $memberId = $request->member_id;

        // Check if already registered in lawyers table
        $existingLawyer = Lawyer::where('bar_council_id', $memberId)->first();
        if ($existingLawyer) {
            return response()->json([
                'found' => false,
                'message' => __('writ.lawyer.already_registered') // Add this string in your lang file
            ]);
        }

        // Call API only if not already registered
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.scba.org.bd/api/esl/memberlist");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                throw new \Exception(curl_error($ch));
            }
            curl_close($ch);

            $data = json_decode($response, true);

            if (!$data) {
                throw new \Exception(__('writ.lawyer.api_error'));
            }

            $member = array_filter($data, fn($m) => $m['memberId'] == $memberId);
            $member = array_values($member);

            if (empty($member)) {
                return response()->json([
                    'found' => false,
                    'message' => __('writ.lawyer.not_found')
                ]);
            }

            return response()->json([
                'found' => true,
                'member' => $member[0]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'found' => false,
                'message' => __('writ.lawyer.api_error') . ' ' . $e->getMessage()
            ], 500);
        }
    }


    public function register(Request $request)
    {

        $request->validate([
            'member_id' => 'required',
            'full_name' => 'required|string',
            'phone' => [
                'required',
                // 'regex:/^01\d{9}$/', // 11 digits, starts with 01
            ],
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:6|regex:/^[\x20-\x7E]+$/', // English characters only
        ], [
            'member_id.required' => __('writ.lawyer.validation_member_id'),
            'full_name.required' => __('writ.lawyer.validation_full_name'),
            'phone.required' => __('writ.lawyer.validation_phone'),
            'phone.regex' => __('writ.lawyer.validation_phone_invalid'), // "Phone must be 11 digits starting with 01"
            'email.required' => __('writ.lawyer.validation_email'),
            'email.email' => __('writ.lawyer.validation_email_invalid'),
            'email.unique' => __('writ.lawyer.validation_email_unique'),
            'password.required' => __('writ.lawyer.validation_password'),
            'password.confirmed' => __('writ.lawyer.validation_password_confirmed'),
            'password.min' => __('writ.lawyer.validation_password_min'),
            'password.regex' => __('writ.lawyer.validation_password_english'), // "Password must contain English characters only"
        ]);

        $user = User::create([
            'name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => 'lawyer',
            'is_active' => 0
        ]);

        Lawyer::create([
            'user_id' => $user->id,
            'bar_council_id' => $request->member_id,
            'full_name' => $request->full_name,
            'phone' => $request->phone,
            'picture' => $request->picture ?? null,
            'barDateOfJoining' => $request->barDateOfJoining ?? null,
            'barDateOfEnrollment' => $request->barDateOfEnrollment ?? null,
            'barCourtType' => $request->barCourtType ?? null,
            'status' => $request->status ?? 'active',
        ]);


        return redirect()->route('lawyer.login')
            ->with('success', __('writ.lawyer.registration_success'));
    }
}
