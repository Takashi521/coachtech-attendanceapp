<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            $table->date('work_date');                    // 勤務日
            $table->time('work_start_time')->nullable();  // 出勤時刻
            $table->time('work_end_time')->nullable();    // 退勤時刻

            $table->string('status', 20)                  // 勤務外 / 出勤中 / 休憩中 / 退勤済
                ->default('not_worked');

            $table->text('note')->nullable();             // 備考

            $table->timestamps();

            // 1ユーザー1日1レコードを保証
            $table->unique(['user_id', 'work_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
}