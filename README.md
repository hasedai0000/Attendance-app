# 勤怠管理システム

## プロジェクトの概要

勤怠管理システムは、Laravel で構築された勤怠管理アプリケーションです。一般ユーザーが勤怠の打刻・確認・修正申請を行い、管理者が勤怠情報の管理・承認を行うプラットフォームを提供します。

### 主な機能

- **一般ユーザー機能**

  - 新規会員登録・ログイン・ログアウト
  - メール認証機能（MailHog/Mailtrap）
  - 勤怠打刻（出勤・休憩・退勤）
  - 勤怠一覧・詳細確認
  - 勤怠修正申請
  - 申請状況確認（承認待ち・承認済み）

- **管理者機能**

  - 管理者ログイン・ログアウト
  - 日次勤怠一覧確認
  - スタッフ一覧・月次勤怠確認
  - 勤怠詳細確認・修正
  - 修正申請承認
  - CSV 出力機能

- **その他**
  - レスポンシブデザイン対応
  - バリデーション機能
  - CI/CD (GitHub Actions)

## 使用技術

### バックエンド

- **PHP** 7.3|8.0
- **Laravel** 8.x
- **MySQL** 8.0.26

### フロントエンド

- **HTML5/CSS3**
- **JavaScript**
- **Blade テンプレートエンジン**

### 認証・メール

- **Laravel Fortify** (認証機能)
- **MailHog/Mailtrap** (メール送信テスト)

### 開発・運用環境

- **Docker** & **Docker Compose**
- **Nginx** 1.21.1
- **PHPMyAdmin** (データベース管理)
- **MailHog** (メール送信テスト)

### 開発ツール

- **PHPStan** (静的解析)
- **PHP CodeSniffer** (コード規約チェック)
- **PHP CS Fixer** (コード整形)
- **PHPUnit** (テスト)

## 環境構築手順

### 前提条件

以下のソフトウェアがインストールされていることを確認してください：

- Docker Desktop
- Docker Compose

### 1. リポジトリのクローン

```bash
# SSHでクローンする場合
git clone git@github.com:hasedai0000/attendance-app.git

# HTTPSでクローンする場合
git clone https://github.com/hasedai0000/attendance-app.git

cd Attendance-app
```

### 2. 環境の起動

```bash
# Dockerコンテナをビルドして起動
docker compose up -d --build
```

### 3. Laravel アプリケーションのセットアップ

```bash
# PHPコンテナに入る
docker compose exec php bash

# 依存関係のインストール
composer install

# 環境設定ファイルのコピー
cp .env.example .env

# アプリケーションキーの生成
php artisan key:generate

# ストレージのシンボリックリンクを作成する
php artisan storage:link

# データベースマイグレーションとシーダーの実行
php artisan migrate:fresh --seed

```

#### 3.1 データベース接続情報

```bash

# .envにDB接続情報の設定
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
DB_HOST=mysql #（コンテナ間通信）

# データベースマイグレーションとシーダーの実行
php artisan migrate:fresh --seed

```

### 4. 動作確認

ブラウザで以下の URL にアクセスして、アプリケーションが正常に動作することを確認してください：

- **アプリケーション**: http://localhost/login
- **PHPMyAdmin**: http://localhost:8080
- **MailHog**: http://localhost:8025

### 5. 2 回目以降の起動

```bash
docker compose up -d
```

## ログイン情報

### 管理者ユーザー

| ユーザー名     | メールアドレス    | パスワード | 状態     |
| -------------- | ----------------- | ---------- | -------- |
| 管理者         | admin@example.com | password   | 認証済み |
| テストユーザー | test@example.com  | password   | 認証済み |

### 一般ユーザー（認証済み）

以下のアカウントでログインできます：

| ユーザー名 | メールアドレス        | パスワード | 状態     |
| ---------- | --------------------- | ---------- | -------- |
| 田中太郎   | tanaka@example.com    | password   | 認証済み |
| 佐藤花子   | sato@example.com      | password   | 認証済み |
| 鈴木美咲   | suzuki@example.com    | password   | 認証済み |
| 高橋健太   | takahashi@example.com | password   | 認証済み |
| 渡辺翔太   | watanabe@example.com  | password   | 認証済み |
| 中村優子   | nakamura@example.com  | password   | 認証済み |
| 小林大輔   | kobayashi@example.com | password   | 認証済み |
| 加藤結衣   | kato@example.com      | password   | 認証済み |
| 森田浩司   | morita@example.com    | password   | 認証済み |
| 松本あかり | matsumoto@example.com | password   | 認証済み |
| 橋本拓也   | hashimoto@example.com | password   | 認証済み |
| 清水恵子   | shimizu@example.com   | password   | 認証済み |

