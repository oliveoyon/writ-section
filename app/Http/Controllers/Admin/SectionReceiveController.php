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
            'barcode' => 'required|string|max:255',
            'action' => 'required|in:receive,reject',
            'reason' => 'nullable|string|max:1000',
        ]);

        $case = CourtCase::where('permanent_barcode', $request->barcode)
            ->orWhere('temporary_barcode', $request->barcode)
            ->first();

        if (!$case) {
            return back()->with('error', 'Barcode not found.');
        }

        $user = $request->user();
        $section = $this->resolveSection($user);
        $latest = $case->latestMovement;
        $fromSection = $latest?->to_section ?? $case->current_section;

        if ($request->action === 'reject' && stripos($section, 'affidavit') === false) {
            return back()->with('error', 'Reject action is only allowed in affidavit section.');
        }

        if ($request->action === 'receive' && $fromSection === $section) {
            return back()->with('error', 'This file is already in your section custody.');
        }

        DB::transaction(function () use ($request, $case, $user, $section, $fromSection) {
            if ($request->action === 'receive') {
                $case->update([
                    'status' => 'in_progress',
                    'current_section' => $section,
                    'current_holder_user_id' => $user->id,
                    'current_holder_at' => now(),
                ]);
            }

            FileMovement::create([
                'case_id' => $case->id,
                'barcode_scanned' => $request->barcode,
                'from_section' => $fromSection,
                'to_section' => $request->action === 'reject' ? $fromSection : $section,
                'movement_type' => $request->action,
                'received_by_user_id' => $user->id,
                'received_at' => now(),
                'notes' => $request->reason,
            ]);
        });

        $message = $request->action === 'receive'
            ? 'File received successfully. Custody is now under your login.'
            : 'Affidavit rejection recorded. Custody remains with previous section.';

        return back()->with('success', $message);
    }

    private function resolveSection($user): string
    {
        return $user->departmentRelation?->name
            ?? $user->department
            ?? 'Unassigned Section';
    }
}
