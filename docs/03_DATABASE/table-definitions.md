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

---

## 6. characters

### 目的

キャラクターの基本情報を管理する。

Oshi-Wikiにおける中心テーブルであり、口調・呼称・関係性・出典・創作メモの起点となる。

### 主な用途

- キャラクター一覧
- キャラクター詳細
- 検索
- 口調データとの紐付け
- 呼称データとの紐付け
- AI創作支援時の基礎データ

### カラム定義

| カラム | 型 | 必須 | 説明 |
|---|---|---|---|
| id | BIGINT UNSIGNED | 必須 | 主キー |
| work_id | BIGINT UNSIGNED | 必須 | works.id |
| name | VARCHAR(255) | 必須 | キャラクター名 |
| name_kana | VARCHAR(255) | 任意 | 読み仮名 |
| slug | VARCHAR(255) | 必須 | URL用識別子 |
| alias | TEXT | 任意 | 別名・通称 |
| gender | VARCHAR(100) | 任意 | 性別 |
| age | VARCHAR(100) | 任意 | 年齢 |
| birthday | VARCHAR(100) | 任意 | 誕生日 |
| height | VARCHAR(100) | 任意 | 身長 |
| weight | VARCHAR(100) | 任意 | 体重 |
| blood_type | VARCHAR(100) | 任意 | 血液型 |
| affiliation | VARCHAR(255) | 任意 | 所属 |
| role_name | VARCHAR(255) | 任意 | 役職 |
| grade_class | VARCHAR(255) | 任意 | 学年・クラス |
| first_appearance | VARCHAR(255) | 任意 | 初登場 |
| personality_summary | TEXT | 任意 | 性格概要 |
| appearance_summary | TEXT | 任意 | 外見概要 |
| background_summary | TEXT | 任意 | 背景・経歴 |
| creative_note | TEXT | 任意 | 創作時の注意点 |
| status | ENUM | 必須 | 公開状態 |
| review_status | ENUM | 必須 | 承認状態 |
| created_by | BIGINT UNSIGNED | 任意 | 作成者 |
| updated_by | BIGINT UNSIGNED | 任意 | 更新者 |
| published_at | DATETIME | 任意 | 公開日時 |
| created_at | DATETIME | 必須 | 作成日時 |
| updated_at | DATETIME | 必須 | 更新日時 |
| deleted_at | DATETIME | 任意 | 論理削除日時 |

### 制約

- 同一作品内で `slug` は一意
- `work_id` は必ず存在する作品を参照する

### 公開条件

- `status = published`
- `deleted_at IS NULL`

---

## 7. speech_profiles

### 目的

キャラクターの口調・一人称・二人称・語尾・話し方の傾向を管理する。

AI創作支援で最も重要なテーブルの一つ。

### 主な用途

- キャラクター詳細の口調表示
- AI口調チェック
- AI会話生成
- 夢小説・二次創作支援

### カラム定義

| カラム | 型 | 必須 | 説明 |
|---|---|---|---|
| id | BIGINT UNSIGNED | 必須 | 主キー |
| character_id | BIGINT UNSIGNED | 必須 | characters.id |
| first_person | VARCHAR(255) | 任意 | 一人称 |
| second_person | VARCHAR(255) | 任意 | 二人称 |
| third_person | VARCHAR(255) | 任意 | 三人称 |
| tone_summary | TEXT | 任意 | 口調概要 |
| endings | TEXT | 任意 | 語尾 |
| catchphrases | TEXT | 任意 | 口癖 |
| polite_speech | TEXT | 任意 | 敬語の傾向 |
| angry_tone | TEXT | 任意 | 怒った時の話し方 |
| shy_tone | TEXT | 任意 | 照れた時の話し方 |
| sad_tone | TEXT | 任意 | 悲しい時の話し方 |
| battle_tone | TEXT | 任意 | 戦闘時の話し方 |
| forbidden_expressions | TEXT | 任意 | 使わせない表現 |
| writing_tips | TEXT | 任意 | 創作時の書き方メモ |
| status | ENUM | 必須 | 公開状態 |
| review_status | ENUM | 必須 | 承認状態 |
| created_at | DATETIME | 必須 | 作成日時 |
| updated_at | DATETIME | 必須 | 更新日時 |
| deleted_at | DATETIME | 任意 | 論理削除日時 |

