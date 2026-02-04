<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonthlyListTest extends TestCase
{
    use RefreshDatabase;

    private function createVerifiedUser(): User
    {
        return User::factory()->create([
            'email_verified_at' => now(),
        ]);
    }

    public function test_list_shows_current_month_by_default(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-02-04 10:00:00'));

        $user = $this->createVerifiedUser();

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $response->assertStatus(200);

        // 画面中央の月表示（controller では Y/m）
        $response->assertSee('2026/02');
    }

    public function test_prev_and_next_month_links_exist(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-02-04 10:00:00'));

        $user = $this->createVerifiedUser();

        $response = $this->actingAs($user)->get(route('attendance.list', ['month' => '2026-02']));

        $response->assertStatus(200);

        // 前月 / 翌月リンクのクエリが正しいこと
        $response->assertSee(route('attendance.list', ['month' => '2026-01']), false);
        $response->assertSee(route('attendance.list', ['month' => '2026-03']), false);
    }

    public function test_detail_link_is_shown_only_when_attendance_exists(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-02-04 10:00:00'));

        $user = $this->createVerifiedUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-02-03',
            'work_start_time' => '09:00:00',
            'work_end_time' => '18:00:00',
            'status' => 'worked',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list', ['month' => '2026-02']));

        $response->assertStatus(200);

        // 勤怠がある日は「詳細」リンク（aタグ）が出る
        $response->assertSee(route('attendance.detail', ['id' => $attendance->id]), false);

        // 勤怠が無い日は empty 表示（span）が存在する（少なくとも1件）
        $response->assertSee('attendance-detail-link--empty', false);
    }

    public function test_not_worked_record_does_not_show_zero_time_total(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-02-04 10:00:00'));

        $user = $this->createVerifiedUser();

        // 以前の「00:00 が残る」パターン：レコードはあるが時刻が null
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-02-03',
            'work_start_time' => null,
            'work_end_time' => null,
            'status' => 'not_worked',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list', ['month' => '2026-02']));

        $response->assertStatus(200);

        // 「00:00」や「0:00」が出ないこと（実装差異を吸収するため両方チェック）
        $response->assertDontSee('00:00');
        $response->assertDontSee('0:00');
    }
}
