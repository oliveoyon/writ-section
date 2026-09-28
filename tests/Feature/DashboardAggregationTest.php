<?php

namespace Tests\Feature;

use App\Models\CourtCase;
use App\Models\FileMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardAggregationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_conditional_aggregates_preserve_case_and_movement_totals(): void
    {
        Carbon::setTestNow('2026-09-28 12:00:00');
        $user = User::factory()->create(['user_type' => 'admin']);

        CourtCase::create(['status' => 'draft']);
        CourtCase::create(['status' => 'returned_to_lawyer']);
        CourtCase::create(['status' => 'completed', 'current_section' => 'Record Room']);
        $activeCase = CourtCase::create(['status' => 'in_progress', 'current_section' => 'Typing Section']);

        foreach (['receive', 'reject', 'dispatch_to_court', 'returned_from_court_handover', 'override_receive'] as $type) {
            FileMovement::create([
                'case_id' => $activeCase->id,
                'barcode_scanned' => '132026000001',
                'to_section' => 'Typing Section',
                'movement_type' => $type,
                'is_override' => $type === 'override_receive',
                'received_at' => now(),
            ]);
        }

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHasAll([
                'totalCases' => 4,
                'pendingCount' => 2,
                'completedCount' => 1,
                'inProgressCount' => 1,
                'todayReceived' => 1,
                'todayRejected' => 1,
                'todayCourtDispatch' => 1,
                'todayCourtReturn' => 1,
                'todayOverride' => 1,
                'totalPeriodMovements' => 5,
                'periodReceive' => 1,
                'periodReject' => 1,
                'periodCourtDispatch' => 1,
                'periodCourtReturn' => 1,
                'periodOverride' => 1,
            ]);
    }
}
