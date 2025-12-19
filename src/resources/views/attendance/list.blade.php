@extends('layouts.app')

@section('content')
<div class="attendance-page">
    <div class="attendance-list-container">
        <h1 class="attendance-page-title">
            <span class="attendance-page-title__bar"></span>
            勤怠一覧
        </h1>

        <div class="attendance-month-nav">
            <a class="attendance-month-nav__btn" href="{{ route('attendance.list', ['month' => $prevMonth]) }}">
                <img class="attendance-month-nav__arrow attendance-month-nav__arrow--prev"
                    src="{{ asset('images/icons/arrow.png') }}" alt="前月">
                <span>前月</span>
            </a>

            <div class="attendance-month-nav__center">
                <img class="attendance-month-nav__calendar"
                    src="{{ asset('images/icons/calendar.png') }}" alt="カレンダー">
                <span class="attendance-month-nav__label">{{ $monthLabel }}</span>
            </div>

            <a class="attendance-month-nav__btn attendance-month-nav__btn--next" href="{{ route('attendance.list', ['month' => $nextMonth]) }}">
                <span>翌月</span>
                <img class="attendance-month-nav__arrow attendance-month-nav__arrow--next"
                    src="{{ asset('images/icons/arrow.png') }}" alt="翌月">
            </a>
        </div>

        <div class="attendance-table-card">
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>日付</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                    @php $a = $row['attendance']; @endphp
                    <tr>
                        <td class="attendance-td--date">{{ $row['label'] }}</td>
                        <td>{{ $a?->work_start_time ? substr($a->work_start_time, 0, 5) : '' }}</td>
                        <td>{{ $a?->work_end_time ? substr($a->work_end_time, 0, 5) : '' }}</td>
                        <td>{{ $a ? $row['break_total'] : '' }}</td>
                        <td>{{ $a ? $row['work_total'] : '' }}</td>
                        <td>
                            @if($a)
                            <a class="attendance-detail-link" href="{{ route('attendance.detail', ['id' => $a->id]) }}">
                                詳細
                            </a>
                            @else
                            <span class="attendance-detail-link attendance-detail-link--empty">詳細</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection