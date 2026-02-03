@extends('layouts.app')

@section('content')
@php
$req = $correctionRequest;

$userName = $req->user?->name ?? '';

$workDate = $req->attendance?->work_date;
$dateYearText = $workDate ? \Carbon\Carbon::parse($workDate)->format('Y年') : '';
$dateMdText = $workDate ? \Carbon\Carbon::parse($workDate)->format('n月j日') : '';

$workStart = $req->requested_work_start_time ? substr($req->requested_work_start_time, 0, 5) : '';
$workEnd = $req->requested_work_end_time ? substr($req->requested_work_end_time, 0, 5) : '';

$note = $req->requested_note ?? '';

$break1 = $req->breaks->firstWhere('break_order', 1);
$break2 = $req->breaks->firstWhere('break_order', 2);

$b1s = $break1?->requested_break_start_time ? substr($break1->requested_break_start_time, 0, 5) : '';
$b1e = $break1?->requested_break_end_time ? substr($break1->requested_break_end_time, 0, 5) : '';

$b2s = $break2?->requested_break_start_time ? substr($break2->requested_break_start_time, 0, 5) : '';
$b2e = $break2?->requested_break_end_time ? substr($break2->requested_break_end_time, 0, 5) : '';

$isApproved = $req->status === 'approved';
@endphp

<div class="attendance-detail-page attendance-approve-page">
    <div class="attendance-detail-container">
        <h1 class="attendance-page-title">
            <span class="attendance-page-title__bar"></span>
            勤怠詳細
        </h1>

        <div class="attendance-detail-card">
            <table class="attendance-detail-table">
                <tbody>
                    <tr>
                        <th class="attendance-detail__label">名前</th>
                        <td class="attendance-detail__value attendance-detail__value--left" colspan="3">
                            {{ $userName }}
                        </td>
                    </tr>

                    <tr>
                        <th class="attendance-detail__label">日付</th>
                        <td class="attendance-detail__value attendance-detail__date" colspan="3">
                            <span class="attendance-detail__date-year">{{ $dateYearText }}</span>
                            <span class="attendance-detail__date-md">{{ $dateMdText }}</span>
                        </td>
                    </tr>

                    <tr>
                        <th>出勤・退勤</th>
                        <td class="attendance-detail-center" colspan="3">
                            @if($workStart || $workEnd)
                            <div class="attendance-time-row">
                                <span class="attendance-detail-strong attendance-time-text">{{ $workStart }}</span>
                                <span class="attendance-time-sep">〜</span>
                                <span class="attendance-detail-strong attendance-time-text">{{ $workEnd }}</span>
                            </div>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th>休憩</th>
                        <td class="attendance-detail-center" colspan="3">
                            @if($b1s || $b1e)
                            <div class="attendance-time-row">
                                <span class="attendance-detail-strong attendance-time-text">{{ $b1s }}</span>
                                <span class="attendance-time-sep">〜</span>
                                <span class="attendance-detail-strong attendance-time-text">{{ $b1e }}</span>
                            </div>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th>休憩2</th>
                        <td class="attendance-detail-center" colspan="3">
                            @if($b2s || $b2e)
                            <div class="attendance-time-row">
                                <span class="attendance-detail-strong attendance-time-text">{{ $b2s }}</span>
                                <span class="attendance-time-sep">〜</span>
                                <span class="attendance-detail-strong attendance-time-text">{{ $b2e }}</span>
                            </div>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th class="attendance-detail__label">備考</th>
                        <td class="attendance-detail__value attendance-detail__value--left" colspan="3">
                            {{ $note }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="attendance-detail-action">
            @if($isApproved)
            <div class="attendance-approved-badge">承認済み</div>
            @else
            <form method="POST" action="{{ route('stamp_correction_request.approve_update', ['id' => $req->id]) }}">
                @csrf
                <input type="hidden" name="action" value="approve">
                <button type="submit" class="attendance-approve-btn">承認</button>
            </form>
            @endif
        </div>

        @if($errors->any())
        <div class="form-errors">
            <ul>
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
</div>
@endsection