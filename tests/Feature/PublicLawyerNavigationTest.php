<?php

namespace Tests\Feature;

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
            ->assertSee('Login')
            ->assertDontSee('Lawyer Login');
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
}
