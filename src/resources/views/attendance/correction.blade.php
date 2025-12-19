@extends('layouts.app')

@section('content')
<div class="correction-container">
    <h1 class="attendance-detail-title">勤怠詳細</h1>

    <div class="attendance-detail-card">
        <table class="attendance-detail-table">
            <tr>
                <th>名前</th>
                <td>{{ $attendance->user->name ?? '' }}</td>
            </tr>

            <tr>
                <th>日付</th>
                <td>
                    {{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年n月j日') }}
                </td>
            </tr>

            <tr>
                <th>出勤・退勤</th>
                <td>
                    {{ $correctionRequest?->work_start ?? $attendance->work_start }}
                    〜
                    {{ $correctionRequest?->work_end ?? $attendance->work_end }}
                </td>
            </tr>

            <tr>
                <th>休憩</th>
                <td>
                    {{ $correctionRequest?->break_start ?? $attendance->break_start }}
                    〜
                    {{ $correctionRequest?->break_end ?? $attendance->break_end }}
                </td>
            </tr>

            <tr>
                <th>備考</th>
                <td>
                    {{ $correctionRequest?->note ?? $attendance->note }}
                </td>
            </tr>
        </table>
    </div>

    @if($isPending)
    <p class="attendance-detail-note attendance-detail-note--danger">
        ※承認待ちのため修正できません。
    </p>
    @endif

    <div class="attendance-detail-actions">
        <a href="{{ route('attendance.detail', ['id' => $attendance->id]) }}">戻る</a>
    </div>
</div>
@endsection