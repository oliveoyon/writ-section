<?php

namespace Tests\Feature;

use App\Models\CaseFile;
use App\Models\CourtCase;
use App\Models\Lawyer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CaseFileSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_case_file_is_available_to_its_lawyer_but_not_another_lawyer(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        [$ownerUser, $owner] = $this->createLawyer('owner@example.test', 'SCB-OWNER');
        [$otherUser] = $this->createLawyer('other@example.test', 'SCB-OTHER');

        $case = CourtCase::create([
            'lawyer_id' => $owner->id,
            'entry_source' => 'lawyer',
            'case_type' => 'Constitutional Matter',
            'status' => 'draft',
            'temporary_barcode' => 'TEMP-PRIVATE-FILE-1',
        ]);

        $path = 'case_files/private-test.pdf';
        Storage::disk('local')->put($path, '%PDF-1.4 test');
        $file = CaseFile::create([
            'case_id' => $case->id,
            'file_path' => $path,
            'original_name' => 'petition.pdf',
            'file_type' => 'application/pdf',
            'size' => 13,
        ]);

        Storage::disk('public')->assertMissing($path);

        $this->actingAs($ownerUser)
            ->get(route('lawyer.case.file', [$case, $file]))
            ->assertOk();

        $this->actingAs($otherUser)
            ->get(route('lawyer.case.file', [$case, $file]))
            ->assertForbidden();

        $this->actingAs($otherUser)->get(route('lawyer.case.summary', $case))->assertForbidden();
        $this->actingAs($otherUser)->get(route('lawyer.case.edit', $case))->assertForbidden();
        $this->actingAs($otherUser)->get(route('lawyer.case.top_sheet', $case))->assertForbidden();
        $this->actingAs($otherUser)->post(route('lawyer.case.resubmit', $case))->assertForbidden();
        $this->actingAs($otherUser)->delete(route('lawyer.case.destroy', $case))->assertForbidden();
        $this->actingAs($otherUser)->put(route('lawyer.case.update', $case), [
            'case_type' => 'Others Matter',
            'petitioners' => [['name_or_organization' => 'Tampered Petitioner']],
            'respondents' => [['name_or_organization' => 'Tampered Respondent']],
        ])->assertForbidden();

        $this->assertSame('Constitutional Matter', $case->fresh()->case_type);

        $otherCase = CourtCase::create([
            'lawyer_id' => $owner->id,
            'entry_source' => 'lawyer',
            'case_type' => 'Service Matter',
            'status' => 'draft',
            'temporary_barcode' => 'TEMP-PRIVATE-FILE-2',
        ]);

        $this->actingAs($ownerUser)
            ->get(route('lawyer.case.file', [$otherCase, $file]))
            ->assertNotFound();
    }

    private function createLawyer(string $email, string $memberId): array
    {
        $user = User::factory()->create([
            'email' => $email,
            'user_type' => 'lawyer',
            'is_active' => true,
        ]);

        $lawyer = Lawyer::create([
            'user_id' => $user->id,
            'bar_council_id' => $memberId,
            'full_name' => $user->name,
            'phone' => '01700000000',
            'status' => 'active',
        ]);

        return [$user, $lawyer];
    }
}
