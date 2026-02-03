@extends('layouts.app')

@section('content')
<div class="attendance-page admin-attendance-page">
    <div class="attendance-list-container">
        <h1 class="attendance-page-title">
            <span class="attendance-page-title__bar"></span>
            {{ $date->format('Y年n月j日') }}の勤怠
        </h1>

        <div class="admin-day-nav">
            <a class="admin-day-nav__btn" href="{{ route('admin.attendance.index', ['date' => $prevDate->toDateString()]) }}">
                <img class="admin-day-nav__arrow" src="{{ asset('images/icons/arrow.png') }}" alt="prev">
                <span>前日</span>
            </a>

            <div class="admin-day-nav__center">
                <img class="admin-day-nav__calendar" src="{{ asset('images/icons/calendar.png') }}" alt="calendar">
                <span class="admin-day-nav__label">{{ $date->format('Y/m/d') }}</span>
            </div>

            <a class="admin-day-nav__btn" href="{{ route('admin.attendance.index', ['date' => $nextDate->toDateString()]) }}">
                <span>翌日</span>
                <img class="admin-day-nav__arrow admin-day-nav__arrow--next" src="{{ asset('images/icons/arrow.png') }}" alt="next">
            </a>
        </div>


        <div class="admin-attendance-table-card">
            <table class="admin-attendance-table">
                <thead>
                    <tr>
                        <th>名前</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                    <tr>
                        <td class="admin-attendance-td--name">{{ $row['user_name'] }}</td>
                        <td>{{ $row['work_start'] }}</td>
                        <td>{{ $row['work_end'] }}</td>
                        <td>{{ $row['break'] }}</td>
                        <td>{{ $row['total'] }}</td>
                        <td>
                            @if($row['attendance_id'])
                            <a class="admin-attendance-detail-link" href="{{ route('admin.attendance.detail', ['id' => $row['attendance_id']]) }}">詳細</a>
                            @else
                            <span class="admin-attendance-detail-link admin-attendance-detail-link--empty">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="admin-attendance-empty">データがありません</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection