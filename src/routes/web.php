<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/attendance');
    }

    return redirect('/login');
});

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

    Route::get('/attendance/list', [AttendanceController::class, 'list'])
        ->name('attendance.list');

    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])
        ->name('attendance.detail');

    Route::get('/attendance/detail/{id}/correction', [AttendanceController::class, 'showCorrection'])
        ->name('attendance.correction.show');

    Route::post('/attendance/detail/{id}/correction', [AttendanceController::class, 'storeCorrection'])
        ->name('attendance.correction.store');

    Route::post('/attendance/detail/{id}/request', [AttendanceController::class, 'requestCorrection'])
        ->name('attendance.correction_request');
});
