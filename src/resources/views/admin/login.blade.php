@extends('layouts.app')

@section('content')
<div class="attendance-detail-page admin-login-page">
    <div class="attendance-detail-container">
        <h1 class="attendance-page-title">管理者ログイン</h1>

        @if ($errors->any())
            <div class="form-errors admin-login-errors">
                <ul class="admin-login-errors__list">
                    @foreach ($errors->all() as $message)
                        <li class="admin-login-errors__item">{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="attendance-detail-card">
            <form method="POST" action="{{ route('admin.login.store') }}">
                @csrf

                <table class="attendance-detail-table">
                    <tbody>
                        <tr>
                            <th>メールアドレス</th>
                            <td>
                                <input
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    class="attendance-time-input">
                            </td>
                        </tr>

                        <tr>
                            <th>パスワード</th>
                            <td>
                                <input
                                    type="password"
                                    name="password"
                                    class="attendance-time-input">
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="attendance-detail-actions">
                    <button type="submit" class="attendance-btn-primary">
                        管理者ログインする
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection