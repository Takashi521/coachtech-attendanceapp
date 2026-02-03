<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminStaffController;

Route::get('/email/verify', function () {
    return view('auth.verify'); // あなたが作ったBladeに合わせて変更OK
})->middleware('auth')->name('verification.notice');

// メール内リンクを踏んだ時（verification.verify）
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/attendance'); // 認証後は勤怠へ
})->middleware(['auth', 'signed'])->name('verification.verify');

// 認証メール 再送（verification.send）
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('status', 'verification-link-sent');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/attendance');
    }

    return redirect('/login');
});

Route::middleware(['auth', 'verified'])->group(function (): void {

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

    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])
        ->name('stamp_correction_request.list');

    Route::get('/stamp_correction_request/{id}', [StampCorrectionRequestController::class, 'show'])
        ->name('stamp_correction_request.show');

    Route::get('/stamp_correction_request/approve/{id}', [StampCorrectionRequestController::class, 'approve'])
        ->name('stamp_correction_request.approve');

    Route::post('/stamp_correction_request/approve/{id}', [StampCorrectionRequestController::class, 'approveUpdate'])
        ->name('stamp_correction_request.approve_update');
});


Route::prefix('admin')->name('admin.')->group(function () {
    // 未ログインのみ（管理者ログイン）
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminLoginController::class, 'create'])->name('login');
        Route::post('/login', [AdminLoginController::class, 'store'])->name('login.store');
    });

    // 管理者ログアウト（管理者ログイン中のみ）
    Route::post('/logout', [AdminLoginController::class, 'destroy'])
        ->middleware('auth')
        ->name('logout');

    // 管理者のみ
    Route::middleware(['auth', 'admin'])->group(function () {

        // 管理者：日次 勤怠一覧
        Route::get('/attendance', [AdminAttendanceController::class, 'index'])
            ->name('attendance.index');

        // 管理者：勤怠編集（詳細）
        Route::get('/attendance/detail/{id}', [AdminAttendanceController::class, 'edit'])
            ->name('attendance.detail');

        // 管理者：勤怠更新
        Route::post('/attendance/detail/{id}', [AdminAttendanceController::class, 'update'])
            ->name('attendance.update');

        // 管理者：スタッフ一覧
        Route::get('/staff', [AdminStaffController::class, 'index'])
            ->name('staff.index');

        // 管理者：スタッフ別 月次勤怠
        Route::get('/staff/{user}/attendance', [AdminStaffController::class, 'monthlyAttendance'])
            ->name('staff.attendance');

        Route::get('/staff/{user}/attendance/csv', [AdminStaffController::class, 'downloadCsv'])
            ->name('staff.attendance.csv');
    });
});