### 制約

- 1キャラクターにつき1つの口調プロフィールを基本とする
- `character_id` は一意

### AI設計上の注意

`tone_summary` に文章としてまとめつつ、`first_person`、`second_person`、`endings`、`catchphrases` などは個別項目として保持する。

---

## 8. appellations

### 目的

キャラクター同士の呼び方を管理する。

「AがBを何と呼ぶか」を構造化するためのテーブル。

### 主な用途

- 呼称一覧
- キャラクター詳細
- AI会話生成
- 口調チェック
- 関係性把握

### カラム定義

| カラム | 型 | 必須 | 説明 |
|---|---|---|---|
| id | BIGINT UNSIGNED | 必須 | 主キー |
| work_id | BIGINT UNSIGNED | 必須 | works.id |
| from_character_id | BIGINT UNSIGNED | 必須 | 呼ぶ側キャラクター |
| to_character_id | BIGINT UNSIGNED | 必須 | 呼ばれる側キャラクター |
| appellation | VARCHAR(255) | 必須 | 呼称 |
| scene | VARCHAR(255) | 任意 | 使用場面 |
| note | TEXT | 任意 | 補足 |
| status | ENUM | 必須 | 公開状態 |
| review_status | ENUM | 必須 | 承認状態 |
| created_at | DATETIME | 必須 | 作成日時 |
| updated_at | DATETIME | 必須 | 更新日時 |
| deleted_at | DATETIME | 任意 | 論理削除日時 |

### 制約

- `from_character_id` と `to_character_id` は characters.id を参照する
- 同一キャラ間でも場面により複数登録可能

---

## 9. relationships

### 目的

キャラクター同士の関係性を管理する。

### 主な用途

- 人間関係表示
- キャラクター詳細
- AI創作支援
- 会話生成時の関係性判断

### カラム定義

| カラム | 型 | 必須 | 説明 |
|---|---|---|---|
| id | BIGINT UNSIGNED | 必須 | 主キー |
| work_id | BIGINT UNSIGNED | 必須 | works.id |
| from_character_id | BIGINT UNSIGNED | 必須 | 主体キャラクター |
| to_character_id | BIGINT UNSIGNED | 必須 | 相手キャラクター |
| relationship_type | VARCHAR(100) | 任意 | 関係種別 |
| description | TEXT | 任意 | 関係性説明 |
| status | ENUM | 必須 | 公開状態 |
| review_status | ENUM | 必須 | 承認状態 |
| created_at | DATETIME | 必須 | 作成日時 |
| updated_at | DATETIME | 必須 | 更新日時 |
| deleted_at | DATETIME | 任意 | 論理削除日時 |

### relationship_type 例

- family
- friend
- rival
- teacher
- student
- ally
- enemy
- superior
- subordinate
- romantic_interest
- unknown

---

## 10. terms

### 目的

作品内の用語・固有名詞を管理する。

### 主な用途

- 用語集
- 作品詳細
- 世界観理解
- AI創作支援

### カラム定義

| カラム | 型 | 必須 | 説明 |
|---|---|---|---|
| id | BIGINT UNSIGNED | 必須 | 主キー |
| work_id | BIGINT UNSIGNED | 必須 | works.id |
| name | VARCHAR(255) | 必須 | 用語名 |
| name_kana | VARCHAR(255) | 任意 | 読み仮名 |
| term_type | VARCHAR(100) | 任意 | 用語種別 |
| description | TEXT | 任意 | 説明 |
| status | ENUM | 必須 | 公開状態 |
| review_status | ENUM | 必須 | 承認状態 |
| created_at | DATETIME | 必須 | 作成日時 |
| updated_at | DATETIME | 必須 | 更新日時 |
| deleted_at | DATETIME | 任意 | 論理削除日時 |

