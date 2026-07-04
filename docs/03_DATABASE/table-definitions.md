# Oshi-Wiki テーブル詳細設計

## 1. roles

### 目的

管理者・キュレーター・レビュアーなどの役割を管理する。

### 主な用途

- 管理画面の権限制御
- 編集可能範囲の判定
- 承認権限の判定

### カラム定義

| カラム | 型 | 必須 | 説明 |
|---|---|---|---|
| id | BIGINT UNSIGNED | 必須 | 主キー |
| name | VARCHAR(100) | 必須 | システム内部名 |
| label | VARCHAR(100) | 必須 | 表示名 |
| description | TEXT | 任意 | 説明 |
| created_at | DATETIME | 必須 | 作成日時 |
| updated_at | DATETIME | 必須 | 更新日時 |

### 初期データ

| name | label |
|---|---|
| super_admin | 最高管理者 |
| admin | 管理者 |
| curator | キュレーター |
| reviewer | レビュアー |
| viewer_staff | 閲覧スタッフ |

---

## 2. permissions

### 目的

管理画面で実行できる操作権限を管理する。

### 主な用途

- 作品編集権限
- キャラクター編集権限
- 承認権限
- 権限変更権限
- 削除権限

### カラム定義

| カラム | 型 | 必須 | 説明 |
|---|---|---|---|
| id | BIGINT UNSIGNED | 必須 | 主キー |
| permission_key | VARCHAR(150) | 必須 | 権限キー |
| label | VARCHAR(150) | 必須 | 表示名 |
| description | TEXT | 任意 | 説明 |
| created_at | DATETIME | 必須 | 作成日時 |
| updated_at | DATETIME | 必須 | 更新日時 |

### 初期データ例

| permission_key | label |
|---|---|
| works.view | 作品閲覧 |
| works.create | 作品作成 |
| works.update | 作品編集 |
| works.delete | 作品削除 |
| characters.view | キャラクター閲覧 |
| characters.create | キャラクター作成 |
| characters.update | キャラクター編集 |
| characters.delete | キャラクター削除 |
| submissions.view | 投稿閲覧 |
| submissions.review | 投稿レビュー |
| submissions.approve | 投稿承認 |
| users.manage | 管理者管理 |
| roles.manage | 権限管理 |
| settings.manage | システム設定管理 |

---

## 3. role_permissions

### 目的

roles と permissions を紐づける中間テーブル。

### 主な用途

- ロールごとの権限定義
- 管理画面の操作可否判定

### カラム定義

| カラム | 型 | 必須 | 説明 |
|---|---|---|---|
| id | BIGINT UNSIGNED | 必須 | 主キー |
| role_id | BIGINT UNSIGNED | 必須 | roles.id |
| permission_id | BIGINT UNSIGNED | 必須 | permissions.id |
| created_at | DATETIME | 必須 | 作成日時 |

### 制約

- `role_id` と `permission_id` の組み合わせは一意

---

## 4. admin_users

### 目的

管理画面にログインするユーザーを管理する。

一般ユーザーはログイン不要のため、このテーブルは管理者・キュレーター・レビュアー専用とする。

### 主な用途

- 管理者ログイン
- 権限判定
- 投稿レビュー
- 編集履歴記録
- キュレーター担当作品管理

### カラム定義

| カラム | 型 | 必須 | 説明 |
|---|---|---|---|
| id | BIGINT UNSIGNED | 必須 | 主キー |
| role_id | BIGINT UNSIGNED | 必須 | roles.id |
| name | VARCHAR(100) | 必須 | 表示名 |
| email | VARCHAR(255) | 必須 | メールアドレス |
| password_hash | VARCHAR(255) | 必須 | ハッシュ化済みパスワード |
| status | ENUM | 必須 | active / inactive / suspended |
| last_login_at | DATETIME | 任意 | 最終ログイン日時 |
| created_at | DATETIME | 必須 | 作成日時 |
| updated_at | DATETIME | 必須 | 更新日時 |
| deleted_at | DATETIME | 任意 | 論理削除日時 |

### セキュリティ要件

- パスワードは必ず `password_hash()` で保存する
- 平文パスワードは保存しない
- 停止中ユーザーはログイン不可
- 削除済みユーザーはログイン不可

---

## 5. works

### 目的

作品情報を管理する。

Oshi-Wikiにおける最上位のコンテンツ単位。

### 主な用途

- 作品一覧
- 作品詳細
- キャラクター紐付け
- 用語紐付け
- 世界観紐付け
- キュレーター担当割り当て

### カラム定義

| カラム | 型 | 必須 | 説明 |
|---|---|---|---|
| id | BIGINT UNSIGNED | 必須 | 主キー |
| title | VARCHAR(255) | 必須 | 作品名 |
| title_kana | VARCHAR(255) | 任意 | 作品名かな |
| slug | VARCHAR(255) | 必須 | URL用識別子 |
| genre | VARCHAR(100) | 任意 | ジャンル |
| original_media | VARCHAR(100) | 任意 | 漫画・アニメ・ゲーム等 |
| official_url | VARCHAR(500) | 任意 | 公式サイトURL |
| guideline_url | VARCHAR(500) | 任意 | 公式ガイドラインURL |
| description | TEXT | 任意 | 作品概要 |
| status | ENUM | 必須 | draft / published / private / archived / deleted |
| review_status | ENUM | 必須 | unreviewed / reviewing / approved / rejected / needs_revision |
| created_by | BIGINT UNSIGNED | 任意 | 作成者 |
| updated_by | BIGINT UNSIGNED | 任意 | 更新者 |
| published_at | DATETIME | 任意 | 公開日時 |
| created_at | DATETIME | 必須 | 作成日時 |
| updated_at | DATETIME | 必須 | 更新日時 |
| deleted_at | DATETIME | 任意 | 論理削除日時 |

### インデックス

- `slug`
- `status`
- `title`

### 公開条件

以下を満たす場合に公開可能とする。

- `status = published`
- `deleted_at IS NULL`

### 備考

作品名は検索流入の中心になるため、SEOを意識して管理する。