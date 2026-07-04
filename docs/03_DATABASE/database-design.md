# 第5章 データベース設計

## 5.1 データベース設計の目的

Oshi-Wikiのデータベースは、単なるWebサイト用の保存領域ではなく、創作支援とAI活用を前提とした構造化ナレッジベースとして設計する。

本データベースは、以下を目的とする。

- 作品情報を整理する
- キャラクター情報を整理する
- 一人称・二人称・口調・呼称を構造化する
- 出典を管理する
- 情報提供と承認フローを管理する
- キュレーター制度を支える
- 将来的なAI検索・AI口調チェック・AI創作支援に利用できる形式で保存する

---

## 5.2 設計原則

### 5.2.1 AI First

Oshi-Wikiでは、AIが利用しやすいデータ構造を優先する。

例：

- 一人称
- 二人称
- 語尾
- 口癖
- 敬語の有無
- 感情別の話し方
- 呼称
- 関係性
- 出典

これらは可能な限り分離して保存する。

---

### 5.2.2 Source First

すべての情報には、可能な限り出典を紐づける。

出典がない情報は「要確認」として扱う。

---

### 5.2.3 Approval Based

一般ユーザーからの情報提供は、直接公開しない。

必ず承認フローを通して公開する。

---

### 5.2.4 Soft Delete

削除は原則として物理削除ではなく、論理削除とする。

`deleted_at` を使用し、復元できる状態を保つ。

---

### 5.2.5 Status Management

公開状態と承認状態は分離する。

公開状態：

- draft
- published
- private
- archived
- deleted

承認状態：

- unreviewed
- reviewing
- approved
- rejected
- needs_revision

---

## 5.3 v1.0 必須エンティティ

v1.0では以下を実装対象とする。

| 区分 | テーブル |
|---|---|
| 作品 | works |
| キャラクター | characters |
| 口調 | speech_profiles |
| 呼称 | appellations |
| 関係性 | relationships |
| 用語 | terms |
| 世界観 | worlds |
| 組織 | organizations |
| 場所 | locations |
| 出典 | sources |
| 出典紐付け | source_references |
| 情報提供 | submissions |
| レビュー | reviews |
| 管理者 | admin_users |
| 権限 | roles |
| 権限詳細 | permissions |
| 権限紐付け | role_permissions |
| 担当作品 | curator_assignments |
| 操作ログ | audit_logs |
| タグ | tags |
| タグ紐付け | taggings |

---

## 5.4 v2以降の拡張候補

v2以降では以下を追加する。

- appearances
- personalities
- backstories
- abilities
- weapons
- items
- timelines
- episodes
- chapters
- scenes
- comments
- reports
- ai_embeddings
- ai_prompts
- ai_memories
- ai_conversations
- ai_search_indexes
- favorites
- view_histories

---

## 5.5 主要リレーション

### 作品とキャラクター

works 1 : N characters

1つの作品は複数のキャラクターを持つ。

---

### キャラクターと口調

characters 1 : 1 speech_profiles

1人のキャラクターは1つの基本口調データを持つ。

---

### キャラクターと呼称

characters N : N characters

appellationsを使い、キャラ同士の呼び方を管理する。

---

### キャラクターと関係性

characters N : N characters

relationshipsを使い、家族・友人・敵対・師弟などを管理する。

---

### 情報と出典

任意の情報 N : N sources

source_referencesを使い、作品・キャラ・口調・呼称・用語などに出典を紐づける。

---

### 投稿とレビュー

submissions 1 : N reviews

1つの投稿に対して、複数回の確認・差戻し・承認履歴を持てる。

---

## 5.6 ER図

```mermaid
erDiagram
    works ||--o{ characters : has
    works ||--o{ terms : has
    works ||--o{ worlds : has
    works ||--o{ organizations : has
    works ||--o{ locations : has

    characters ||--|| speech_profiles : has
    characters ||--o{ appellations : from_character
    characters ||--o{ relationships : from_character

    sources ||--o{ source_references : referenced_by

    submissions ||--o{ reviews : has

    admin_users }o--|| roles : has
    roles ||--o{ role_permissions : has
    permissions ||--o{ role_permissions : has

    admin_users ||--o{ audit_logs : creates
    admin_users ||--o{ curator_assignments : assigned
    works ||--o{ curator_assignments : assigned_to

    tags ||--o{ taggings : has