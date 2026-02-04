<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DetailViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_detail_shows_logged_in_user_name_and_selected_date(): void
    {
        $user = User::factory()->create();
        $date = Carbon::today()->toDateString();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => $date,
            'work_start_time' => '09:00:00',
            'work_end_time' => '18:00:00',
            'status' => 'worked',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.detail', ['id' => $attendance->id]));

        $response->assertStatus(200);

        // 名前がログインユーザー名
        $response->assertSee($user->name);

        $response->assertSee(Carbon::parse($date)->format('Y年'));
        $response->assertSee(Carbon::parse($date)->format('n月j日'));
    }
}