<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_promote_a_member_to_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lecturer = User::factory()->create(['role' => 'lecturer']);

        $response = $this->actingAs($admin)->patch(route('admin.members.role', $lecturer), [
            'role' => 'admin',
        ]);

        $response->assertRedirect();
        $this->assertSame('admin', $lecturer->fresh()->role);
    }

    public function test_promoted_member_can_access_admin_routes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($admin)->patch(route('admin.members.role', $student), [
            'role' => 'admin',
        ]);

        $this->actingAs($student->fresh())
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_admin_cannot_change_their_own_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->patch(route('admin.members.role', $admin), [
            'role' => 'student',
        ]);

        $response->assertStatus(422);
        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_non_admin_cannot_change_roles(): void
    {
        $lecturer = User::factory()->create(['role' => 'lecturer']);
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($lecturer)->patch(route('admin.members.role', $student), [
            'role' => 'admin',
        ]);

        $response->assertForbidden();
        $this->assertSame('student', $student->fresh()->role);
    }

    public function test_admin_can_demote_another_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $otherAdmin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->patch(route('admin.members.role', $otherAdmin), [
            'role' => 'lecturer',
        ]);

        $response->assertRedirect();
        $this->assertSame('lecturer', $otherAdmin->fresh()->role);
    }

    public function test_the_sole_admin_cannot_lock_the_system_out_of_admins(): void
    {
        // With only one admin in the system, the only person who could act on
        // this route is that admin, and the self-role-change guard blocks
        // them from demoting themselves — so the admin count can never hit zero.
        $soleAdmin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($soleAdmin)->patch(route('admin.members.role', $soleAdmin), [
            'role' => 'lecturer',
        ]);

        $response->assertStatus(422);
        $this->assertSame(1, User::where('role', 'admin')->count());
    }
}
