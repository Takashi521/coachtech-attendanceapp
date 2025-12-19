<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>勤怠管理アプリ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
</head>

<body>

    <header class="site-header">
        <div class="site-header__inner">
            <div class="site-header__logo">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('images/logo.png') }}" alt="COACHTECH">
                </a>
            </div>

            @php
            $isLoggedIn = auth()->check();
            $role = $isLoggedIn ? (string) auth()->user()->role : '';
            $isAdmin = $isLoggedIn && ($role === 'admin');

            $isAttendanceDonePage = $isLoggedIn
            && request()->routeIs('attendance.show')
            && isset($attendance)
            && $attendance->status === 'done';
            @endphp

            {{-- ログイン前：ロゴのみ --}}
            @if($isLoggedIn)
            <nav class="site-header__nav">
                <ul class="site-header__nav-list">

                    {{-- 管理者 --}}
                    @if($isAdmin)
                    <li><a class="nav-link nav-link--bold" href="{{ url('/admin/attendance/list') }}">勤怠一覧</a></li>
                    <li><a class="nav-link nav-link--bold" href="{{ url('/admin/staff/list') }}">スタッフ一覧</a></li>
                    <li><a class="nav-link nav-link--bold" href="{{ url('/stamp_correction_request/list') }}">申請一覧</a></li>

                    {{-- 一般ユーザー：退勤後（勤怠登録画面のみ） --}}
                    @elseif($isAttendanceDonePage)
                    <li><a class="nav-link nav-link--regular" href="{{ url('/attendance/list') }}">今月の出勤一覧</a></li>
                    <li><a class="nav-link nav-link--regular" href="{{ url('/stamp_correction_request/list') }}">申請一覧</a></li>

                    {{-- 一般ユーザー：通常 --}}
                    @else
                    <li><a class="nav-link nav-link--bold" href="{{ url('/attendance') }}">勤怠</a></li>
                    <li><a class="nav-link nav-link--bold" href="{{ url('/attendance/list') }}">勤怠一覧</a></li>
                    <li><a class="nav-link nav-link--bold" href="{{ url('/stamp_correction_request/list') }}">申請</a></li>
                    @endif

                    {{-- ログアウト（共通） --}}
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="nav-link nav-link--bold nav-button">ログアウト</button>
                        </form>
                    </li>

                </ul>
            </nav>
            @endif
        </div>
    </header>

    <main class="site-main">
        @yield('content')
    </main>

</body>

</html>