### メール未認証ユーザー

| ユーザー名 | メールアドレス     | パスワード | 状態   |
| ---------- | ------------------ | ---------- | ------ |
| 山田次郎   | yamada@example.com | password   | 未認証 |
| 伊藤愛     | ito@example.com    | password   | 未認証 |

## メール設定

### MailHog の設定

開発環境では MailHog を使用してメール送信をテストできます。

- **MailHog URL**: http://localhost:8025
- **SMTP 設定**: ローカルホストの 1025 ポートを使用

### メール認証機能

- 新規会員登録時にメール認証が送信されます
- メール認証が完了していない場合は、ログイン時に認証誘導画面が表示されます
- 認証メールの再送機能も利用できます

## 開発時の操作

### コンテナの操作

```bash
# コンテナを停止
docker compose stop

# コンテナを停止して削除
docker compose down

# コンテナを再起動
docker compose restart
```

### ログの確認

```bash
# 全サービスのログを確認
docker compose logs

# 特定のサービスのログを確認
docker compose logs nginx
docker compose logs php
docker compose logs mysql
```

### コンテナ内での作業

```bash
# PHPコンテナに入る
docker compose exec php bash

# Nginxコンテナに入る
docker compose exec nginx bash

# MySQLコンテナに入る
docker compose exec mysql bash
```

### MySQL への直接アクセス

```bash
# MySQLコンテナ内でMySQLにログイン
docker compose exec mysql mysql -u laravel_user -p laravel_db
# パスワード: attendance_pass
```

### 開発用コマンド

```bash
# PHPコンテナ内で実行

# マイグレーションの実行
php artisan migrate

# シーダーの実行
php artisan db:seed

# テスト用のDBを作成
docker compose exec mysql mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS laravel_test;"

# マイグレーション
docker compose exec php php artisan migrate --env=testing

# テストを実行
docker compose exec php php artisan test
```

## CI/CD

このプロジェクトでは GitHub Actions を使用して CI/CD パイプラインを構築しています。

### 自動テスト

プッシュ・プルリクエスト時に以下が自動実行されます：

- **テスト実行**: PHPUnit を使用したユニットテスト・フィーチャーテスト

### マトリックステスト

複数の PHP バージョンでテストを実行：

- PHP 8.0

### ワークフロー

CI 設定ファイル: `.github/workflows/ci.yml`

```bash
# ローカルで同じテストを実行する場合
cd src
composer test      # PHPUnit テストを実行
```

## ファイル構成

```
Attendance-app/
├── docker/                 # Docker設定
│   ├── nginx/
│   │   └── default.conf    # Nginx設定
│   ├── php/
│   │   ├── Dockerfile      # PHP設定
│   │   └── php.ini         # PHP設定
│   └── mysql/
│       └── my.cnf          # MySQL設定
├── src/                    # Laravelアプリケーション
│   ├── app/                # アプリケーションロジック
│   ├── database/           # マイグレーション・シーダー
│   ├── public/             # 公開ファイル
│   ├── resources/          # ビュー・CSS・JS
│   └── routes/             # ルート定義
├── docker-compose.yml      # Docker Compose設定
└── README.md              # このファイル
```

## トラブルシューティング

### ポート競合の場合

ポート 80 が使用中の場合、`docker-compose.yml`を編集：

```yaml
services:
  nginx:
    ports:
      - "8080:80" # ホストの8080ポートを使用
```

アクセス URL: http://localhost:8080

### コンテナが起動しない場合

```bash
# コンテナの状態確認
docker compose ps

# ログでエラー確認
docker compose logs

# 完全にクリーンアップして再構築
docker compose down --volumes --remove-orphans
docker compose up --build
```

### データベース接続エラー

```bash
# PHPコンテナ内で接続確認
docker compose exec php php artisan tinker
DB::connection()->getPdo();
```
