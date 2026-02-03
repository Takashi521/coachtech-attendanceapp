@extends('layouts.app')

@section('body_class', 'auth-page')

@section('content')
<div class="login-page">
    <div class="login-card">
        <h1 class="login-title">ログイン</h1>

        @if ($errors->any())
           <div class="auth-error-summary auth-error-summary--login">
              @foreach ($errors->all() as $error)
                <p class="auth-error-message">・{{ $error }}</p>
              @endforeach
           </div>
        @endif

        <form class="login-form" method="POST" action="/login">
            @csrf

            <div class="login-field">
                <label class="login-label">メールアドレス</label>
                <input class="login-input" type="email" name="email">
            </div>

            <div class="login-field">
                <label class="login-label">パスワード</label>
                <input class="login-input" type="password" name="password">
            </div>

            <button class="login-submit" type="submit">ログインする</button>
        </form>

        <div class="login-link-wrap">
            <a class="login-link" href="/register">会員登録はこちら</a>
        </div>
    </div>
</div>
@endsection