<?php

namespace Tests\Feature;

use App\Models\Lawyer;
use App\Models\User;
use App\Notifications\LawyerActivationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class LawyerEmailActivationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_an_inactive_lawyer_and_sends_activation_email(): void
    {
        Notification::fake();

        $response = $this->post(route('lawyer.register.submit'), [
            'full_name' => 'Email Activation Lawyer',
            'phone' => '01700000001',
            'email' => 'activation@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response->assertRedirect(route('lawyer.login'))
            ->assertSessionHas('success');

        $user = User::where('email', 'activation@example.com')->firstOrFail();

        $this->assertFalse((bool) $user->is_active);
        $this->assertNull($user->email_verified_at);
        $this->assertDatabaseHas('lawyers', ['user_id' => $user->id]);
        Notification::assertSentTo($user, LawyerActivationNotification::class);
    }

    public function test_valid_signed_link_verifies_and_activates_lawyer(): void
    {
        [$user] = $this->inactiveLawyer();
        $url = (new LawyerActivationNotification)->toMail($user)->actionUrl;

        $this->get($url)
            ->assertRedirect(route('lawyer.login'))
            ->assertSessionHas('success');

        $user->refresh();

        $this->assertTrue((bool) $user->is_active);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_expired_activation_link_is_rejected(): void
    {
        [$user] = $this->inactiveLawyer();
        $url = URL::temporarySignedRoute('lawyer.activate', now()->subMinute(), [
            'user' => $user->id,
            'hash' => sha1((string) $user->email),
        ]);

        $this->get($url)->assertForbidden();

        $this->assertFalse((bool) $user->fresh()->is_active);
    }

    public function test_activation_link_cannot_activate_a_staff_account(): void
    {
        $user = User::factory()->unverified()->create([
            'user_type' => 'staff',
            'is_active' => false,
        ]);

        $this->get($this->activationUrl($user))->assertNotFound();

        $this->assertFalse((bool) $user->fresh()->is_active);
    }

    public function test_inactive_lawyer_can_request_another_activation_email(): void
    {
        Notification::fake();
        [$user] = $this->inactiveLawyer();

        $this->post(route('lawyer.activation.resend'), ['email' => $user->email])
            ->assertSessionHas('success');

        Notification::assertSentTo($user, LawyerActivationNotification::class);
    }

    public function test_resend_does_not_reveal_whether_an_email_exists(): void
    {
        Notification::fake();

        $this->post(route('lawyer.activation.resend'), ['email' => 'missing@example.com'])
            ->assertSessionHas('success');

        Notification::assertNothingSent();
    }

    private function inactiveLawyer(): array
    {
        $user = User::factory()->unverified()->create([
            'user_type' => 'lawyer',
            'is_active' => false,
        ]);
        $lawyer = Lawyer::create([
            'user_id' => $user->id,
            'full_name' => $user->name,
            'phone' => '01700000002',
            'status' => 'active',
        ]);

        return [$user, $lawyer];
    }

    private function activationUrl(User $user): string
    {
        return URL::temporarySignedRoute('lawyer.activate', now()->addHour(), [
            'user' => $user->id,
            'hash' => sha1((string) $user->email),
        ]);
    }
}
