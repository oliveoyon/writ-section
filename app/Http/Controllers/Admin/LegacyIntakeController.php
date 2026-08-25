<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourtCase;
use App\Models\FileMovement;
use App\Services\RtftsCaseReference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LegacyIntakeController extends Controller
{
    public function show(Request $request): View
    {
        $section = $this->resolveSection($request->user());

        abort_if($section === '', 403, 'Department is not assigned.');

        return view('admin.tracking.legacy-intake', [
            'section' => $section,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $parsed = RtftsCaseReference::parseIdentifier($validated['identifier']);
        if (!$parsed) {
            return back()
                ->withInput()
                ->with('error', 'Invalid RTFTS barcode or case number. Please scan/write like 132026004788 or WRPET 4788/2026.');
        }

        $user = $request->user();
        $section = $this->resolveSection($user);

        if ($section === '') {
            return back()->with('error', 'Department is not assigned.');
        }

        $result = DB::transaction(function () use ($parsed, $user, $section, $validated) {
            $case = $this->findExistingCase($parsed);
            $now = now();

            if (!$case) {
                $case = CourtCase::create([
                    'initiated_by_user_id' => $user->id,
                    'entry_source' => 'legacy',
                    'status' => 'in_progress',
                    'permanent_barcode' => $parsed['barcode'],
                    'permanent_barcode_generated_at' => $now,
                    'final_case_number' => $parsed['reference'],
                    'final_case_year' => $parsed['year'],
                    'registration_serial' => $parsed['serial'],
                    'current_section' => $section,
                    'current_holder_user_id' => $user->id,
                    'current_holder_at' => $now,
                ]);

                FileMovement::create([
                    'case_id' => $case->id,
                    'barcode_scanned' => $parsed['input'],
                    'from_section' => 'Old Case Record',
                    'to_section' => $section,
                    'movement_type' => 'legacy_intake',
                    'received_by_user_id' => $user->id,
                    'received_at' => $now,
                    'notes' => $validated['notes'] ?: 'Old case received by scan.',
                ]);

                $this->syncRegistrationSequence($parsed['year'], $parsed['serial']);

                return [
                    'status' => 'created',
                    'case' => $case->fresh(['currentHolder']),
                    'from_section' => 'Old Case Record',
                    'to_section' => $section,
                ];
            }

            $fromSection = $case->latestMovement?->to_section ?? $case->current_section ?? 'Existing Record';

            if ((int) $case->current_holder_user_id === (int) $user->id) {
                return [
                    'status' => 'same_holder',
                    'case' => $case->fresh(['currentHolder']),
                    'from_section' => $fromSection,
                    'to_section' => $section,
                ];
            }

            $case->update([
                'status' => 'in_progress',
                'current_section' => $section,
                'current_holder_user_id' => $user->id,
                'current_holder_at' => $now,
            ]);

            FileMovement::create([
                'case_id' => $case->id,
                'barcode_scanned' => $parsed['input'],
                'from_section' => $fromSection,
                'to_section' => $section,
                'movement_type' => 'legacy_receive',
                'received_by_user_id' => $user->id,
                'received_at' => $now,
                'notes' => $validated['notes'] ?: 'Existing case received by scan.',
            ]);

            return [
                'status' => 'received',
                'case' => $case->fresh(['currentHolder']),
                'from_section' => $fromSection,
                'to_section' => $section,
            ];
        });

        $message = match ($result['status']) {
            'created' => 'Old case added and received.',
            'received' => 'Existing file received into your custody.',
            default => 'This file is already in your custody.',
        };

        return back()->with('success', $message)->with('legacy_intake_result', [
            'status' => $result['status'],
            'case_no' => $result['case']->case_reference,
            'barcode' => $result['case']->permanent_barcode,
            'from_section' => $result['from_section'],
            'to_section' => $result['to_section'],
            'received_by' => $user->name,
            'received_at' => now()->format('Y-m-d h:i A'),
        ]);
    }

    private function findExistingCase(array $parsed): ?CourtCase
    {
        return CourtCase::query()
            ->with(['latestMovement', 'currentHolder'])
            ->where('permanent_barcode', $parsed['barcode'])
            ->orWhere(function ($query) use ($parsed) {
                $query->where('final_case_year', $parsed['year'])
                    ->where('registration_serial', $parsed['serial']);
            })
            ->orWhere('final_case_number', $parsed['reference'])
            ->first();
    }

    private function syncRegistrationSequence(string $year, int $serial): void
    {
        DB::table('case_registration_sequences')->insertOrIgnore([
            'year' => $year,
            'last_serial' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $current = DB::table('case_registration_sequences')
            ->where('year', $year)
            ->lockForUpdate()
            ->value('last_serial');

        if ((int) $current < $serial) {
            DB::table('case_registration_sequences')
                ->where('year', $year)
                ->update([
                    'last_serial' => $serial,
                    'updated_at' => now(),
                ]);
        }
    }

    private function resolveSection($user): string
    {
        return trim((string) (
            $user->departmentRelation?->name
            ?? $user->department
            ?? ''
        ));
    }
}
