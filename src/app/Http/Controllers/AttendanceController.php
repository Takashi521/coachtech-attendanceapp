<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\CorrectionRequest;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
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
        $attendance->status = 'done';
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

    public function list(Request $request): View
    {
        $user = $request->user();

        $month = $this->parseMonth($request);
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->with(['breaks' => function ($query) {
                $query->orderBy('break_order');
            }])
            ->get()
            ->keyBy('work_date');

        $jpDow = ['日', '月', '火', '水', '木', '金', '土'];

        $rows = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $workDate = $date->toDateString();
            $attendance = $attendances->get($workDate);

            // ★ まず必ず変数を定義する（これがないと未定義エラーになる）
            $breakMinutes = $attendance ? $this->breakMinutes($attendance) : 0;

            $workMinutes = 0;
            if ($attendance && $attendance->work_start_time && $attendance->work_end_time) {
                $workMinutes = $this->workMinutes($attendance);
            }

            $rows[] = [
                'label' => $date->format('m/d') . '(' . $jpDow[$date->dayOfWeek] . ')',
                'attendance' => $attendance,

                // ★ 休憩は「1分でもあれば表示」
                'break_total' => $breakMinutes > 0 ? $this->formatMinutes($breakMinutes) : '',

                // ★ 合計は「出勤・退勤が揃ったら表示」
                'work_total' => $workMinutes > 0 ? $this->formatMinutes($workMinutes) : '',
            ];
        }

        $prevMonth = $month->copy()->subMonth()->format('Y-m');
        $nextMonth = $month->copy()->addMonth()->format('Y-m');
        $monthLabel = $month->format('Y/m');

        return view('attendance.list', compact('rows', 'monthLabel', 'prevMonth', 'nextMonth'));
    }


    public function detail(int $id): View
    {
        $user = auth()->user();

        $attendance = Attendance::with('breaks')->findOrFail($id);

        if ((int) $attendance->user_id !== (int) $user->id) {
            abort(403);
        }

        $targetDate = Carbon::parse($attendance->work_date);

        $pending = CorrectionRequest::where('attendance_id', $attendance->id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->latest('id')
            ->first();

        $isPending = (bool) $pending;

        $displayWorkStart = $pending?->requested_work_start_time;
        $displayWorkEnd   = $pending?->requested_work_end_time;
        $displayNote      = $pending?->requested_note;

        $displayBreaks = $isPending
            ? $pending->breaks()->orderBy('break_order')->get()
            : null;

        return view('attendance.detail', compact(
            'user',
            'attendance',
            'targetDate',
            'isPending',
            'displayWorkStart',
            'displayWorkEnd',
            'displayNote',
            'displayBreaks'
        ));
    }

    public function correctionRequestList(Request $request): View
    {
        $user = $request->user();

        // tab=pending / approved（デフォルト pending）
        $tab = (string) $request->query('tab', 'pending');
        $status = $tab === 'approved' ? 'approved' : 'pending';

        $requests = CorrectionRequest::with(['attendance'])
            ->where('user_id', $user->id)
            ->where('status', $status)
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('stamp_correction_request.list', compact('requests', 'tab', 'user'));
    }


    private function parseMonth(Request $request): Carbon
    {
        $month = (string) $request->query('month', '');

        if (preg_match('/^\d{4}-\d{2}$/', $month) === 1) {
            return Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        }

        return Carbon::today()->startOfMonth();
    }

    private function breakMinutes(Attendance $attendance): int
    {
        $total = 0;

        foreach ($attendance->breaks as $break) {
            if ($break->break_start_time && $break->break_end_time) {
                $start = Carbon::createFromFormat('H:i:s', $break->break_start_time);
                $end = Carbon::createFromFormat('H:i:s', $break->break_end_time);
                $total += $end->diffInMinutes($start);
            }
        }

        return $total;
    }

    private function workMinutes(Attendance $attendance): int
    {
        if (!$attendance->work_start_time || !$attendance->work_end_time) {
            return 0;
        }

        $start = Carbon::createFromFormat('H:i:s', $attendance->work_start_time);
        $end = Carbon::createFromFormat('H:i:s', $attendance->work_end_time);

        $minutes = $end->diffInMinutes($start) - $this->breakMinutes($attendance);

        return max(0, $minutes);
    }

    private function formatMinutes(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        return sprintf('%d:%02d', $hours, $mins);
    }

    public function requestCorrection(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();

        $attendance = Attendance::with('breaks')->findOrFail($id);
        if ((int) $attendance->user_id !== (int) $user->id) {
            abort(403);
        }

        $alreadyPending = CorrectionRequest::where('attendance_id', $attendance->id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($alreadyPending) {
            return back()->withErrors([
                'pending' => '承認待ちのため修正できません。',
            ]);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'work_start_time' => ['required'],
                'work_end_time' => ['required'],
                'break_start_time' => ['array'],
                'break_start_time.*' => ['nullable'],
                'break_end_time' => ['array'],
                'break_end_time.*' => ['nullable'],
                'note' => ['required'],
            ],
            [
                'note.required' => '備考を記入してください',
            ]
        );

        $validator->after(function ($v) use ($request) {
            $workStart = $request->input('work_start_time'); // H:i
            $workEnd = $request->input('work_end_time');     // H:i

            // 1) 出勤 > 退勤
            if ($workStart && $workEnd) {
                $ws = Carbon::createFromFormat('H:i', $workStart);
                $we = Carbon::createFromFormat('H:i', $workEnd);

                if ($ws->gt($we)) {
                    $v->errors()->add('work_time', '出勤時間もしくは退勤時間が不適切な値です');
                }
            }

            $breakStarts = $request->input('break_start_time', []);
            $breakEnds = $request->input('break_end_time', []);

            foreach ($breakStarts as $i => $bsValue) {
                $beValue = $breakEnds[$i] ?? null;

                // 2) 休憩開始が出勤より前 / 退勤より後
                if ($bsValue && $workStart && $workEnd) {
                    $bs = Carbon::createFromFormat('H:i', $bsValue);
                    $ws = Carbon::createFromFormat('H:i', $workStart);
                    $we = Carbon::createFromFormat('H:i', $workEnd);

                    if ($bs->lt($ws) || $bs->gt($we)) {
                        $v->errors()->add("break_time.{$i}", '休憩時間が不適切な値です');
                    }
                }

                // 3) 休憩終了が退勤より後
                if ($beValue && $workEnd) {
                    $be = Carbon::createFromFormat('H:i', $beValue);
                    $we = Carbon::createFromFormat('H:i', $workEnd);

                    if ($be->gt($we)) {
                        $v->errors()->add("break_time.{$i}", '休憩時間もしくは退勤時間が不適切な値です');
                    }
                }
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $correctionRequest = CorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'requested_work_start_time' => $request->input('work_start_time') . ':00',
            'requested_w ork_end_time' => $request->input('work_end_time') . ':00',
            'requested_note' => $request->input('note'),
            'requested_work_date' => $attendance->work_date,
        ]);

        $breakStarts = $request->input('break_start_time', []);
        $breakEnds = $request->input('break_end_time', []);

        foreach ($breakStarts as $i => $bs) {
            $be = $breakEnds[$i] ?? null;

            $bs = $bs ? ($bs . ':00') : null;
            $be = $be ? ($be . ':00') : null;

            if ($bs === null && $be === null) {
                continue;
            }

            $correctionRequest->breaks()->create([
                'break_order' => $i + 1,
                'requested_break_start_time' => $bs,
                'requested_break_end_time' => $be,
            ]);
        }

        return redirect()->route('attendance.detail', ['id' => $attendance->id]);
    }

    public function showCorrection(int $id): View
    {
        $user = auth()->user();

        $attendance = Attendance::with('user')->findOrFail($id);
        if ((int) $attendance->user_id !== (int) $user->id) {
            abort(403);
        }

        $correctionRequest = CorrectionRequest::where('attendance_id', $attendance->id)
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        $isPending = (bool) ($correctionRequest && $correctionRequest->status === 'pending');

        return view('attendance.correction', compact('attendance', 'correctionRequest', 'isPending'));
    }
}
