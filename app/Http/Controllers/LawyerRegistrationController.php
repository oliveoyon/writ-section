<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Lawyer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

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

        $apiDiagnostics = [];

        // Call API only if not already registered
        try {
            $ch = curl_init();
            $verifySsl = filter_var(config('services.scba.verify_ssl', false), FILTER_VALIDATE_BOOL);
            $sslCipherList = trim((string) config('services.scba.ssl_cipher_list', 'DEFAULT@SECLEVEL=1'));
            $curlOptions = [
                CURLOPT_URL => config('services.scba.member_list_url'),
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => '',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => $verifySsl,
                CURLOPT_SSL_VERIFYHOST => $verifySsl ? 2 : 0,
                CURLOPT_HTTPHEADER => ['Accept: application/json'],
                CURLOPT_ENCODING => '',
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_USERAGENT => 'WritFileTracking/1.0',
            ];

            if ($sslCipherList !== '') {
                $curlOptions[CURLOPT_SSL_CIPHER_LIST] = $sslCipherList;
            }

            curl_setopt_array($ch, $curlOptions);

            $response = curl_exec($ch);
            $curlErrno = curl_errno($ch);

            if ($curlErrno !== 0 && $sslCipherList !== 'DEFAULT@SECLEVEL=0') {
                $apiDiagnostics['first_attempt'] = [
                    'curl_errno' => $curlErrno,
                    'curl_error' => curl_error($ch),
                    'ssl_cipher_list' => $sslCipherList,
                ];

                curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'DEFAULT@SECLEVEL=0');
                $response = curl_exec($ch);
                $curlErrno = curl_errno($ch);
            }

            if ($curlErrno !== 0) {
                $curlError = curl_error($ch);
                $apiDiagnostics['curl_errno'] = $curlErrno;
                $apiDiagnostics['curl_error'] = $curlError;
                $apiDiagnostics['curl_info'] = curl_getinfo($ch);
                curl_close($ch);
                throw new \Exception($curlError);
            }
            $apiDiagnostics = [
                'http_status' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
                'content_type' => curl_getinfo($ch, CURLINFO_CONTENT_TYPE),
                'effective_url' => curl_getinfo($ch, CURLINFO_EFFECTIVE_URL),
                'response_bytes' => is_string($response) ? strlen($response) : 0,
            ] + $apiDiagnostics;
            curl_close($ch);

            if ($apiDiagnostics['http_status'] < 200 || $apiDiagnostics['http_status'] >= 300) {
                throw new \RuntimeException('SCBA returned HTTP ' . $apiDiagnostics['http_status']);
            }

            $response = preg_replace('/^\xEF\xBB\xBF/', '', (string) $response);

            $data = json_decode($response, true);

            if (!is_array($data)) {
                $apiDiagnostics['json_error'] = json_last_error_msg();
                $apiDiagnostics['response_preview'] = mb_substr(trim(strip_tags((string) $response)), 0, 200);
                throw new \RuntimeException('SCBA returned an invalid JSON response.');
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
            Log::warning('SCBA member lookup failed.', [
                'member_id' => $memberId,
                'error' => $e->getMessage(),
                'diagnostics' => $apiDiagnostics,
            ]);

            return response()->json([
                'found' => false,
                'message' => __('writ.lawyer.api_error')
            ]);
        }
    }


    public function register(Request $request)
    {

        $request->validate([
            'member_id' => [
                'nullable',
                'string',
                Rule::unique('lawyers', 'bar_council_id')->whereNotNull('bar_council_id'),
            ],
            'full_name' => 'required|string',
            'phone' => [
                'required',
                // 'regex:/^01\d{9}$/', // 11 digits, starts with 01
            ],
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:6|regex:/^[\x20-\x7E]+$/', // English characters only
        ], [
            'member_id.unique' => __('writ.lawyer.already_registered'),
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

        $phone = $request->phone;
        $memberId = trim((string) $request->input('member_id'));
        $memberId = $memberId !== '' ? $memberId : null;

        // Remove leading 88 if exists
        if (str_starts_with($phone, '88')) {
            $phone = substr($phone, 2);
        }

        // Optional: remove any other non-digit characters
        $phone = preg_replace('/\D/', '', $phone);

        $user = User::create([
            'name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => 'lawyer',
            'is_active' => 0
        ]);

        Lawyer::create([
            'user_id' => $user->id,
            'bar_council_id' => $memberId,
            'full_name' => $request->full_name,
            'phone' => $phone,
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
