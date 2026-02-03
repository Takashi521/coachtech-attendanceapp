@extends('layouts.app')

@section('content')
@php
    $statusLabels = [
     'not_worked' => '勤務外',
     'working' => '出勤中',
     'on_break' => '休憩中',
     'done' => '退勤済',
    ];

    $statusLabel = $statusLabels[$attendance->status] ?? $attendance->status;

    // 日付（今日）
    $dateText = \Carbon\Carbon::today()->locale('ja')
                                       ->isoFormat('YYYY年M月D日(ddd)');

    $timeText = \Carbon\Carbon::now()->format('H:i');
@endphp

<div class="attendance-stamp-page">
    <div class="attendance-stamp-card">

        {{-- 1) ステータス（勤務外/出勤中/休憩中/退勤済み） --}}
        <div class="attendance-stamp-badge">
            {{ $statusLabel }}
        </div>

        {{-- 2) 日付 --}}
        <div class="attendance-stamp-date">
            {{ $dateText }}
        </div>

        {{-- 3) 時刻 --}}
        <div class="attendance-stamp-time">
            {{ $timeText }}
        </div>

        {{-- ボタンエリア --}}
        <div class="attendance-stamp-actions">
            @if($attendance->status === 'not_worked')
                <form method="POST" action="{{ route('attendance.work_start') }}">
                    @csrf
                    <button type="submit" class="attendance-stamp-btn attendance-stamp-btn--black">
                        出勤
                    </button>
                </form>
            @endif

            @if($attendance->status === 'working')
                <form method="POST" action="{{ route('attendance.break_start') }}">
                    @csrf
                    <button type="submit" class="attendance-stamp-btn attendance-stamp-btn--white">
                        休憩入
                    </button>
                </form>

                <form method="POST" action="{{ route('attendance.work_end') }}">
                    @csrf
                    <button type="submit" class="attendance-stamp-btn attendance-stamp-btn--black">
                        退勤
                    </button>
                </form>
            @endif

            @if($attendance->status === 'on_break')
                <form method="POST" action="{{ route('attendance.break_end') }}">
                    @csrf
                    <button type="submit" class="attendance-stamp-btn attendance-stamp-btn--white">
                        休憩戻
                    </button>
                </form>
            @endif
        </div>

        @if($attendance->status === 'done')
            <p style="margin-top: 28px; font-family: Inter, Arial, sans-serif; font-weight: 700; font-size: 26px; color: #000;">
                お疲れ様でした。
            </p>
        @endif
    </div>
</div>
@endsection