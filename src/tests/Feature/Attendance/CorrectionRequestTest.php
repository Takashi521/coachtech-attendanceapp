<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    private function createAttendanceFor(User $user): Attendance
    {
        return Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->toDateString(),
            'work_start_time' => '09:00:00',
            'work_end_time' => '18:00:00',
            'status' => 'working', // あなたの実装に合わせて必要なら変更
        ]);
    }

    public function test_invalid_time_shows_validation_error(): void
    {
        $user = User::factory()->create();
        $attendance = $this->createAttendanceFor($user);

        $response = $this->actingAs($user)->post(
            route('attendance.correction_request', ['id' => $attendance->id]),
            [
                'work_start_time' => '19:00',
                'work_end_time' => '18:00',
                'note' => 'テスト備考',
                'break_start_time' => [],
                'break_end_time' => [],
            ]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors();
    }

    public function test_correction_request_can_be_created(): void
    {
        $user = User::factory()->create();
        $attendance = $this->createAttendanceFor($user);

        $response = $this->actingAs($user)->post(
            route('attendance.correction_request', ['id' => $attendance->id]),
            []
        );

        $response->assertStatus(302);
    }
}
