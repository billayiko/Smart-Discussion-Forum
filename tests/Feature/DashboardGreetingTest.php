<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardGreetingTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_student_dashboard_greets_according_to_the_time_of_day(): void
    {
        $student = User::factory()->create(['role' => 'student', 'name' => 'Ada Lovelace']);

        Carbon::setTestNow(Carbon::parse('08:00'));
        $this->actingAs($student)->get(route('student.dashboard'))->assertSeeText('Good morning, Ada');

        Carbon::setTestNow(Carbon::parse('14:00'));
        $this->actingAs($student)->get(route('student.dashboard'))->assertSeeText('Good afternoon, Ada');

        Carbon::setTestNow(Carbon::parse('20:00'));
        $this->actingAs($student)->get(route('student.dashboard'))->assertSeeText('Good evening, Ada');
    }

    public function test_lecturer_dashboard_greets_according_to_the_time_of_day(): void
    {
        $lecturer = User::factory()->create(['role' => 'lecturer', 'name' => 'Grace Hopper']);

        Carbon::setTestNow(Carbon::parse('08:00'));
        $this->actingAs($lecturer)->get(route('lecturer.dashboard'))->assertSeeText('Good morning, Grace');

        Carbon::setTestNow(Carbon::parse('14:00'));
        $this->actingAs($lecturer)->get(route('lecturer.dashboard'))->assertSeeText('Good afternoon, Grace');

        Carbon::setTestNow(Carbon::parse('20:00'));
        $this->actingAs($lecturer)->get(route('lecturer.dashboard'))->assertSeeText('Good evening, Grace');
    }
}
