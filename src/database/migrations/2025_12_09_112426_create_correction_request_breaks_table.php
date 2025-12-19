<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorrectionRequestBreaksTable extends Migration
{
    public function up(): void
    {
        Schema::create('correction_request_breaks', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('correction_request_id');
            $table->integer('break_order');
            $table->time('requested_break_start_time')->nullable();
            $table->time('requested_break_end_time')->nullable();
            $table->timestamps();

            $table->foreign('correction_request_id')
                ->references('id')->on('correction_requests')
                ->onDelete('cascade');
            $table->unique(['correction_request_id', 'break_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correction_request_breaks');
    }
}
