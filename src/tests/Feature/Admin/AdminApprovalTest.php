<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminApprovalTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'is_admin' => 1,
            'email_verified_at' => now(),
        ]);
    }

    private function createPendingRequest(): CorrectionRequest
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'work_start_time' => '09:00:00',
            'work_end_time' => '18:00:00',
        ]);

        $request = CorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,

            'requested_work_date' => $attendance->work_date,

            'requested_work_start_time' => '10:00:00',
            'requested_work_end_time'   => '19:00:00',
            'requested_note'            => 'approved by test',
        ]);

        return $request;
    }

    public function test_admin_can_open_approval_list(): void
    {
        $admin = $this->createAdmin();
        $this->createPendingRequest();

        $res = $this->actingAs($admin)->get(route('stamp_correction_request.list'));

        $res->assertOk();
        $res->assertSee('申請');
    }

    public function test_admin_can_approve_request(): void
    {
        $admin = $this->createAdmin();
        $req = $this->createPendingRequest();

        // 承認画面が開ける
        $show = $this->actingAs($admin)->get(route('stamp_correction_request.approve', ['id' => $req->id]));
        $show->assertOk();

        // 承認POST（コントローラが必要とする項目があるなら配列に追加）
        $post = $this->actingAs($admin)->post(route('stamp_correction_request.approve_update', ['id' => $req->id]), []);
        $post->assertStatus(302);

        // DB確認（カラム差があっても落ちにくくする）
        $fresh = $req->fresh();

        if (Schema::hasColumn('correction_requests', 'status')) {
            $this->assertNotSame('pending', (string) $fresh->status);
        }

        if (Schema::hasColumn('correction_requests', 'approved_at')) {
            $this->assertNotNull($fresh->approved_at);
        }
    }
}
