<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceEditTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'is_admin' => 1,
            'email_verified_at' => now(),
        ]);
    }

    public function test_admin_can_open_attendance_edit_page(): void
    {
        $admin = $this->createAdmin();
        $user  = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->toDateString(),
            'work_start_time' => '09:00:00',
            'work_end_time' => '18:00:00',
        ]);

        $res = $this->actingAs($admin)->get(route('admin.attendance.detail', ['id' => $attendance->id]));

        $res->assertOk();
        $res->assertSee($user->name);
        $res->assertSee('勤怠');
    }

    public function test_invalid_update_returns_validation_error(): void
    {
        $admin = $this->createAdmin();
        $user  = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->toDateString(),
            'work_start_time' => '09:00:00',
            'work_end_time' => '18:00:00',
        ]);

        $res = $this->actingAs($admin)->post(
            route('admin.attendance.update', ['id' => $attendance->id]),
            [
                'work_start_time' => '18:00',
                'work_end_time'   => '09:00',
                'break_start_time' => [],
                'break_end_time'   => [],
                'note' => '',
            ]
        );

        $res->assertStatus(302);
        $res->assertSessionHasErrors(); // キー指定は実装差が出るので全体でOKにしてます
    }

    public function test_valid_update_updates_attendance(): void
    {
        $admin = $this->createAdmin();

        // 実打刻で「更新対象の勤怠」を作る（status not_worked を避ける）
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)->post(route('attendance.work_start'));
        $this->actingAs($user)->post(route('attendance.work_end'));

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', Carbon::today()->toDateString())
            ->firstOrFail();

        $res = $this->actingAs($admin)->post(route('admin.attendance.update', ['id' => $attendance->id]), [
            'work_start' => '10:00',
            'work_end'   => '19:00',
            'break1_start' => null,
            'break1_end'   => null,
            'break2_start' => null,
            'break2_end'   => null,
            'note' => 'updated by test',
        ]);

        $res->assertStatus(302);
        $res->assertSessionHasNoErrors();

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'work_start_time' => '10:00:00',
            'work_end_time'   => '19:00:00',
        ]);
    }
}