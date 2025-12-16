@extends('layouts.app')

@section('content')
<div>
    <h1>打刻</h1>

    @php
    $statusLabels = [
    'not_worked' => '勤務外',
    'working' => '出勤中',
    'on_break' => '休憩中',
    'done' => '退勤済',
    ];
    @endphp

    <p>ステータス：{{ $statusLabels[$attendance->status] ?? $attendance->status }}</p>
    <p>出勤：{{ $attendance->work_start_time ?? '-' }}</p>
    <p>退勤：{{ $attendance->work_end_time ?? '-' }}</p>

    @if(session('message'))
    <p>{{ session('message') }}</p>
    @endif

    @if($attendance->status === 'not_worked')
    <form method="POST" action="{{ route('attendance.work_start') }}">
        @csrf
        <button type="submit">出勤</button>
    </form>
    @endif

    @if($attendance->status === 'working')
    <form method="POST" action="{{ route('attendance.break_start') }}">
        @csrf
        <button type="submit">休憩入</button>
    </form>

    <form method="POST" action="{{ route('attendance.work_end') }}">
        @csrf
        <button type="submit">退勤</button>
    </form>
    @endif

    @if($attendance->status === 'on_break')
    <form method="POST" action="{{ route('attendance.break_end') }}">
        @csrf
        <button type="submit">休憩戻</button>
    </form>
    @endif

    @if($attendance->status === 'done')
    <p>お疲れ様でした。</p>
    @endif


    <h2>休憩一覧</h2>
    <ul>
        @foreach($breaks as $break)
        <li>
            {{ $break->break_order }}回目：
            {{ $break->break_start_time }} 〜 {{ $break->break_end_time }}
        </li>
        @endforeach
    </ul>
</div>
@endsection