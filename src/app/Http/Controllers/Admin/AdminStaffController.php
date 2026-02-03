<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminStaffController extends Controller
{
    public function index(Request $request): View
    {
        // 管理者以外は middleware で弾かれてる想定
        $users = User::query()
            ->where(function ($q) {
                $q->whereNull('role')->orWhere('role', '!=', 'admin');
            })
            ->orderBy('id')
            ->get(['id', 'name', 'email']);

        return view('admin.staff.index', compact('users'));
    }

    public function monthlyAttendance(Request $request, int $user): View
    {
        $targetUser = User::findOrFail($user);

        $month = (string) $request->query('month', Carbon::today()->format('Y-m'));
        $monthCarbon = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        $start = $monthCarbon->copy()->startOfMonth();
        $end   = $monthCarbon->copy()->endOfMonth();

        $attendances = Attendance::with('breaks')
            ->where('user_id', $targetUser->id)
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(function ($a) {
                return Carbon::parse($a->work_date)->toDateString();
            });

        $days = CarbonPeriod::create($start, $end);
        $rows = [];

        foreach ($days as $day) {
            $dateKey = $day->toDateString();

            $a = $attendances->get($dateKey);

            $rows[] = [
                'date' => $day->format('Y-m-d'),
                'work_start' => $a && $a->work_start_time ? substr($a->work_start_time, 0, 5) : '',
                'work_end'   => $a && $a->work_end_time ? substr($a->work_end_time, 0, 5) : '',
                'break'      => '',
                'total'      => '',
                'attendance_id' => $a?->id,
            ];
        }

        $prevMonth = $monthCarbon->copy()->subMonth();
        $nextMonth = $monthCarbon->copy()->addMonth();

        return view('admin.staff.monthly',
            [
                'targetUser'  => $targetUser,
                'monthCarbon' => $monthCarbon,
                'prevMonth'   => $prevMonth,
                'nextMonth'   => $nextMonth,
                'days'        => $days,
                'attendances' => $attendances,
            ]);
    }

    public function downloadCsv(Request $request, int $user): StreamedResponse
    {
        $targetUser = User::findOrFail($user);

        $month = (string) $request->query('month', Carbon::today()->format('Y-m'));
        $monthCarbon = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        $start = $monthCarbon->copy()->startOfMonth();
        $end   = $monthCarbon->copy()->endOfMonth();

        $attendances = Attendance::with('breaks')
            ->where('user_id', $targetUser->id)
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('work_date')
            ->get();

        $fileName = sprintf('attendance_%s_%s.csv', $targetUser->id, $monthCarbon->format('Ym'));

        return response()->streamDownload(function () use ($attendances) {
            $out = fopen('php://output', 'w');

            // Excel対策（文字化け防止）: UTF-8 BOM
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['日付', '出勤', '退勤', '休憩', '合計', '備考']);

            foreach ($attendances as $a) {
                $date = Carbon::parse($a->work_date)->format('Y-m-d');

                $workStart = $a->work_start_time ? substr($a->work_start_time, 0, 5) : '';
                $workEnd   = $a->work_end_time ? substr($a->work_end_time, 0, 5) : '';

                $breakMinutes = 0;
                foreach ($a->breaks as $b) {
                    if ($b->break_start_time && $b->break_end_time) {
                        $bs = Carbon::parse($b->break_start_time);
                        $be = Carbon::parse($b->break_end_time);
                        $breakMinutes += $bs->diffInMinutes($be);
                    }
                }
                $breakText = $breakMinutes ? sprintf('%d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60) : '';

                $totalText = '';
                if ($a->work_start_time && $a->work_end_time) {
                    $ws = Carbon::parse($a->work_start_time);
                    $we = Carbon::parse($a->work_end_time);
                    $workMinutes = $ws->diffInMinutes($we);
                    $net = max(0, $workMinutes - $breakMinutes);
                    $totalText = sprintf('%d:%02d', intdiv($net, 60), $net % 60);
                }

                fputcsv($out, [
                    $date,
                    $workStart,
                    $workEnd,
                    $breakText,
                    $totalText,
                    (string) ($a->note ?? ''),
                ]);
            }

            fclose($out);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}