### term_type 例

- place
- organization
- ability
- item
- title
- event
- culture
- rule
- other

---

## 11. worlds

### 目的

作品の世界観情報を管理する。

時代・地理・社会制度・特殊能力・ルールなど、創作時に必要な背景情報を整理する。

### 主な用途

- 作品詳細の世界観表示
- 世界観ページ
- AI創作支援
- 用語・場所・組織との補完

### カラム定義

| カラム | 型 | 必須 | 説明 |
|---|---|---|---|
| id | BIGINT UNSIGNED | 必須 | 主キー |
| work_id | BIGINT UNSIGNED | 必須 | works.id |
| title | VARCHAR(255) | 必須 | 世界観項目名 |
| era | TEXT | 任意 | 時代 |
| geography | TEXT | 任意 | 地理・舞台 |
| society | TEXT | 任意 | 社会制度 |
| rules | TEXT | 任意 | 作中ルール |
| magic_or_ability | TEXT | 任意 | 魔法・能力・忍術など |
| note | TEXT | 任意 | 補足 |
| status | ENUM | 必須 | 公開状態 |
| created_at | DATETIME | 必須 | 作成日時 |
| updated_at | DATETIME | 必須 | 更新日時 |
| deleted_at | DATETIME | 任意 | 論理削除日時 |

---

## 12. organizations

### 目的

作品内の組織・学校・国・チームなどを管理する。

### 主な用途

- 作品詳細
- キャラクター所属情報
- 世界観整理
- AI創作支援

### カラム定義

| カラム | 型 | 必須 | 説明 |
|---|---|---|---|
| id | BIGINT UNSIGNED | 必須 | 主キー |
| work_id | BIGINT UNSIGNED | 必須 | works.id |
| name | VARCHAR(255) | 必須 | 組織名 |
| description | TEXT | 任意 | 説明 |
| status | ENUM | 必須 | 公開状態 |
| created_at | DATETIME | 必須 | 作成日時 |
| updated_at | DATETIME | 必須 | 更新日時 |
| deleted_at | DATETIME | 任意 | 論理削除日時 |

---

## 13. locations

### 目的

作品内の場所・地域・施設・国・都市などを管理する。

### 主な用途

- 作品詳細
- 世界観整理
- 用語集
- AI創作支援

### カラム定義

| カラム | 型 | 必須 | 説明 |
|---|---|---|---|
| id | BIGINT UNSIGNED | 必須 | 主キー |
| work_id | BIGINT UNSIGNED | 必須 | works.id |
| name | VARCHAR(255) | 必須 | 場所名 |
| description | TEXT | 任意 | 説明 |
| status | ENUM | 必須 | 公開状態 |
| created_at | DATETIME | 必須 | 作成日時 |
| updated_at | DATETIME | 必須 | 更新日時 |
| deleted_at | DATETIME | 任意 | 論理削除日時 |

---

## 14. sources

### 目的

情報の根拠となる出典を管理する。

Oshi-Wikiでは、情報の信頼性を高めるため、可能な限り各情報に出典を紐づける。

### 主な用途

- キャラクター情報の根拠
- 口調情報の根拠
- 呼称情報の根拠
- 用語情報の根拠
- 権利・引用管理

### カラム定義

| カラム | 型 | 必須 | 説明 |
|---|---|---|---|
| id | BIGINT UNSIGNED | 必須 | 主キー |
| work_id | BIGINT UNSIGNED | 任意 | works.id |
| source_type | VARCHAR(100) | 必須 | 出典種別 |
| title | VARCHAR(255) | 必須 | 出典タイトル |
| volume | VARCHAR(100) | 任意 | 巻数 |
| episode | VARCHAR(100) | 任意 | 話数 |
| chapter | VARCHAR(100) | 任意 | 章 |
| page | VARCHAR(100) | 任意 | ページ |
| url | VARCHAR(500) | 任意 | URL |
| checked_at | DATE | 任意 | 確認日 |
| note | TEXT | 任意 | 補足 |
| created_at | DATETIME | 必須 | 作成日時 |
| updated_at | DATETIME | 必須 | 更新日時 |

