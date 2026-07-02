<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourtCase;
use App\Models\FileMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SectionReceiveController extends Controller
{
    public function show(Request $request)
    {
        $section = $this->resolveSection($request->user());
        $isAffidavit = stripos($section, 'affidavit') !== false;

        return view('admin.tracking.receive', compact('section', 'isAffidavit'));
    }

    public function receive(Request $request)
    {
        $request->validate([
            'barcode' => 'nullable|string|max:255',
            'barcodes' => 'nullable|string|max:20000',
            'action' => 'required|in:receive,reject',
            'reason' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();
        $section = $this->resolveSection($user);

        if ($request->action === 'reject' && stripos($section, 'affidavit') === false) {
            return back()->with('error', 'Reject action is only allowed in affidavit section.');
        }

        if ($request->action === 'reject') {
            return $this->processSingleReject($request, $user, $section);
        }

        return $this->processBatchReceive($request, $user, $section);
    }

    private function processSingleReject(Request $request, $user, string $section)
    {
        $barcode = trim((string) $request->input('barcode', ''));
        if ($barcode === '') {
            return back()->with('error', 'Barcode is required for reject action.');
        }

        $case = CourtCase::where('permanent_barcode', $barcode)
            ->orWhere('temporary_barcode', $barcode)
            ->first();

        if (!$case) {
            return back()->with('error', 'Barcode not found.');
        }

        $latest = $case->latestMovement;
        $fromSection = $latest?->to_section ?? $case->current_section;

        DB::transaction(function () use ($request, $case, $user, $fromSection, $barcode) {
            FileMovement::create([
                'case_id' => $case->id,
                'barcode_scanned' => $barcode,
                'from_section' => $fromSection,
                'to_section' => $fromSection,
                'movement_type' => 'reject',
                'received_by_user_id' => $user->id,
                'received_at' => now(),
                'notes' => $request->reason,
            ]);
        });

        return back()->with('success', 'Affidavit rejection recorded. Custody remains with previous section.');
    }

    private function processBatchReceive(Request $request, $user, string $section)
    {
        $reason = $request->reason;
        $input = trim((string) $request->input('barcodes', ''));
        if ($input === '') {
            $input = trim((string) $request->input('barcode', ''));
        }

        $barcodes = $this->extractBarcodes($input);
        if (count($barcodes) === 0) {
            return back()->with('error', 'Please scan at least one barcode.');
        }

        $received = [];
        $failed = [];

        foreach ($barcodes as $barcode) {
            $case = CourtCase::where('permanent_barcode', $barcode)
                ->orWhere('temporary_barcode', $barcode)
                ->first();

            if (!$case) {
                $failed[] = ['barcode' => $barcode, 'reason' => 'Barcode not found'];
                continue;
            }

            $latest = $case->latestMovement;
            $fromSection = $latest?->to_section ?? $case->current_section;

            if ($fromSection === $section) {
                $failed[] = ['barcode' => $barcode, 'reason' => 'Already in your section custody'];
                continue;
            }

            DB::transaction(function () use ($case, $user, $section, $fromSection, $barcode, $reason) {
                $case->update([
                    'status' => 'in_progress',
                    'current_section' => $section,
                    'current_holder_user_id' => $user->id,
                    'current_holder_at' => now(),
                ]);

                FileMovement::create([
                    'case_id' => $case->id,
                    'barcode_scanned' => $barcode,
                    'from_section' => $fromSection,
                    'to_section' => $section,
                    'movement_type' => 'receive',
                    'received_by_user_id' => $user->id,
                    'received_at' => now(),
                    'notes' => $reason,
                ]);
            });

            $received[] = [
                'barcode' => $barcode,
                'case_no' => $case->case_reference ?: ('CASE-' . $case->id),
                'from_section' => $fromSection ?: '-',
                'to_section' => $section,
            ];
        }

        $message = count($received) . ' file(s) received successfully.';
        if (count($failed) > 0) {
            $message .= ' ' . count($failed) . ' failed.';
        }

        return back()->with('success', $message)->with('receive_summary', [
            'section' => $section,
            'received_by' => $user->name ?? 'Unknown',
            'received_at' => now()->format('Y-m-d h:i A'),
            'received' => $received,
            'failed' => $failed,
        ]);
    }

    private function extractBarcodes(string $input): array
    {
        $parts = preg_split('/[\r\n,\t ]+/', $input) ?: [];
        $normalized = [];

        foreach ($parts as $part) {
            $barcode = trim($part);
            if ($barcode === '') {
                continue;
            }

            $normalized[$barcode] = $barcode;
        }

        return array_values($normalized);
    }

    private function resolveSection($user): string
    {
        return $user->departmentRelation?->name
            ?? $user->department
            ?? 'Unassigned Section';
    }
}
