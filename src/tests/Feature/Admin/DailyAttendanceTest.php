<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'is_admin' => 1,
            'email_verified_at' => now(),
        ]);
    }

    public function test_admin_daily_attendance_page_shows_today_and_users(): void
    {
        $admin = $this->createAdmin();

        $u1 = User::factory()->create();
        $u2 = User::factory()->create();

        $today = Carbon::today()->toDateString();

        $a1 = Attendance::create([
            'user_id' => $u1->id,
            'work_date' => $today,
            'work_start_time' => '09:00:00',
            'work_end_time' => '18:00:00',
        ]);

        $a2 = Attendance::create([
            'user_id' => $u2->id,
            'work_date' => $today,
            'work_start_time' => '10:00:00',
            'work_end_time' => '19:00:00',
        ]);

        $res = $this->actingAs($admin)->get(route('admin.attendance.index'));

        $res->assertOk();

        // 日付表示（改行や分割に強いように “年” と “月日” を分けて確認）
        $res->assertSee(Carbon::parse($today)->format('Y年'));
        $res->assertSee(Carbon::parse($today)->format('n月j日'));

        // 全ユーザー分の勤怠が見える（名前が出る）
        $res->assertSee($u1->name);
        $res->assertSee($u2->name);

        // 詳細リンク（少なくともURLが含まれる）
        $res->assertSee('/admin/attendance/detail/' . $a1->id);
        $res->assertSee('/admin/attendance/detail/' . $a2->id);

        // 前日/翌日ナビがある前提（文言確認）
        $res->assertSee('前日');
        $res->assertSee('翌日');
    }
}
