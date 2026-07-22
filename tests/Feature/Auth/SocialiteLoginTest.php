<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class SocialiteLoginTest extends TestCase
{
    use RefreshDatabase;

    private function fakeSocialiteUser(string $id, string $email, string $name = 'Jamie Doe'): SocialiteUser
    {
        return (new SocialiteUser)->map([
            'id' => $id,
            'name' => $name,
            'nickname' => 'jamie',
            'email' => $email,
            'avatar' => 'https://example.com/avatar.png',
        ]);
    }

    public function test_redirect_sends_user_to_the_provider(): void
    {
        $response = $this->get(route('social.redirect', 'google'));

        $response->assertRedirect();
        $this->assertStringContainsString('accounts.google.com', $response->headers->get('Location'));
    }

    public function test_unknown_provider_is_rejected(): void
    {
        $this->get('/auth/facebook/redirect')->assertNotFound();
    }

    public function test_callback_creates_a_new_user_as_a_pending_member_and_sends_to_onboarding(): void
    {
        $provider = $this->mock(Provider::class);
        $provider->shouldReceive('user')->andReturn($this->fakeSocialiteUser('google-123', 'new-google-user@example.com'));
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('social.callback', 'google'));

        $user = User::where('email', 'new-google-user@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame('google-123', $user->google_id);
        $this->assertSame('member', $user->role);
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('onboarding.edit'));
    }

    public function test_callback_links_provider_to_an_existing_account_with_matching_email(): void
    {
        $existing = User::factory()->create(['email' => 'already-here@example.com', 'role' => 'student']);

        $provider = $this->mock(Provider::class);
        $provider->shouldReceive('user')->andReturn($this->fakeSocialiteUser('gh-999', 'already-here@example.com'));
        Socialite::shouldReceive('driver')->with('github')->andReturn($provider);

        $response = $this->get(route('social.callback', 'github'));

        $this->assertSame(1, User::where('email', 'already-here@example.com')->count());
        $existing->refresh();
        $this->assertSame('gh-999', $existing->github_id);
        $this->assertAuthenticatedAs($existing);
        $response->assertRedirect(route('student.dashboard'));
    }

    public function test_callback_reuses_the_same_account_on_a_second_login(): void
    {
        $provider = $this->mock(Provider::class);
        $provider->shouldReceive('user')->andReturn($this->fakeSocialiteUser('google-321', 'returning@example.com'));
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get(route('social.callback', 'google'));
        $this->post(route('logout'));

        $provider2 = $this->mock(Provider::class);
        $provider2->shouldReceive('user')->andReturn($this->fakeSocialiteUser('google-321', 'returning@example.com'));
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider2);

        $this->get(route('social.callback', 'google'));

        $this->assertSame(1, User::where('email', 'returning@example.com')->count());
    }

    public function test_cancelled_provider_login_redirects_to_login_with_a_friendly_status(): void
    {
        $response = $this->get('/auth/google/callback?error=access_denied');

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', 'Google sign-in was cancelled.');
        $this->assertGuest();
    }

    public function test_provider_failure_redirects_to_login_with_an_error(): void
    {
        $provider = $this->mock(Provider::class);
        $provider->shouldReceive('user')->andThrow(new \Exception('boom'));
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('social.callback', 'google'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_onboarding_completes_role_selection_and_redirects_to_the_role_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'member', 'rules_agreed_at' => null]);

        $response = $this->actingAs($user)->patch(route('onboarding.update'), [
            'role' => 'lecturer',
            'rules_agreement' => '1',
            'security_question' => 'favorite_sport',
            'security_answer' => 'Football',
        ]);

        $user->refresh();

        $this->assertSame('lecturer', $user->role);
        $this->assertNotNull($user->rules_agreed_at);
        $this->assertNotNull($user->currentTeam);
        $this->assertSame('favorite_sport', $user->security_question);
        $this->assertTrue($user->verifySecurityAnswer('football'));
        $response->assertRedirect(route('lecturer.dashboard'));
    }

    public function test_onboarding_requires_a_valid_role_and_accepted_rules(): void
    {
        $user = User::factory()->create(['role' => 'member']);

        $response = $this->actingAs($user)->patch(route('onboarding.update'), []);

        $response->assertSessionHasErrors(['role', 'rules_agreement']);
    }
}
