<?php

namespace Tests\Feature;

use App\Models\Court;
use App\Models\CourtCase;
use App\Models\CourtDispatchBatch;
use App\Models\CourtDispatchBatchItem;
use App\Models\Department;
use App\Models\FileMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourtBatchManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_see_only_their_own_batches_while_admin_sees_all(): void
    {
        $office = Department::create(['name' => 'Office Assistant', 'display_name' => 'Office Assistant']);
        $registrar = Department::create(['name' => 'Assistant Registrar Office', 'display_name' => 'Assistant Registrar Office']);
        $staff = User::factory()->create(['department' => $office->id, 'user_type' => 'staff']);
        $otherStaff = User::factory()->create(['department' => $office->id, 'user_type' => 'staff']);
        $admin = User::factory()->create(['department' => $registrar->id, 'user_type' => 'admin']);
        $court = $this->createCourt();
        $ownBatch = $this->createBatch($court, $staff, 'DSP-OWN-001');
        $otherBatch = $this->createBatch($court, $otherStaff, 'DSP-OTHER-001');

        $this->actingAs($staff)->get(route('admin.tracking.court.batches.index'))
            ->assertOk()
            ->assertSee($ownBatch->batch_no)
            ->assertDontSee($otherBatch->batch_no);

        $this->actingAs($staff)->get(route('admin.tracking.court.batches.show', $otherBatch))->assertForbidden();

        $this->actingAs($admin)->get(route('admin.tracking.court.batches.index'))
            ->assertOk()
            ->assertSee($ownBatch->batch_no)
            ->assertSee($otherBatch->batch_no);
    }

    public function test_batch_detail_marks_a_dispatched_file_as_returned_from_movement_history(): void
    {
        $office = Department::create(['name' => 'Office Assistant', 'display_name' => 'Office Assistant']);
        $staff = User::factory()->create(['department' => $office->id, 'user_type' => 'staff']);
        $court = $this->createCourt();
        $case = CourtCase::create([
            'entry_source' => 'filing',
            'case_type' => 'Writ',
            'subject' => 'Batch return status',
            'status' => 'in_progress',
            'permanent_barcode' => '132026000099',
            'final_case_number' => 'WRPET 99/2026',
            'current_section' => 'Office Assistant',
        ]);
        $batch = $this->createBatch($court, $staff, 'DSP-RETURNED-001');
        $processedAt = now()->subHour();

        CourtDispatchBatchItem::create([
            'batch_id' => $batch->id,
            'case_id' => $case->id,
            'barcode_scanned' => $case->permanent_barcode,
            'from_section' => 'Office Assistant',
            'to_section' => 'Court',
            'processed_at' => $processedAt,
        ]);
        FileMovement::create([
            'case_id' => $case->id,
            'court_id' => $court->id,
            'barcode_scanned' => $case->permanent_barcode,
            'from_section' => 'Court',
            'to_section' => 'Office Assistant',
            'movement_type' => 'returned_from_court_handover',
            'received_by_user_id' => $staff->id,
            'received_at' => now(),
        ]);

        $this->actingAs($staff)->get(route('admin.tracking.court.batches.show', $batch))
            ->assertOk()
            ->assertSee('WRPET 99/2026')
            ->assertSee('Returned');
    }

    private function createCourt(): Court
    {
        return Court::create(['name_en' => 'Test Court', 'code' => 'BATCH-COURT', 'is_active' => true]);
    }

    private function createBatch(Court $court, User $creator, string $number): CourtDispatchBatch
    {
        return CourtDispatchBatch::create([
            'batch_no' => $number,
            'court_id' => $court->id,
            'created_by_user_id' => $creator->id,
            'type' => 'dispatch',
            'dispatched_at' => now(),
        ]);
    }
}
