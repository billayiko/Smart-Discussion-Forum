<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'student',
            'rules_agreement' => '1',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('student.dashboard', absolute: false));

        $this->assertAuthenticated();
        $this->assertNotNull($user->rules_agreed_at);
    }

    public function test_registration_is_declined_without_agreeing_to_the_rules(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'student',
        ]);

        $response->assertSessionHasErrors('rules_agreement');
        $this->assertGuest();
        $this->assertNull(User::where('email', 'test@example.com')->first());
    }

    public function test_users_cannot_self_register_as_admin(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'admin',
            'rules_agreement' => '1',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertGuest();
        $this->assertNull(User::where('email', 'test@example.com')->first());
    }
}
