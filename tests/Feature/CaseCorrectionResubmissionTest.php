<?php

namespace Tests\Feature;

use App\Models\CourtCase;
use App\Models\Department;
use App\Models\FileMovement;
use App\Models\Lawyer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaseCorrectionResubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_filing_return_and_lawyer_resubmission_keep_the_same_temp_id(): void
    {
        [$lawyerUser, $lawyer] = $this->lawyer();
        $filing = Department::create(['name' => 'Filing Section', 'display_name' => 'Filing Section']);
        $filingUser = User::factory()->create([
            'department' => $filing->id,
            'user_type' => 'staff',
        ]);
        $generatedAt = now()->subHour();
        $case = CourtCase::create([
            'lawyer_id' => $lawyer->id,
            'entry_source' => 'lawyer',
            'case_type' => 'Service Matter',
            'court_name' => 'Annex 5, Main 23',
            'status' => 'draft',
            'temporary_barcode' => 'TEMP-SAME-001',
            'temporary_barcode_generated_at' => $generatedAt,
            'current_section' => 'Filing Section',
        ]);
        $originalGeneratedAt = $case->fresh()->temporary_barcode_generated_at;

        $this->actingAs($filingUser)
            ->post(route('admin.tracking.filing.return-to-lawyer'), [
                'temporary_barcode' => 'TEMP-SAME-001',
                'return_reason' => 'Please correct the respondent information.',
            ])
            ->assertSessionHas('success');

        $case->refresh();
        $this->assertSame('returned_to_lawyer', $case->status);
        $this->assertSame('TEMP-SAME-001', $case->temporary_barcode);
        $this->assertTrue($originalGeneratedAt->equalTo($case->temporary_barcode_generated_at));

        $this->actingAs($lawyerUser)
            ->post(route('lawyer.case.resubmit', $case))
            ->assertRedirect(route('lawyer.case.summary', $case));

        $case->refresh();
        $this->assertSame('resubmitted', $case->status);
        $this->assertSame('TEMP-SAME-001', $case->temporary_barcode);
        $this->assertTrue($originalGeneratedAt->equalTo($case->temporary_barcode_generated_at));
        $this->assertSame('resubmitted', $case->latestMovement?->movement_type);
        $this->assertSame('TEMP-SAME-001', $case->latestMovement?->barcode_scanned);
    }

    public function test_affidavit_rejection_returns_to_lawyer_and_permanent_file_can_be_received_after_resubmission(): void
    {
        [$lawyerUser, $lawyer] = $this->lawyer();
        $affidavit = Department::create(['name' => 'Affidavit Section', 'display_name' => 'Affidavit Section']);
        $affidavitUser = User::factory()->create([
            'department' => $affidavit->id,
            'user_type' => 'staff',
        ]);
        $case = CourtCase::create([
            'lawyer_id' => $lawyer->id,
            'entry_source' => 'lawyer',
            'case_type' => 'Constitutional Matter',
            'court_name' => 'Annex 5, Main 23',
            'status' => 'in_progress',
            'temporary_barcode' => 'TEMP-SAME-002',
            'permanent_barcode' => '132026000222',
            'final_case_number' => 'WRPET 222/2026',
            'current_section' => 'Affidavit Section',
            'current_holder_user_id' => $affidavitUser->id,
            'current_holder_at' => now(),
        ]);
        FileMovement::create([
            'case_id' => $case->id,
            'barcode_scanned' => $case->permanent_barcode,
            'from_section' => 'Filing Section',
            'to_section' => 'Affidavit Section',
            'movement_type' => 'receive',
            'received_by_user_id' => $affidavitUser->id,
            'received_at' => now(),
        ]);

        $this->actingAs($affidavitUser)
            ->post(route('admin.tracking.section.receive.store'), [
                'action' => 'reject',
                'barcode' => $case->permanent_barcode,
                'reason' => 'Affidavit information requires correction.',
            ])
            ->assertSessionHas('success');

        $case->refresh();
        $this->assertSame('returned_to_lawyer', $case->status);
        $this->assertSame('Lawyer', $case->current_section);
        $this->assertNull($case->current_holder_user_id);
        $this->assertSame('TEMP-SAME-002', $case->temporary_barcode);
        $this->assertSame('132026000222', $case->permanent_barcode);
        $this->assertSame('Lawyer', $case->latestMovement?->to_section);

        $this->actingAs($lawyerUser)
            ->post(route('lawyer.case.resubmit', $case))
            ->assertSessionHas('success');

        $case->refresh();
        $this->assertSame('resubmitted', $case->status);
        $this->assertSame('TEMP-SAME-002', $case->temporary_barcode);
        $this->assertSame('132026000222', $case->permanent_barcode);
        $this->assertSame('resubmitted', $case->latestMovement?->movement_type);

        $this->actingAs($affidavitUser)
            ->post(route('admin.tracking.section.receive.store'), [
                'action' => 'receive',
                'barcodes' => $case->permanent_barcode,
            ])
            ->assertSessionHas('receive_summary', function (array $summary): bool {
                return count($summary['received']) === 1 && count($summary['failed']) === 0;
            });

        $case->refresh();
        $this->assertSame($affidavitUser->id, $case->current_holder_user_id);
        $this->assertSame('Affidavit Section', $case->current_section);
        $this->assertSame('TEMP-SAME-002', $case->temporary_barcode);
    }

    private function lawyer(): array
    {
        $user = User::factory()->create([
            'user_type' => 'lawyer',
            'is_active' => true,
        ]);
        $lawyer = Lawyer::create([
            'user_id' => $user->id,
            'bar_council_id' => 'SCB-CORRECTION-'.$user->id,
            'full_name' => $user->name,
            'phone' => '01700000000',
            'status' => 'active',
        ]);

        return [$user, $lawyer];
    }
}
