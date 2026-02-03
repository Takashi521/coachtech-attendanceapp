@extends('layouts.app')

@section('content')
<div class="attendance-page admin-staff-monthly-page">
    <div class="attendance-list-container">
        <h1 class="attendance-page-title admin-staff-monthly-title">
            <span class="attendance-page-title__bar"></span>
            {{ $targetUser->name }}さんの勤怠
        </h1>

        <div class="admin-day-nav">
            <a class="admin-day-nav__btn"
                href="{{ route('admin.staff.attendance', ['user' => $targetUser->id, 'month' => $prevMonth->format('Y-m')]) }}">
                <img class="admin-month-nav__arrow" src="{{ asset('images/icons/arrow.png') }}" alt="prev">
                <span>前月</span>
            </a>

            <div class="admin-day-nav__center">
                <img class="admin-month-nav__calendar" src="{{ asset('images/icons/calendar.png') }}" alt="calendar">
                <span class="admin-day-nav__label">{{ $monthCarbon->format('Y/m') }}</span>
            </div>

            <a class="admin-day-nav__btn"
                href="{{ route('admin.staff.attendance', ['user' => $targetUser->id, 'month' => $nextMonth->format('Y-m')]) }}">
                <span>翌月</span>
                <img class="admin-month-nav__arrow admin-month-nav__arrow--next"
                    src="{{ asset('images/icons/arrow.png') }}" alt="next">
            </a>
        </div>

        <div class="admin-attendance-table-card">
            <table class="admin-attendance-table">
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
                    @foreach($days as $day)
                    @php
                    $dateKey = $day->toDateString();
                    $a = $attendances->get($dateKey);

                    $ws = $a?->work_start_time ? substr($a->work_start_time, 0, 5) : '-';
                    $we = $a?->work_end_time ? substr($a->work_end_time, 0, 5) : '-';
                    @endphp
                    <tr>
                        <td class="admin-attendance-td--date">{{ $day->format('n/j') }}</td>
                        <td>{{ $ws }}</td>
                        <td>{{ $we }}</td>
                        <td>-</td>
                        <td>-</td>
                        <td>
                            @if($a)
                            <a class="admin-attendance-detail-link"
                                href="{{ route('admin.attendance.detail', ['id' => $a->id]) }}">詳細</a>
                            @else
                            <span class="admin-attendance-detail-link admin-attendance-detail-link--disabled">詳細</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- CSV出力ボタン（表の下） --}}
        <div class="admin-csv-actions">
            <a class="admin-csv-btn"
                href="{{ route('admin.staff.attendance.csv', ['user' => $targetUser->id, 'month' => $monthCarbon->format('Y-m')]) }}">
                CSV出力
            </a>
        </div>
    </div>
</div>
@endsection