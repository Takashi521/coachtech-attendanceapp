@extends('layouts.app')

@section('content')
<div class="attendance-detail-page">
    <div class="attendance-detail-container">
        <h1 class="attendance-page-title">
            <span class="attendance-page-title__bar"></span>
            勤怠詳細
        </h1>

        <div class="attendance-detail-card">
            @if ($errors->any())
            <div class="form-errors">
                <ul>
                    @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('admin.attendance.update', ['id' => $attendance->id]) }}">
                @csrf

                @php
                $workStart = old('work_start', $attendance->work_start_time);
                $workEnd = old('work_end', $attendance->work_end_time);
                $noteValue = old('note', $attendance->note);
                $date = \Carbon\Carbon::parse($attendance->work_date);
                $b1 = $attendance->breaks[0] ?? null;
                $b2 = $attendance->breaks[1] ?? null;
                
                $b1s = old('break1_start', $b1?->break_start_time ? substr((string) $b1->break_start_time, 0, 5) : '');
                $b1e = old('break1_end',   $b1?->break_end_time   ? substr((string) $b1->break_end_time,   0, 5) : '');
                $b2s = old('break2_start', $b2?->break_start_time ? substr((string) $b2->break_start_time, 0, 5) : '');
                $b2e = old('break2_end',   $b2?->break_end_time   ? substr((string) $b2->break_end_time,   0, 5) : '');

                @endphp

                <table class="attendance-detail-table">
                    <tbody>
                        <tr>
                            <th>名前</th>
                            <td class="attendance-detail-center">
                                <span class="attendance-detail-strong">{{ $attendance->user->name }}</span>
                            </td>
                        </tr>

                        <tr>
                            <th>日付</th>
                            <td class="attendance-detail-center">
                                <span class="attendance-detail-strong">{{ $date->format('Y年') }}</span>
                                <span class="attendance-detail-strong">{{ $date->format('n月j日') }}</span>
                            </td>
                        </tr>

                        <tr>
                            <th>出勤・退勤</th>
                            <td class="attendance-detail-center">
                                <div class="attendance-time-row">
                                    <input class="attendance-time-input" type="time" name="work_start" value="{{ $workStart ? substr($workStart, 0, 5) : '' }}">
                                    <span class="attendance-time-sep">〜</span>
                                    <input class="attendance-time-input" type="time" name="work_end" value="{{ $workEnd ? substr($workEnd, 0, 5) : '' }}">
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th>休憩</th>
                            <td class="attendance-detail-center">
                                <div class="attendance-time-row">
                                    <input class="attendance-time-input" type="time" name="break1_start" value="{{ $b1s }}">
                                    <span class="attendance-time-sep">〜</span>
                                    <input class="attendance-time-input" type="time" name="break1_end" value="{{ $b1e }}">
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th>休憩2</th>
                            <td class="attendance-detail-center">
                                <div class="attendance-time-row">
                                    <input class="attendance-time-input" type="time" name="break2_start" value="{{ $b2s }}">
                                    <span class="attendance-time-sep">〜</span>
                                    <input class="attendance-time-input" type="time" name="break2_end" value="{{ $b2e }}">
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th>備考</th>
                            <td class="attendance-detail-center">
                                <textarea class="attendance-note-input" name="note">{{ $noteValue }}</textarea>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="attendance-detail-actions">
                    <button type="submit" class="btn-correction">修正</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection