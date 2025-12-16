<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorrectionRequestBreaksTable extends Migration
{
    public function up(): void
    {
        Schema::create('correction_request_breaks', function (Blueprint $table):void {
            $table->id();

            $table->foreignId('correction_request_id')
                ->constrained()
                ->onDelete('cascade');

            $table->unsignedTinyInteger('break_order')
                ->default(1); // 1回目 / 2回目 ... の順番

            $table->time('requested_break_start_time')->nullable();
            $table->time('requested_break_end_time')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correction_request_breaks');
    }
}