### source_type 例

- manga
- anime
- game
- novel
- official_site
- official_book
- official_sns
- event
- other

---

## 15. source_references

### 目的

出典と各データを紐づける。

works、characters、speech_profiles、appellations、terms など、複数種類のデータへ出典を紐づけるための汎用参照テーブル。

### 主な用途

- 情報ごとの出典表示
- 管理画面での出典確認
- 承認時の根拠確認
- AI出力時の根拠参照

### カラム定義

| カラム | 型 | 必須 | 説明 |
|---|---|---|---|
| id | BIGINT UNSIGNED | 必須 | 主キー |
| source_id | BIGINT UNSIGNED | 必須 | sources.id |
| reference_type | VARCHAR(100) | 必須 | 参照先テーブル種別 |
| reference_id | BIGINT UNSIGNED | 必須 | 参照先ID |
| note | TEXT | 任意 | 補足 |
| created_at | DATETIME | 必須 | 作成日時 |

### reference_type 例

- work
- character
- speech_profile
- appellation
- relationship
- term
- world
- organization
- location

### 設計上の注意

`reference_type` と `reference_id` によるポリモーフィック関連を採用する。

外部キー制約は `source_id` のみに設定し、参照先の整合性はアプリケーション側で保証する。

---

## 16. submissions

### 目的

一般ユーザーからの情報提供・修正依頼・削除依頼を管理する。

一般ユーザーはログイン不要で投稿できるが、投稿内容は直接公開されず、必ず承認フローを通す。

### 主な用途

- 情報提供フォーム
- 修正依頼フォーム
- 削除依頼フォーム
- 管理画面の承認待ち一覧
- レビュー・承認フロー

### カラム定義

| カラム | 型 | 必須 | 説明 |
|---|---|---|---|
| id | BIGINT UNSIGNED | 必須 | 主キー |
| target_type | VARCHAR(100) | 任意 | 対象種別 |
| target_id | BIGINT UNSIGNED | 任意 | 対象ID |
| submitter_name | VARCHAR(100) | 任意 | 投稿者名 |
| submitter_contact | VARCHAR(255) | 任意 | 連絡先 |
| category | ENUM | 必須 | canon / summary / interpretation / creative_note / delete_request / other |
| title | VARCHAR(255) | 任意 | 投稿タイトル |
| content | TEXT | 必須 | 投稿内容 |
| source_text | TEXT | 任意 | 出典・根拠 |
| status | ENUM | 必須 | pending / reviewing / approved / rejected / needs_revision / archived |
| reviewed_by | BIGINT UNSIGNED | 任意 | 最終確認者 |
| reviewed_at | DATETIME | 任意 | 最終確認日時 |
| created_at | DATETIME | 必須 | 作成日時 |
| updated_at | DATETIME | 必須 | 更新日時 |

### target_type 例

- work
- character
- speech_profile
- appellation
- relationship
- term
- source
- general

---

## 17. reviews

### 目的

submissions に対するレビュー履歴を管理する。

1つの投稿に対して複数回の確認・差戻し・承認履歴を保持できる。

### 主な用途

- 投稿承認
- 差戻し
- 却下理由管理
- レビュー履歴表示

### カラム定義

| カラム | 型 | 必須 | 説明 |
|---|---|---|---|
| id | BIGINT UNSIGNED | 必須 | 主キー |
| submission_id | BIGINT UNSIGNED | 必須 | submissions.id |
| reviewer_id | BIGINT UNSIGNED | 必須 | admin_users.id |
| result | ENUM | 必須 | approved / rejected / needs_revision / comment |
| comment | TEXT | 任意 | レビューコメント |
| created_at | DATETIME | 必須 | 作成日時 |

