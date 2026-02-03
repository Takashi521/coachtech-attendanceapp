<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>勤怠管理アプリ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
</head>

<body class="@yield('body_class')">

    <header class="site-header">
        <div class="site-header__inner">
            <div class="site-header__logo">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('images/logo.png') }}" alt="COACHTECH">
                </a>
            </div>

            @auth
            <nav class="site-header__nav">
                <ul class="site-header__nav-list">
                    @if(auth()->user()->is_admin)
                    {{-- 管理者 --}}
                    <li><a class="nav-link nav-link--bold" href="{{ url('/admin/attendance') }}">勤怠一覧</a></li>
                    <li>
                        <a class="nav-link nav-link--bold" href="{{ route('admin.staff.index') }}">スタッフ一覧</a>
                    </li>
                    <li><a class="nav-link nav-link--bold" href="{{ url('/stamp_correction_request/list') }}">申請一覧</a></li>

                    <li>
                        <form method="POST" action="/admin/logout">
                            @csrf
                            <button type="submit" class="nav-link nav-link--bold nav-button">ログアウト</button>
                        </form>
                    </li>
                @else
                    {{-- 一般ユーザー --}}
                    @if(auth()->user()->hasVerifiedEmail())

                            @php
                              $isStampDone = request()->routeIs('attendance.show')
                                 && isset($attendance)
                                 && ($attendance->status === 'done');
                            @endphp

                            @if($isStampDone)
                             <li><a class="nav-link nav-link--bold" href="{{ route('attendance.list') }}">今月の出勤一覧</a></li>
                             <li><a class="nav-link nav-link--bold" href="{{ route('stamp_correction_request.list') }}">申請一覧</a></li>
                            <li>
                               <form method="POST" action="/logout">
                                   @csrf
                                   <button type="submit" class="nav-link nav-link--bold nav-button">ログアウト</button>
                               </form>
                            </li>
                        @else
                            <li><a class="nav-link nav-link--bold" href="{{ route('attendance.show') }}">勤怠</a></li>
                            <li><a class="nav-link nav-link--bold" href="{{ route('attendance.list') }}">勤怠一覧</a></li>
                            <li><a class="nav-link nav-link--bold" href="{{ route('stamp_correction_request.list') }}">申請</a></li>
                            <li>
                               <form method="POST" action="/logout">
                                  @csrf
                                  <button type="submit" class="nav-link nav-link--bold nav-button">ログアウト</button>
                               </form>
                            </li>
                        @endif
                        
                    @endif
                @endif
                </ul>
            </nav>
            @endauth
        </div>
    </header>

    <main class="site-main">
        @yield('content')
    </main>

</body>

</html>