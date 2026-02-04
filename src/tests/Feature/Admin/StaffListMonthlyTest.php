<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffListMonthlyTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'is_admin' => 1,
            'email_verified_at' => now(),
        ]);
    }

    public function test_admin_can_open_staff_list(): void
    {
        $admin = $this->createAdmin();
        $user  = User::factory()->create();

        $res = $this->actingAs($admin)->get(route('admin.staff.index'));

        $res->assertOk();
        $res->assertSee('スタッフ一覧');
        $res->assertSee($user->name);
    }

    public function test_admin_can_open_staff_monthly_attendance(): void
    {
        $admin = $this->createAdmin();
        $user  = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $date = Carbon::today()->toDateString();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $date,
            'work_start_time' => '09:00:00',
            'work_end_time' => '18:00:00',
            'status' => 'working',
        ]);

        $res = $this->actingAs($admin)->get(route('admin.staff.attendance', ['user' => $user->id]));

        $res->assertOk();
        $res->assertSee($user->name);
        $res->assertSee(Carbon::parse($date)->format('Y/m'));
    }
}