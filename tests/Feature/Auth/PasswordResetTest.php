<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_screen_can_be_rendered(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertOk();
    }

    public function test_identity_can_be_verified_with_the_correct_security_answer(): void
    {
        $user = User::factory()->create([
            'security_question' => 'favorite_sport',
            'security_answer' => Hash::make('football'),
        ]);

        $response = $this->post(route('password.verify'), [
            'email' => $user->email,
            'security_question' => 'favorite_sport',
            'security_answer' => 'Football',
        ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('password.reset'));

        $this->assertTrue(session()->has('security_reset.user_id'));
    }

    public function test_identity_verification_fails_with_the_wrong_answer(): void
    {
        $user = User::factory()->create([
            'security_question' => 'favorite_sport',
            'security_answer' => Hash::make('football'),
        ]);

        $response = $this->post(route('password.verify'), [
            'email' => $user->email,
            'security_question' => 'favorite_sport',
            'security_answer' => 'basketball',
        ]);

        $response->assertSessionHasErrors('security_answer');
        $this->assertFalse(session()->has('security_reset.user_id'));
    }

    public function test_identity_verification_fails_for_accounts_without_a_security_question_set(): void
    {
        $user = User::factory()->create([
            'security_question' => null,
            'security_answer' => null,
        ]);

        $response = $this->post(route('password.verify'), [
            'email' => $user->email,
            'security_question' => 'favorite_sport',
            'security_answer' => 'anything',
        ]);

        $response->assertSessionHasErrors('security_answer');
    }

    public function test_reset_password_screen_requires_verification_first(): void
    {
        $response = $this->get(route('password.reset'));

        $response->assertRedirect(route('password.request'));
    }

    public function test_password_can_be_reset_after_verification(): void
    {
        $user = User::factory()->create([
            'security_question' => 'favorite_sport',
            'security_answer' => Hash::make('football'),
        ]);

        $this->post(route('password.verify'), [
            'email' => $user->email,
            'security_question' => 'favorite_sport',
            'security_answer' => 'Football',
        ]);

        $response = $this->post(route('password.update'), [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login', absolute: false));

        $user->refresh();
        $this->assertTrue(Hash::check('new-password', $user->password));
    }
}
