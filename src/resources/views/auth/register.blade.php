<!doctype html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>会員登録</title>
    <style>
        .form-errors {
            margin: 12px 0;
            padding-left: 18px;
            color: #ff0000;
        }

        .form-errors__item {
            margin: 4px 0;
        }
    </style>
</head>

<body>
    <h1>会員登録</h1>

    @if ($errors->any())
    <ul class="form-errors">
        @foreach ($errors->all() as $error)
        <li class="form-errors__item">{{ $error }}</li>
        @endforeach
    </ul>
    @endif




    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div>
            <label>お名前</label>
            <input type="text" name="name">
        </div>

        <div>
            <label>メールアドレス</label>
            <input type="email" name="email">
        </div>

        <div>
            <label>パスワード</label>
            <input type="password" name="password">
        </div>

        <div>
            <label>確認用パスワード</label>
            <input type="password" name="password_confirmation">
        </div>

        <button type="submit">登録</button>
    </form>

    <p><a href="/login">ログインへ</a></p>
</body>

</html>