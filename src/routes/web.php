<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

Route::middleware('auth')->group(function (): void {
    Route::get('/attendance', [AttendanceController::class, 'show'])
        ->name('attendance.show');

    Route::post('/attendance/work-start', [AttendanceController::class, 'workStart'])
        ->name('attendance.work_start');

    Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart'])
        ->name('attendance.break_start');

    Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd'])
        ->name('attendance.break_end');

    Route::post('/attendance/work-end', [AttendanceController::class, 'workEnd'])
        ->name('attendance.work_end');
});
