# アプリケーション名
- Coachtech-attendanceapp

# 環境構築

### 前提
- Docker / Docker Compose が利用できること

### 1.リポジトリの取得
```bash
git clone <リポジトリURL>
cd coachtech-attendanceapp
```
### 2.コンテナ起動
```bash
docker compose up -d --build
```

### 3.Laravel初期設定(コンテナ内で実行)
```bash
docker compose exec php bash
```

### 4.コンテナ内で以下を実行
``` bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

## ER図

 ![ER図](./docs/er-diagram.png)


## Tests
Run tests:

```bash
docker compose exec php bash -lc "php artisan test"
```

## Test evidence
- Test matrix: ./docs/test-matrix.md
- Manual test results: ./docs/manual-test-results.md

## ログイン情報
### 管理者（Seederで作成）
`php artisan migrate --seed` 実行後、以下でログインできます。

- email: admin@example.com
- password: password123

### 一般ユーザー
一般ユーザーは `/register` から任意のメールアドレス・パスワードで新規登録してログインしてください。


## URL
- アプリ: http://localhost:8081/
- phpMyAdmin: http://localhost:8080/

## 主な画面
- User: /login , /attendance , /attendance/list　
- Admin: /admin/login , /admin/attendance , /admin/staff

## 使用技術
• PHP 8.1.34
• Laravel 8.83.29
• MySQL 8.0.45

## 設計資料
- 基本設計書：別途模擬案件要件シートに貼り付け
- テーブル設計書：別途模擬案件要件シートに貼り付け