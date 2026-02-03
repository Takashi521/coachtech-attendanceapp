<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\CorrectionRequest;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StampCorrectionRequestController extends Controller
{
    /**
     * PG06 / PG12: 申請一覧（一般/管理者 共通）
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $isAdmin = $this->isAdmin($user);

        $tab = (string) $request->query('tab', 'pending');
        $status = $tab === 'approved' ? 'approved' : 'pending';

        $query = CorrectionRequest::with(['attendance', 'user'])
            ->where('status', $status)
            ->orderByDesc('created_at');

        if (!$isAdmin) {
            $query->where('user_id', $user->id);
        }

        $requests = $query->paginate(10);

        return view('stamp_correction_request.list', compact('requests', 'tab', 'isAdmin', 'user'));
    }


    public function show(Request $request, int $id): View
    {
        $user = $request->user();
        $isAdmin = $this->isAdmin($user);

        $correctionRequest = CorrectionRequest::with([
            'attendance',
            'breaks' => function ($q) {
                $q->orderBy('break_order');
            },
            'user',
        ])->findOrFail($id);


        if (!$isAdmin && (int) $correctionRequest->user_id !== (int) $user->id) {
            abort(403);
        }

        return view('stamp_correction_request.show', compact('correctionRequest', 'isAdmin'));
    }

    /**
     * PG13: 承認画面（管理者）
     */
    public function approve(Request $request, int $id): View
    {
        $user = $request->user();
        if (!$this->isAdmin($user)) {
            abort(403);
        }

        $correctionRequest = CorrectionRequest::with([
            'attendance' => function ($q) {
                $q->with(['breaks' => function ($bq) {
                    $bq->orderBy('break_order');
                }]);
            },
            'breaks' => function ($q) {
                $q->orderBy('break_order');
            },
            'user',
        ])->findOrFail($id);

        return view('stamp_correction_request.approve', compact('correctionRequest'));
    }

    /**
     * PG13: 承認/却下の更新（管理者）
     */
    public function approveUpdate(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        if (!$this->isAdmin($user)) {
            abort(403);
        }

        $action = (string) $request->input('action', 'approve');
        if ($action !== 'approve') {
            return back()->withErrors(['action' => '不正な操作です。']);
        }

        $correctionRequest = CorrectionRequest::with([
            'attendance',
            'breaks' => function ($q) {
                $q->orderBy('break_order');
            },
        ])->findOrFail($id);

        // すでに処理済み
        if ($correctionRequest->status !== 'pending') {

            return redirect()->route('stamp_correction_request.approve', ['id' => $id]);
        }

        DB::transaction(function () use ($user, $correctionRequest): void {
            $attendance = Attendance::with('breaks')->findOrFail($correctionRequest->attendance_id);

            $attendance->work_start_time = $correctionRequest->requested_work_start_time;
            $attendance->work_end_time   = $correctionRequest->requested_work_end_time;
            $attendance->note            = $correctionRequest->requested_note;
            $attendance->save();

            AttendanceBreak::where('attendance_id', $attendance->id)->delete();

            foreach ($correctionRequest->breaks as $b) {
                $bs = $b->requested_break_start_time;
                $be = $b->requested_break_end_time;

                if ($bs === null && $be === null) {
                    continue;
                }

                AttendanceBreak::create([
                    'attendance_id'     => $attendance->id,
                    'break_order'       => $b->break_order,
                    'break_start_time'  => $bs,
                    'break_end_time'    => $be,
                ]);
            }

            $correctionRequest->status      = 'approved';
            $correctionRequest->approver_id = $user->id;
            $correctionRequest->approved_at = now();
            $correctionRequest->save();
        });

        // ★ 一覧へ戻さず、同じ承認詳細へ戻す
        return redirect()->route('stamp_correction_request.approve', ['id' => $id]);
    }

    private function isAdmin($user): bool
    {
        return (bool) ($user?->is_admin ?? false);
    }
}