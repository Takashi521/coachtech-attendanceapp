<!doctype html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>ログイン</title>
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
    <h1>ログイン</h1>

    @if ($errors->any())
    <ul class="form-errors">
        @foreach ($errors->all() as $error)
        <li class="form-errors__item">{{ $error }}</li>
        @endforeach
    </ul>
    @endif




    <form method="POST" action="/login">
        @csrf
        <div>
            <label>メールアドレス</label>
            <input type="email" name="email">
        </div>

        <div>
            <label>パスワード</label>
            <input type="password" name="password">
        </div>

        <button type="submit">ログイン</button>
    </form>

    <p><a href="/register">会員登録へ</a></p>
</body>

</html>