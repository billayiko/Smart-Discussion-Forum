<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_redirects_a_student_to_the_student_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'student', 'password' => bcrypt('password')]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('student.dashboard', absolute: false));
    }

    public function test_login_redirects_a_lecturer_to_the_lecturer_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'lecturer', 'password' => bcrypt('password')]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('lecturer.dashboard', absolute: false));
    }

    public function test_login_redirects_an_admin_to_the_admin_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'admin', 'password' => bcrypt('password')]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_a_fresh_login_after_closing_the_browser_still_lands_on_the_role_dashboard(): void
    {
        // Simulates closing the browser (no prior "intended" URL in the session)
        // and logging back in from scratch.
        $user = User::factory()->create(['role' => 'lecturer', 'password' => bcrypt('password')]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('lecturer.dashboard', absolute: false));

        $dashboard = $this->get(route('lecturer.dashboard'));
        $dashboard->assertOk();
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('student.dashboard'));

        $response->assertRedirect(route('login'));
    }
}