---

## 18. curator_assignments

### 目的

キュレーターが担当する作品を管理する。

Curatorは、担当作品に対してのみ編集・確認を行える。

### 主な用途

- 作品担当者管理
- Curator権限制御
- 担当作品一覧
- コミュニティ運営

### カラム定義

| カラム | 型 | 必須 | 説明 |
|---|---|---|---|
| id | BIGINT UNSIGNED | 必須 | 主キー |
| admin_user_id | BIGINT UNSIGNED | 必須 | admin_users.id |
| work_id | BIGINT UNSIGNED | 必須 | works.id |
| assigned_by | BIGINT UNSIGNED | 任意 | 割り当てた管理者 |
| status | ENUM | 必須 | active / inactive |
| created_at | DATETIME | 必須 | 作成日時 |

### 制約

- `admin_user_id` と `work_id` の組み合わせは一意

---

## 19. audit_logs

### 目的

管理者・キュレーター・レビュアーによる重要操作を記録する。

監査・復元・不正操作確認のために使用する。

### 主な用途

- ログイン履歴
- 編集履歴
- 承認履歴
- 削除履歴
- 権限変更履歴

### カラム定義

| カラム | 型 | 必須 | 説明 |
|---|---|---|---|
| id | BIGINT UNSIGNED | 必須 | 主キー |
| admin_user_id | BIGINT UNSIGNED | 任意 | 操作者 |
| action | VARCHAR(150) | 必須 | 操作種別 |
| target_type | VARCHAR(100) | 任意 | 操作対象種別 |
| target_id | BIGINT UNSIGNED | 任意 | 操作対象ID |
| before_data | JSON | 任意 | 変更前データ |
| after_data | JSON | 任意 | 変更後データ |
| ip_address | VARCHAR(45) | 任意 | IPアドレス |
| user_agent | TEXT | 任意 | ユーザーエージェント |
| created_at | DATETIME | 必須 | 作成日時 |

### action 例

- auth.login
- auth.logout
- work.create
- work.update
- work.delete
- character.create
- character.update
- character.delete
- submission.approve
- submission.reject
- user.create
- user.update
- role.update

---

## 20. tags

### 目的

作品・キャラクター・用語などに付与するタグを管理する。

### 主な用途

- 検索
- 絞り込み
- 関連情報表示
- SEO補助

### カラム定義

| カラム | 型 | 必須 | 説明 |
|---|---|---|---|
| id | BIGINT UNSIGNED | 必須 | 主キー |
| name | VARCHAR(100) | 必須 | タグ名 |
| slug | VARCHAR(100) | 必須 | URL・内部識別子 |
| created_at | DATETIME | 必須 | 作成日時 |
| updated_at | DATETIME | 必須 | 更新日時 |

---

## 21. taggings

### 目的

tags と任意の対象データを紐づける。

作品・キャラクター・用語など、複数種類のデータにタグ付けできるようにする。

### 主な用途

- 作品タグ
- キャラクタータグ
- 用語タグ
- 検索フィルター

### カラム定義

| カラム | 型 | 必須 | 説明 |
|---|---|---|---|
| id | BIGINT UNSIGNED | 必須 | 主キー |
| tag_id | BIGINT UNSIGNED | 必須 | tags.id |
| taggable_type | VARCHAR(100) | 必須 | 対象種別 |
| taggable_id | BIGINT UNSIGNED | 必須 | 対象ID |
| created_at | DATETIME | 必須 | 作成日時 |

### taggable_type 例

- work
- character
- term
- world
- organization
- location

### 設計上の注意

`taggable_type` と `taggable_id` によるポリモーフィック関連を採用する。

外部キー制約は `tag_id` のみに設定し、対象データの整合性はアプリケーション側で保証する。