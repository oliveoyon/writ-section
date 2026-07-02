<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'login_id' => $user->login_id,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_admin_users_can_authenticate_with_email_and_password(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'login_id' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_card_punch_logs_active_user_in_directly(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $response = $this->post('/proximity-login', [
            'login_id' => $user->login_id,
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_card_punch_rejects_inactive_user(): void
    {
        $user = User::factory()->create([
            'is_active' => false,
        ]);

        $this->post('/proximity-login', [
            'login_id' => $user->login_id,
        ]);

        $this->assertGuest();
    }

    public function test_staff_without_recognized_department_does_not_redirect_loop_to_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'department' => null,
            'user_type' => 'staff',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'login_id' => $user->login_id,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('admin.tracking.register-report', absolute: false));
    }

    public function test_face_login_route_is_disabled(): void
    {
        $this->post('/login-face', [
            'login_id' => 'SUPER-ADMIN',
            'descriptor' => [],
        ])->assertNotFound();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'login_id' => $user->login_id,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }
}
