<?php

namespace Tests\Feature;

use App\Models\Court;
use App\Models\CourtCase;
use App\Models\FileMovement;
use App\Models\Lawyer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicLawyerNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_find_plain_login_link_on_homepage(): void
    {
        $this->get(route('web.home'))
            ->assertOk()
            ->assertSee(route('lawyer.login'), false)
            ->assertSee('Login');
    }

    public function test_expired_lawyer_session_is_sent_to_lawyer_login(): void
    {
        $this->get(route('lawyer.dashboard'))
            ->assertRedirect(route('lawyer.login'));
    }

    public function test_authenticated_lawyer_is_redirected_to_dashboard_from_public_pages(): void
    {
        $lawyer = User::factory()->create([
            'user_type' => 'lawyer',
            'is_active' => true,
        ]);

        $this->actingAs($lawyer)
            ->get(route('web.home'))
            ->assertRedirect(route('lawyer.dashboard'));

        $this->actingAs($lawyer)
            ->get(route('lawyer.login'))
            ->assertRedirect(route('lawyer.dashboard'));
    }

    public function test_inactive_lawyer_cannot_log_in(): void
    {
        $lawyer = User::factory()->create([
            'user_type' => 'lawyer',
            'is_active' => false,
        ]);

        $this->post(route('lawyer.login.submit'), [
            'email_or_phone' => $lawyer->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email_or_phone');

        $this->assertGuest();
    }

    public function test_active_lawyer_can_log_in(): void
    {
        $lawyer = User::factory()->create([
            'user_type' => 'lawyer',
            'is_active' => true,
        ]);

        $this->post(route('lawyer.login.submit'), [
            'email_or_phone' => $lawyer->email,
            'password' => 'password',
        ])->assertRedirect(route('lawyer.dashboard'));

        $this->assertAuthenticatedAs($lawyer);
    }

    public function test_lawyer_case_list_shows_the_current_tracking_desk(): void
    {
        $user = User::factory()->create([
            'user_type' => 'lawyer',
            'is_active' => true,
        ]);
        $lawyer = Lawyer::create([
            'user_id' => $user->id,
            'full_name' => 'Tracking Test Lawyer',
            'phone' => '01700000000',
            'status' => 'active',
        ]);
        CourtCase::create([
            'lawyer_id' => $lawyer->id,
            'entry_source' => 'lawyer',
            'case_type' => 'Service Matter',
            'status' => 'in_progress',
            'current_section' => 'Typing Section',
        ]);
        CourtCase::create([
            'lawyer_id' => $lawyer->id,
            'entry_source' => 'lawyer',
            'case_type' => 'PIL Matter',
            'status' => 'draft',
            'current_section' => null,
        ]);
        $court = Court::create([
            'name_en' => 'Appellate Court No. 2',
            'name_bn' => 'আপিল আদালত নং ২',
            'code' => 'LAWYER-PLACE-COURT',
            'is_active' => true,
        ]);
        $courtCase = CourtCase::create([
            'lawyer_id' => $lawyer->id,
            'entry_source' => 'lawyer',
            'case_type' => 'Constitutional Matter',
            'status' => 'in_progress',
            'permanent_barcode' => '132026000099',
            'final_case_number' => 'WRPET 99/2026',
            'current_section' => 'Court',
        ]);
        FileMovement::create([
            'case_id' => $courtCase->id,
            'court_id' => $court->id,
            'barcode_scanned' => $courtCase->permanent_barcode,
            'from_section' => 'Dealing Assistant',
            'to_section' => 'Court',
            'movement_type' => 'dispatch_to_court',
            'received_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('lawyer.my_cases'))
            ->assertOk()
            ->assertSee('Current Place')
            ->assertSee('Typing Section')
            ->assertSee("Lawyer's Desk")
            ->assertSee('Court (Appellate Court No. 2)');
    }
}
