<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminAttendanceUpdateRequest;
use App\Models\Attendance;
use App\Models\User;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;



class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))->startOfDay()
            : Carbon::today();

        $prevDate = $date->copy()->subDay();
        $nextDate = $date->copy()->addDay();

        // 管理者以外の全ユーザー
        $users = User::query()
            ->where('is_admin', false)
            ->orderBy('name')
            ->get();

        // 対象日の勤怠（ユーザー・休憩もまとめて取得）
        $attendancesByUserId = Attendance::query()
            ->with(['breaks', 'user'])
            ->whereDate('work_date', $date->toDateString())
            ->get()
            ->keyBy('user_id');

        $rows = $users
            ->filter(function ($user) use ($attendancesByUserId) {
               return $attendancesByUserId->has($user->id);
            })
            ->values()
            ->map(function ($user) use ($attendancesByUserId, $date) {
            $attendance = $attendancesByUserId->get($user->id);

            $workStart = $attendance?->work_start_time ? substr((string) $attendance->work_start_time, 0, 5) : '';
            $workEnd = $attendance?->work_end_time ? substr((string) $attendance->work_end_time, 0, 5) : '';

            $breakMinutes = 0;
            if ($attendance && $attendance->relationLoaded('breaks')) {
                foreach ($attendance->breaks as $break) {
                    $bs = $break->break_start_time ?? null;
                    $be = $break->break_end_time ?? null;

                    if (!$bs || !$be) {
                        continue;
                    }

                    $bsText = substr((string) $bs, 0, 5);
                    $beText = substr((string) $be, 0, 5);

                    $bsAt = Carbon::parse($date->toDateString() . ' ' . $bsText);
                    $beAt = Carbon::parse($date->toDateString() . ' ' . $beText);

                    if ($beAt->lessThanOrEqualTo($bsAt)) {
                        continue;
                    }

                    $breakMinutes += $bsAt->diffInMinutes($beAt);
                }
            }

            $breakText = $this->formatMinutes($breakMinutes);

            $totalText = '';
            if ($workStart !== '' && $workEnd !== '') {
                $wsAt = Carbon::parse($date->toDateString() . ' ' . $workStart);
                $weAt = Carbon::parse($date->toDateString() . ' ' . $workEnd);

                if ($weAt->greaterThan($wsAt)) {
                    $workMinutes = $wsAt->diffInMinutes($weAt);
                    $totalMinutes = max(0, $workMinutes - $breakMinutes);
                    $totalText = $this->formatMinutes($totalMinutes);
                }
            }

            return [
                'user_name' => $user->name,
                'work_start' => $workStart,
                'work_end' => $workEnd,
                'break' => $breakText,
                'total' => $totalText,
                'attendance_id' => $attendance?->id,
            ];
        });

        return view('admin.attendance.index', [
            'date' => $date,
            'prevDate' => $prevDate,
            'nextDate' => $nextDate,
            'rows' => $rows,
        ]);
    }

    private function formatMinutes(int $minutes): string
    {
        if ($minutes <= 0) {
            return '';
        }

        $h = intdiv($minutes, 60);
        $m = $minutes % 60;

        return sprintf('%d:%02d', $h, $m);
    }

    public function edit(int $id): View
    {
        $attendance = Attendance::with(['user', 'breaks' => function ($q) {
            $q->orderBy('break_order');
        }])->findOrFail($id);

        return view('admin.attendance.edit', compact('attendance'));
    }

    public function update(AdminAttendanceUpdateRequest $request, int $id): RedirectResponse
    {
        $attendance = Attendance::with(['breaks'])->findOrFail($id);

        $attendance->work_start_time = $request->input('work_start');
        $attendance->work_end_time = $request->input('work_end');
        $attendance->note = $request->input('note');
        $attendance->save();

        // breaks（break_order 1/2）を更新 or 作成
        $this->upsertBreak($attendance->id, 1, $request->input('break1_start'), $request->input('break1_end'));
        $this->upsertBreak($attendance->id, 2, $request->input('break2_start'), $request->input('break2_end'));

        return redirect()
            ->route('admin.attendance.detail', ['id' => $attendance->id])
            ->with('message', '勤怠を修正しました。');
    }

    private function upsertBreak(int $attendanceId, int $order, ?string $start, ?string $end): void
    {
        if (!$start && !$end) {
            AttendanceBreak::where('attendance_id', $attendanceId)
                ->where('break_order', $order)
                ->delete();
            return;
        }

        $break = AttendanceBreak::firstOrNew([
            'attendance_id' => $attendanceId,
            'break_order' => $order,
        ]);

        $break->break_start_time = $start;
        $break->break_end_time = $end;
        $break->save();
    }

    public function detail(int $id)
    {
        $attendance = Attendance::with(['user', 'breaks'])->findOrFail($id);

        return view('admin.attendance.edit', compact('attendance'));
    }
}