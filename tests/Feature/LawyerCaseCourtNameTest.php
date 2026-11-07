<?php

namespace Tests\Feature;

use App\Models\CourtCase;
use App\Models\Lawyer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LawyerCaseCourtNameTest extends TestCase
{
    use RefreshDatabase;

    public function test_court_name_is_required_when_lawyer_creates_a_case(): void
    {
        [$user] = $this->lawyer();

        $this->actingAs($user)
            ->post(route('lawyer.case.store'), $this->casePayload(['court_name' => '']))
            ->assertSessionHasErrors('court_name');

        $this->assertDatabaseCount('cases', 0);
    }

    public function test_lawyer_can_create_and_edit_case_court_name(): void
    {
        [$user] = $this->lawyer();

        $this->actingAs($user)
            ->post(route('lawyer.case.store'), $this->casePayload())
            ->assertRedirect();

        $case = CourtCase::firstOrFail();
        $this->assertSame('Annex 5, Main 23', $case->court_name);

        $this->actingAs($user)
            ->get(route('lawyer.case.edit', $case))
            ->assertOk()
            ->assertSee('value="Annex 5, Main 23"', false);

        $this->actingAs($user)
            ->put(route('lawyer.case.update', $case), $this->casePayload([
                'court_name' => 'Annex 7, Main 12',
            ]))
            ->assertRedirect(route('lawyer.case.summary', $case));

        $this->assertSame('Annex 7, Main 12', $case->fresh()->court_name);
    }

    public function test_top_sheet_places_court_name_below_respondents_table(): void
    {
        [$user, $lawyer] = $this->lawyer();
        $case = CourtCase::create([
            'lawyer_id' => $lawyer->id,
            'entry_source' => 'lawyer',
            'case_type' => 'Constitutional Matter',
            'court_name' => 'Annex 5, Main 23',
            'status' => 'draft',
            'temporary_barcode' => 'TEMP-COURT-NAME-1',
        ]);
        $case->petitioners()->create(['name_or_organization' => 'Petitioner One']);
        $case->respondents()->create(['name_or_organization' => 'Respondent One']);
        $case->load(['lawyer', 'petitioners', 'respondents']);

        $html = view('website.lawyer.top_sheet', [
            'case' => $case,
            'barcode' => base64_encode('barcode'),
        ])->render();

        $respondentPosition = strpos($html, 'Respondent One');
        $courtPosition = strpos($html, 'Annex 5, Main 23');

        $this->assertNotFalse($respondentPosition);
        $this->assertNotFalse($courtPosition);
        $this->assertGreaterThan($respondentPosition, $courtPosition);

        $this->actingAs($user)
            ->get(route('lawyer.case.summary', $case))
            ->assertOk()
            ->assertSee('Annex 5, Main 23');
    }

    private function lawyer(): array
    {
        $user = User::factory()->create([
            'user_type' => 'lawyer',
            'is_active' => true,
        ]);
        $lawyer = Lawyer::create([
            'user_id' => $user->id,
            'bar_council_id' => 'SCB-COURT-NAME-'.$user->id,
            'full_name' => $user->name,
            'phone' => '01700000000',
            'status' => 'active',
        ]);

        return [$user, $lawyer];
    }

    private function casePayload(array $overrides = []): array
    {
        return array_replace([
            'case_type' => 'Constitutional Matter',
            'description' => 'Constitutional petition description',
            'court_name' => 'Annex 5, Main 23',
            'petitioners' => [[
                'name_or_organization' => 'Petitioner One',
                'represented_by' => null,
                'designation' => null,
                'address' => 'Dhaka',
            ]],
            'respondents' => [[
                'name_or_organization' => 'Respondent One',
                'represented_by' => null,
                'designation' => null,
                'address' => 'Dhaka',
            ]],
        ], $overrides);
    }
}
