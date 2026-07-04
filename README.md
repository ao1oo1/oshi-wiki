# Oshi-Wiki

Oshi-Wiki は、二次創作や創作活動のために、作品・キャラクター・関係性・タグ情報を整理するための Wiki 型管理システムです。

## 目的

作品ごとのキャラクター情報を、客観的・公式寄りの情報として整理します。

AI 二次創作補助ツールとは別機能として扱い、Oshi-Wiki では以下のような情報を管理します。

- 作品情報
- キャラクター情報
- キャラクター同士の関係性
- タグ・分類
- 公開ページ用の情報表示

## 技術スタック

- PHP
- Laravel
- Blade
- Tailwind CSS
- MySQL
- Vite

## アーキテクチャ方針

基本構成は以下の流れに統一します。

Controller
→ FormRequest
→ Service
→ Repository
→ Model
→ Database

## 主な機能

### 管理画面

- 管理トップ
- 作品管理
- キャラクター管理
- キャラクター関係性管理
- タグ管理
- 検索
- タグ絞り込み
- 公開・下書き・非公開ステータス管理

### 公開ページ

- 作品一覧
- 作品詳細
- キャラクター詳細
- published のデータのみ表示

## ステータス

| status | 表示名 | 公開ページ |
|---|---|---|
| published | 公開 | 表示される |
| draft | 下書き | 表示されない |
| private | 非公開 | 表示されない |

## 開発ルール

- 1 Feature ごとに Git commit する
- Controller に処理を詰め込まない
- DB 操作は Repository に寄せる
- ビジネスロジックは Service に寄せる
- 入力チェックは FormRequest に寄せる
- 公開ページには published のデータのみ表示する
- 作品 Wiki と AI 二次創作補助機能は分離して考える

## ローカル起動

composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run dev
php artisan serve

## よく使うコマンド

php artisan serve
npm run dev
php artisan migrate
php artisan optimize:clear
php artisan test

## 管理画面

/admin

## 公開ページ

/
/works
