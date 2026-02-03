@extends('layouts.app')

@section('content')
<div class="attendance-detail-page">
    <div class="attendance-detail-container">
        <h1 class="attendance-page-title">
            <span class="attendance-page-title__bar"></span>
            勤怠詳細
        </h1>

        <div class="attendance-detail-card">
            @php
                $isPending = $isPending ?? false;

                $workStart = old('work_start_time', $displayWorkStart ?? $attendance->work_start_time);
                $workEnd = old('work_end_time', $displayWorkEnd ?? $attendance->work_end_time);

                $noteValue = old('note', $displayNote ?? $attendance->note);

                $breakRows = $displayBreaks ?? $attendance->breaks; // Collection想定
                $breakCount = $breakRows ? $breakRows->count() : 0;
                $rowsToShow = $isPending ? $breakCount : ($breakCount + 1);
                $breakLabelNo = 1;
            @endphp

            @if ($errors->any())
                <div class="form-errors">
                    <ul>
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('attendance.correction_request', ['id' => $attendance->id]) }}">
                @csrf

                <table class="attendance-detail-table">
                    <tbody>
                        {{-- 名前 --}}
                        <tr>
                            <th>名前</th>
                            <td class="attendance-detail-center">
                                <span class="attendance-detail-strong">{{ $user->name }}</span>
                            </td>
                        </tr>

                        {{-- 日付（★ここが壊れていたので正しいtrで復元） --}}
                        <tr>
                            <th>日付</th>
                            <td class="attendance-detail-center">
                                <div class="attendance-date-row">
                                    <span class="attendance-detail-strong">{{ $targetDate->format('Y年') }}</span>
                                    <span class="attendance-detail-strong">{{ $targetDate->format('n月j日') }}</span>
                                </div>
                            </td>
                        </tr>

                        {{-- 出勤・退勤 --}}
                        <tr>
                            <th>出勤・退勤</th>
                            <td class="attendance-detail-center">
                                <div class="attendance-time-row {{ $isPending ? 'attendance-time-row--pending' : '' }}">
                                    @if ($isPending)
                                        <span class="attendance-detail-strong">{{ $workStart ? substr($workStart, 0, 5) : '-' }}</span>
                                    @else
                                        <input class="attendance-time-input" type="time" name="work_start_time" value="{{ $workStart ? substr($workStart, 0, 5) : '' }}">
                                    @endif

                                    <span class="attendance-time-sep">〜</span>

                                    @if ($isPending)
                                        <span class="attendance-detail-strong">{{ $workEnd ? substr($workEnd, 0, 5) : '-' }}</span>
                                    @else
                                        <input class="attendance-time-input" type="time" name="work_end_time" value="{{ $workEnd ? substr($workEnd, 0, 5) : '' }}">
                                    @endif
                                </div>
                            </td>
                        </tr>

                        {{-- 休憩（可変） --}}
                        @for ($i = 0; $i < $rowsToShow; $i++)
                            @php
                                $b = $breakRows[$i] ?? null;

                                $bsName = "break_start_time[{$i}]";
                                $beName = "break_end_time[{$i}]";

                                $bsOldKey = "break_start_time.{$i}";
                                $beOldKey = "break_end_time.{$i}";

                                $defaultBs = $b?->requested_break_start_time ?? $b?->break_start_time;
                                $defaultBe = $b?->requested_break_end_time ?? $b?->break_end_time;

                                $bs = old($bsOldKey, $defaultBs);
                                $be = old($beOldKey, $defaultBe);

                                $bsText = $bs ? substr($bs, 0, 5) : '';
                                $beText = $be ? substr($be, 0, 5) : '';
                            @endphp

                            @continue(($bsText === '' && $beText === '') && ($isPending || $i < $breakCount))

                            <tr>
                                <th>{{ $breakLabelNo === 1 ? '休憩' : '休憩' . $breakLabelNo }}</th>
                                <td class="attendance-detail-center">
                                    <div class="attendance-time-row {{ $isPending ? 'attendance-time-row--pending' : '' }}">
                                        @if ($isPending)
                                            <span class="attendance-detail-strong">{{ $bsText }}</span>
                                        @else
                                            <input class="attendance-time-input" type="time" name="{{ $bsName }}" value="{{ $bsText }}">
                                        @endif

                                        <span class="attendance-time-sep">〜</span>

                                        @if ($isPending)
                                            <span class="attendance-detail-strong">{{ $beText }}</span>
                                        @else
                                            <input class="attendance-time-input" type="time" name="{{ $beName }}" value="{{ $beText }}">
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            @php $breakLabelNo++; @endphp
                        @endfor

                        {{-- 備考 --}}
                        <tr>
                            <th>備考</th>
                            <td class="attendance-detail-center">
                                @if ($isPending)
                                    <span class="attendance-detail-strong">{{ $noteValue }}</span>
                                @else
                                    <textarea class="attendance-note-input" name="note">{{ $noteValue }}</textarea>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="attendance-detail-actions">
                    @if (!$isPending)
                        <button type="submit" class="btn-correction">修正</button>
                    @endif
                </div>

                @if ($isPending)
                    <p class="attendance-pending-note">*承認待ちのため修正はできません。</p>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection
