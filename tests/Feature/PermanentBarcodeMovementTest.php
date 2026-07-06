<?php

namespace Tests\Feature;

use App\Models\Court;
use App\Models\CourtCase;
use App\Models\Department;
use App\Models\FileMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermanentBarcodeMovementTest extends TestCase
{
    use RefreshDatabase;

    public function test_section_receive_rejects_temporary_barcode_and_accepts_permanent_barcode(): void
    {
        $department = Department::create(['name' => 'Typing Section', 'display_name' => 'Typing Section']);
        $user = User::factory()->create([
            'department' => $department->id,
            'user_type' => 'staff',
        ]);
        $temporaryCase = $this->createCase(['temporary_barcode' => 'TEMP-ONLY-001']);
        $permanentCase = $this->createCase([
            'temporary_barcode' => 'TEMP-OLD-002',
            'permanent_barcode' => '132026000002',
            'final_case_number' => 'WRPET 2/2026',
            'status' => 'filed',
        ]);

        $this->actingAs($user)
            ->getJson(route('admin.tracking.movement.validate-identifier', [
                'identifier' => $permanentCase->final_case_number,
            ]))
            ->assertOk()
            ->assertJson([
                'valid' => true,
                'permanent_barcode' => $permanentCase->permanent_barcode,
            ]);

        $this->actingAs($user)
            ->getJson(route('admin.tracking.movement.validate-identifier', [
                'identifier' => $temporaryCase->temporary_barcode,
            ]))
            ->assertUnprocessable()
            ->assertJson(['valid' => false]);

        $this->actingAs($user)->post(route('admin.tracking.section.receive.store'), [
            'action' => 'receive',
            'barcodes' => $temporaryCase->temporary_barcode,
        ])->assertSessionHas('receive_summary', function (array $summary): bool {
            return count($summary['received']) === 0
                && $summary['failed'][0]['reason'] === 'Temporary barcode is not accepted. Complete filing first.';
        });

        $this->assertNull($temporaryCase->fresh()->current_holder_user_id);
        $this->assertDatabaseMissing('file_movements', ['case_id' => $temporaryCase->id]);

        $this->actingAs($user)->post(route('admin.tracking.section.receive.store'), [
            'action' => 'receive',
            'barcodes' => $permanentCase->final_case_number,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('cases', [
            'id' => $permanentCase->id,
            'current_section' => 'Typing Section',
            'current_holder_user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('file_movements', [
            'case_id' => $permanentCase->id,
            'barcode_scanned' => $permanentCase->permanent_barcode,
            'movement_type' => 'receive',
        ]);
    }

    public function test_court_dispatch_rejects_temporary_barcode(): void
    {
        $department = Department::create(['name' => 'Office Assistant', 'display_name' => 'Office Assistant']);
        $user = User::factory()->create([
            'department' => $department->id,
            'user_type' => 'staff',
        ]);
        $court = Court::create([
            'name_en' => 'Test Court',
            'code' => 'TEST-COURT',
            'is_active' => true,
        ]);
        $case = $this->createCase(['temporary_barcode' => 'TEMP-COURT-001']);

        $this->actingAs($user)->post(route('admin.tracking.court.dispatch.store'), [
            'court_id' => $court->id,
            'barcodes' => $case->temporary_barcode,
        ])->assertSessionHas('error');

        $this->assertSame('Filing Section', $case->fresh()->current_section);
        $this->assertDatabaseMissing('file_movements', ['case_id' => $case->id]);
    }

    public function test_office_assistant_can_receive_permanent_file_from_filing(): void
    {
        $department = Department::create(['name' => 'Office Assistant', 'display_name' => 'Office Assistant']);
        $user = User::factory()->create([
            'department' => $department->id,
            'user_type' => 'staff',
        ]);
        $case = $this->createCase([
            'permanent_barcode' => '132026000003',
            'status' => 'filed',
        ]);

        $this->actingAs($user)
            ->get(route('admin.tracking.section.receive'))
            ->assertOk()
            ->assertSee('Office Assistant');

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.tracking.section.receive'));

        $this->actingAs($user)->post(route('admin.tracking.section.receive.store'), [
            'action' => 'receive',
            'barcodes' => $case->permanent_barcode,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('cases', [
            'id' => $case->id,
            'current_section' => 'Office Assistant',
            'current_holder_user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('file_movements', [
            'case_id' => $case->id,
            'from_section' => 'Filing Section',
            'to_section' => 'Office Assistant',
            'movement_type' => 'receive',
            'received_by_user_id' => $user->id,
        ]);
    }

    public function test_same_holder_cannot_receive_again_but_colleague_can_take_custody(): void
    {
        $department = Department::create(['name' => 'Typing Section', 'display_name' => 'Typing Section']);
        $currentHolder = User::factory()->create([
            'department' => $department->id,
            'user_type' => 'staff',
        ]);
        $colleague = User::factory()->create([
            'department' => $department->id,
            'user_type' => 'staff',
        ]);
        $case = $this->createCase([
            'permanent_barcode' => '132026000005',
            'status' => 'in_progress',
            'current_section' => 'Typing Section',
            'current_holder_user_id' => $currentHolder->id,
            'current_holder_at' => now(),
        ]);

        $this->actingAs($currentHolder)->post(route('admin.tracking.section.receive.store'), [
            'action' => 'receive',
            'barcodes' => $case->permanent_barcode,
        ])->assertSessionHas('receive_summary', function (array $summary): bool {
            return count($summary['received']) === 0
                && $summary['failed'][0]['reason'] === 'This file is already in your custody.';
        });

        $this->assertDatabaseMissing('file_movements', ['case_id' => $case->id]);

        $this->actingAs($colleague)->post(route('admin.tracking.section.receive.store'), [
            'action' => 'receive',
            'barcodes' => $case->permanent_barcode,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('cases', [
            'id' => $case->id,
            'current_section' => 'Typing Section',
            'current_holder_user_id' => $colleague->id,
        ]);
        $this->assertDatabaseHas('file_movements', [
            'case_id' => $case->id,
            'from_section' => 'Typing Section',
            'to_section' => 'Typing Section',
            'movement_type' => 'receive',
            'received_by_user_id' => $colleague->id,
        ]);
    }

    public function test_office_assistant_becomes_holder_when_receiving_file_from_court(): void
    {
        $department = Department::create(['name' => 'Office Assistant', 'display_name' => 'Office Assistant']);
        $user = User::factory()->create([
            'department' => $department->id,
            'user_type' => 'staff',
        ]);
        $court = Court::create([
            'name_en' => 'Test Court',
            'code' => 'RETURN-COURT',
            'is_active' => true,
        ]);
        $case = $this->createCase([
            'permanent_barcode' => '132026000004',
            'status' => 'in_progress',
            'current_section' => 'Court',
        ]);

        FileMovement::create([
            'case_id' => $case->id,
            'court_id' => $court->id,
            'barcode_scanned' => $case->permanent_barcode,
            'from_section' => 'Office Assistant',
            'to_section' => 'Court',
            'movement_type' => 'dispatch_to_court',
            'received_by_user_id' => $user->id,
            'received_at' => now(),
        ]);

        $this->actingAs($user)->post(route('admin.tracking.section.receive.store'), [
            'action' => 'receive',
            'barcodes' => $case->permanent_barcode,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('cases', [
            'id' => $case->id,
            'current_section' => 'Office Assistant',
            'current_holder_user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('file_movements', [
            'case_id' => $case->id,
            'from_section' => 'Court',
            'to_section' => 'Office Assistant',
            'movement_type' => 'returned_from_court_handover',
            'received_by_user_id' => $user->id,
        ]);
    }

    public function test_unfiled_case_cannot_be_overridden(): void
    {
        $registrar = Department::create([
            'name' => 'Assistant Registrar Office',
            'display_name' => 'Assistant Registrar Office',
        ]);
        $destination = Department::create(['name' => 'Record Room', 'display_name' => 'Record Room']);
        $user = User::factory()->create([
            'department' => $registrar->id,
            'user_type' => 'staff',
        ]);
        $case = $this->createCase(['temporary_barcode' => 'TEMP-OVERRIDE-001']);

        $this->actingAs($user)
            ->get(route('admin.tracking.timeline', $case))
            ->assertOk()
            ->assertSee('Complete filing and generate the permanent barcode before moving this file.')
            ->assertDontSee('name="to_department_id"', false);

        $this->actingAs($user)->post(route('admin.tracking.override', $case), [
            'to_department_id' => $destination->id,
            'reason' => 'incorrect_section',
        ])->assertSessionHas('error');

        $this->assertSame('Filing Section', $case->fresh()->current_section);
        $this->assertSame(0, FileMovement::where('case_id', $case->id)->count());
    }

    private function createCase(array $attributes = []): CourtCase
    {
        return CourtCase::create(array_merge([
            'entry_source' => 'lawyer',
            'case_type' => 'Writ',
            'subject' => 'Permanent barcode movement test',
            'status' => 'draft',
            'current_section' => 'Filing Section',
        ], $attributes));
    }
}
