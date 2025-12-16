<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StampController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::firstOrCreate(
            [
                'user_id' => $user->id,
                'work_date' => $today,
            ],
            [
                'status' => 'not_worked',
            ]
        );

        $breaks = $attendance->breaks()
            ->orderBy('break_order')
            ->get();

        return view('stamp.show', compact('attendance', 'breaks'));
    }

    public function workStart(Request $request): RedirectResponse
    {
        $attendance = $this->getTodayAttendance($request);

        if ($attendance->status !== 'not_worked') {
            return back();
        }

        $attendance->work_start_time = now()->format('H:i:s');
        $attendance->status = 'working';
        $attendance->save();

        return back();
    }

    public function breakStart(Request $request): RedirectResponse
    {
        $attendance = $this->getTodayAttendance($request);

        if ($attendance->status !== 'working') {
            return back();
        }

        $latestOpenBreak = $attendance->breaks()
            ->whereNull('break_end_time')
            ->latest('id')
            ->first();

        if ($latestOpenBreak) {
            return back();
        }

        $nextOrder = (int) $attendance->breaks()->max('break_order') + 1;

        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_order' => $nextOrder,
            'break_start_time' => now()->format('H:i:s'),
            'break_end_time' => null,
        ]);

        $attendance->status = 'on_break';
        $attendance->save();

        return back();
    }

    public function breakEnd(Request $request): RedirectResponse
    {
        $attendance = $this->getTodayAttendance($request);

        if ($attendance->status !== 'on_break') {
            return back();
        }

        $openBreak = $attendance->breaks()
            ->whereNull('break_end_time')
            ->latest('id')
            ->first();

        if (!$openBreak) {
            return back();
        }

        $openBreak->break_end_time = now()->format('H:i:s');
        $openBreak->save();

        $attendance->status = 'working';
        $attendance->save();

        return back();
    }

    public function workEnd(Request $request): RedirectResponse
    {
        $attendance = $this->getTodayAttendance($request);

        if ($attendance->status !== 'working') {
            return back();
        }

        $openBreak = $attendance->breaks()
            ->whereNull('break_end_time')
            ->latest('id')
            ->first();

        if ($openBreak) {
            return back();
        }

        $attendance->work_end_time = now()->format('H:i:s');
        $attendance->status = 'finished';
        $attendance->save();

        return back()->with('message', 'お疲れ様でした。');
    }

    private function getTodayAttendance(Request $request): Attendance
    {
        $user = $request->user();
        $today = Carbon::today()->toDateString();

        return Attendance::firstOrCreate(
            [
                'user_id' => $user->id,
                'work_date' => $today,
            ],
            [
                'status' => 'not_worked',
            ]
        );
    }
}