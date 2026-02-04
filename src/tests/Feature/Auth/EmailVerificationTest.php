<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_user_is_redirected_to_verification_notice(): void
    {
        // 認証必須にしておく（環境差で変わるのを防ぐ）
        Config::set('fortify.features', [
            \Laravel\Fortify\Features::emailVerification(),
        ]);

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $res = $this->actingAs($user)->get('/attendance');

        // verified ミドルウェアで /email/verify へ
        $res->assertRedirect('/email/verify');
    }

    public function test_verified_user_can_access_attendance(): void
    {
        Config::set('fortify.features', [
            \Laravel\Fortify\Features::emailVerification(),
        ]);

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $res = $this->actingAs($user)->get('/attendance');

        $res->assertOk();
        $res->assertSee('勤怠'); // 画面文言はあなたの実装に合わせてる（勤怠画面の見出し）
    }
}
