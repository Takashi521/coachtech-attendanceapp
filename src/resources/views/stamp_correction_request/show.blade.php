@extends('layouts.app')

@section('content')
@php
$req = $correctionRequest;
$attendance = $req->attendance;

$targetDate = $attendance?->work_date ? \Carbon\Carbon::parse($attendance->work_date) : null;

$workStart = $req->requested_work_start_time ? substr($req->requested_work_start_time, 0, 5) : '-';
$workEnd = $req->requested_work_end_time ? substr($req->requested_work_end_time, 0, 5) : '-';

$note = $req->requested_note ?? '';

$breaks = $req->breaks ?? collect();
$isPending = $req->status === 'pending';
@endphp

<div class="attendance-detail-page">
    <div class="attendance-detail-container">
        <h1 class="attendance-page-title">
            <span class="attendance-page-title__bar"></span>
            申請詳細
        </h1>

        <div class="attendance-detail-card">
            <table class="attendance-detail-table">
                <tbody>
                    <tr>
                        <th>名前</th>
                        <td class="attendance-detail-center">
                            <span class="attendance-detail-strong">{{ $req->user?->name ?? '-' }}</span>
                        </td>
                    </tr>

                    <tr>
                        <th>日付</th>
                        <td class="attendance-detail-center">
                            @if($targetDate)
                            <span class="attendance-detail-strong">{{ $targetDate->format('Y年') }}</span>
                            <span class="attendance-detail-strong">{{ $targetDate->format('n月j日') }}</span>
                            @else
                            <span class="attendance-detail-strong">-</span>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th>出勤・退勤</th>
                        <td class="attendance-detail-center">
                            <div class="attendance-time-row">
                                <span class="attendance-detail-strong">{{ $workStart }}</span>
                                <span class="attendance-time-sep">〜</span>
                                <span class="attendance-detail-strong">{{ $workEnd }}</span>
                            </div>
                        </td>
                    </tr>

                    @php $breakLabelNo = 1; @endphp
                    @foreach($breaks as $b)
                    @php
                    $bs = $b->requested_break_start_time ? substr($b->requested_break_start_time, 0, 5) : '';
                    $be = $b->requested_break_end_time ? substr($b->requested_break_end_time, 0, 5) : '';
                    @endphp

                    @continue($bs === '' && $be === '')

                    <tr>
                        <th>{{ $breakLabelNo === 1 ? '休憩' : '休憩' . $breakLabelNo }}</th>
                        <td class="attendance-detail-center">
                            <div class="attendance-time-row">
                                <span class="attendance-detail-strong">{{ $bs }}</span>
                                <span class="attendance-time-sep">〜</span>
                                <span class="attendance-detail-strong">{{ $be }}</span>
                            </div>
                        </td>
                    </tr>

                    @php $breakLabelNo++; @endphp
                    @endforeach

                    <tr>
                        <th>備考</th>
                        <td class="attendance-detail-center">
                            <span class="attendance-detail-strong">{{ $note }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>

            @if($isPending)
            <p class="attendance-pending-note">※承認待ちのため修正はできません。</p>
            @else
            <p class="attendance-pending-note" style="color:#666;">承認済み</p>
            @endif
        </div>
    </div>
</div>
@endsection