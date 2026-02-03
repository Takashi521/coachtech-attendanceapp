@extends('layouts.app')

@section('content')
<div class="verify-page">
    <div class="verify-card">
        <p class="verify-message">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        <div class="verify-actions">
            {{-- Mailtrapでメールを確認する導線（メール内リンクを踏んで認証完了） --}}
            <a class="verify-btn" href="https://localhost:8025" target="_blank" rel="noopener">
                認証はこちらから
            </a>
        </div>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="verify-resend">認証メールを再送する</button>
        </form>
    </div>
</div>
@endsection