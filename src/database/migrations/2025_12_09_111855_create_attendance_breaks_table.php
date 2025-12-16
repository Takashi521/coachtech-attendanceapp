<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceBreaksTable extends Migration
{
    public function up():void
    {
        Schema::create('attendance_breaks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('attendance_id')
                ->constrained()
                ->onDelete('cascade');

            $table->unsignedTinyInteger('break_order')
                ->default(1);                          // 1回目 / 2回目 ... の順番

            $table->time('break_start_time');         // 休憩開始
            $table->time('break_end_time')->nullable(); // 休憩戻ボタンがまだなら null
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_breaks');
    }
}
