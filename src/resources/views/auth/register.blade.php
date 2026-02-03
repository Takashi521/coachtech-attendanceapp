@extends('layouts.app')

@section('body_class', 'auth-page')

@section('content')
<div class="register-page">
    <div class="register-card">
        <h1 class="register-title">会員登録</h1>

    @if ($errors->any())
    　<div class="register-error-summary">
        @foreach ($errors->all() as $error)
            <p class="register-error-message">・{{ $error }}</p>
        @endforeach
    　</div>
　　@endif

    <form class="register-form" method="POST" action="{{ route('register') }}">
    @csrf

    <div class="register-field">
        <label class="register-label" for="name">名前</label>
        <input class="register-input" id="name" type="text" name="name" value="{{ old('name') }}">

    <div class="register-field">
        <label class="register-label" for="email">メールアドレス</label>
        <input class="register-input" id="email" type="email" name="email" value="{{ old('email') }}">
    </div>

    <div class="register-field">
        <label class="register-label" for="password">パスワード</label>
        <input class="register-input" id="password" type="password" name="password">
    </div>

    <div class="register-field">
        <label class="register-label" for="password_confirmation">パスワード確認</label>
        <input class="register-input" id="password_confirmation" type="password" name="password_confirmation">
    </div>

    <button class="register-submit" type="submit">登録する</button>
    </form>

        <div class="register-link-wrap">
            <a class="register-link" href="{{ route('login') }}">ログインはこちら</a>
        </div>
    </div>
</div>
@endsection