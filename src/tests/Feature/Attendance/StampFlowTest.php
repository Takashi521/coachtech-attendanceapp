<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StampFlowTest extends TestCase
{
    use RefreshDatabase;

    private function createVerifiedUser(): User
    {
        return User::factory()->create([
            'email_verified_at' => now(),
        ]);
    }

    private function todayString(): string
    {
        return Carbon::today()->toDateString();
    }

    public function test_work_start_creates_attendance_for_today(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-02-04 09:00:00'));

        $user = $this->createVerifiedUser();

        $response = $this->actingAs($user)->post(route('attendance.work_start'));

        $response->assertStatus(302);

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $this->todayString())
            ->first();

        $this->assertNotNull($attendance);
        $this->assertNotNull($attendance->work_start_time);
        $this->assertNull($attendance->work_end_time);
    }

    public function test_break_can_be_started_and_ended_multiple_times(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-02-04 10:00:00'));

        $user = $this->createVerifiedUser();

        // 先に出勤
        $this->actingAs($user)->post(route('attendance.work_start'))->assertStatus(302);

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $this->todayString())
            ->firstOrFail();

        // 休憩1回目（入）
        $this->actingAs($user)->post(route('attendance.break_start'))->assertStatus(302);

        $attendance->refresh();
        $attendance->load('breaks');

        $this->assertCount(1, $attendance->breaks);
        $this->assertNotNull($attendance->breaks[0]->break_start_time);
        $this->assertNull($attendance->breaks[0]->break_end_time);

        // 休憩1回目（戻）
        $this->actingAs($user)->post(route('attendance.break_end'))->assertStatus(302);

        $attendance->refresh();
        $attendance->load('breaks');

        $this->assertNotNull($attendance->breaks[0]->break_end_time);

        // 休憩2回目（入）
        $this->actingAs($user)->post(route('attendance.break_start'))->assertStatus(302);

        $attendance->refresh();
        $attendance->load('breaks');

        $this->assertCount(2, $attendance->breaks);
        $this->assertNotNull($attendance->breaks[1]->break_start_time);
        $this->assertNull($attendance->breaks[1]->break_end_time);

        // 休憩2回目（戻）
        $this->actingAs($user)->post(route('attendance.break_end'))->assertStatus(302);

        $attendance->refresh();
        $attendance->load('breaks');

        $this->assertNotNull($attendance->breaks[1]->break_end_time);
    }

    public function test_work_end_sets_end_time(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-02-04 18:00:00'));

        $user = $this->createVerifiedUser();

        $this->actingAs($user)->post(route('attendance.work_start'))->assertStatus(302);
        $this->actingAs($user)->post(route('attendance.work_end'))->assertStatus(302);

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $this->todayString())
            ->firstOrFail();

        $this->assertNotNull($attendance->work_start_time);
        $this->assertNotNull($attendance->work_end_time);
    }
}
