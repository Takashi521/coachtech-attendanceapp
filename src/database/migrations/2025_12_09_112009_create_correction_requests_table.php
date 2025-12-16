<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorrectionRequestsTable extends Migration
{
    public function up():void
    {
        Schema::create('correction_requests', function (Blueprint $table):void {
            $table->id();

            // 申請者（一般ユーザー）
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            // 対象となる勤怠
            $table->foreignId('attendance_id')
                ->constrained()
                ->onDelete('cascade');

            // 修正後の希望値（空の場合は「その項目は変更なし」と解釈）
            $table->date('requested_work_date')->nullable();
            $table->time('requested_work_start_time')->nullable();
            $table->time('requested_work_end_time')->nullable();
            $table->text('requested_note')->nullable();

            // 申請全体の状態
            $table->string('status', 20)
                ->default('pending'); // pending / approved / rejected

            // 承認者（管理ユーザー）
            $table->foreignId('approver_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correction_requests');
    }
}
