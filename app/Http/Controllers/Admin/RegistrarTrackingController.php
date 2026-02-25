<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourtCase;
use App\Models\FileMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistrarTrackingController extends Controller
{
    public function lookup(Request $request)
    {
        $case = null;

        if ($request->filled('q')) {
            $query = trim((string) $request->q);
            $case = CourtCase::with(['latestMovement.receivedBy', 'currentHolder'])
                ->where('temporary_barcode', $query)
                ->orWhere('permanent_barcode', $query)
                ->orWhere('final_case_number', $query)
                ->first();
        }

        return view('admin.tracking.lookup', compact('case'));
    }

    public function timeline(CourtCase $case)
    {
        $movements = $case->movements()->with('receivedBy')->orderBy('received_at', 'asc')->get();
        return view('admin.tracking.timeline', compact('case', 'movements'));
    }

    public function overrideReceive(Request $request, CourtCase $case)
    {
        $request->validate([
            'to_section' => 'required|string|max:255',
            'reason' => 'required|string|max:1000',
        ]);

        $user = $request->user();
        $latest = $case->latestMovement;
        $fromSection = $latest?->to_section ?? $case->current_section;

        DB::transaction(function () use ($request, $case, $user, $fromSection) {
            $case->update([
                'current_section' => $request->to_section,
                'current_holder_user_id' => $user->id,
                'current_holder_at' => now(),
            ]);

            FileMovement::create([
                'case_id' => $case->id,
                'barcode_scanned' => $case->permanent_barcode ?? $case->temporary_barcode,
                'from_section' => $fromSection,
                'to_section' => $request->to_section,
                'movement_type' => 'override_receive',
                'received_by_user_id' => $user->id,
                'received_at' => now(),
                'notes' => 'Registrar override performed.',
                'is_override' => true,
                'override_reason' => $request->reason,
            ]);
        });

        return redirect()->route('admin.tracking.timeline', $case)->with('success', 'Override recorded with audit reason.');
    }
}
