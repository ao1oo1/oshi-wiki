# Oshi-Wiki Master Design

## 1. プロジェクト概要

Oshi-Wiki は、作品・キャラクター・関係性・タグを管理する Wiki 型アプリケーションです。

主目的は、二次創作時に参照しやすいよう、作品ごとの基本情報を整理することです。

## 2. 重要な設計方針

### 作品 Wiki と AI 二次創作補助は分離する

Oshi-Wiki の Wiki 機能では、作品・キャラクターの客観的な情報を扱います。

AI 二次創作補助機能では、プロット、夢主、ヒロイン、創作設定、生成補助などを扱います。

この2つは混ぜない方針です。

### キャラクター情報に入れるもの

- 名前
- 読み仮名
- 年齢
- 所属
- 学年クラス
- 一人称
- 口調
- 口調の例
- 性格
- 外見の特徴
- 背景・経歴
- タグ
- 公開状態

### キャラクター情報に入れないもの

- ヒロインへの想い
- 夢主との関係
- AI生成用の創作補助情報

これらは将来的に AI 二次創作補助側の機能として分離します。

## 3. データ構造

### works

作品情報を管理します。

主な項目：

- title
- title_kana
- slug
- genre
- original_media
- official_url
- guideline_url
- description
- status
- review_status

### characters

キャラクター情報を管理します。

主な項目：

- work_id
- name
- name_kana
- age
- affiliation
- grade_class
- first_person
- tone
- tone_examples
- personality
- appearance
- background
- status

### character_relationships

キャラクター同士の関係性を管理します。

主な項目：

- work_id
- from_character_id
- to_character_id
- called_name
- relationship
- impression
- notes
- status

### tags

タグ・分類を管理します。

主な項目：

- name
- slug
- type
- description
- status

## 4. リレーション

Work 1 - N Character
Work 1 - N CharacterRelationship
Character N - N Tag
Work N - N Tag
Character 1 - N outgoingRelationships
Character 1 - N incomingRelationships

## 5. 画面

### 管理画面

| URL | 内容 |
|---|---|
| /admin | 管理トップ |
| /admin/works | 作品管理 |
| /admin/characters | キャラクター管理 |
| /admin/character-relationships | 関係性管理 |
| /admin/tags | タグ管理 |

### 公開画面

| URL | 内容 |
|---|---|
| / | 作品一覧 |
| /works | 作品一覧 |
| /works/{work} | 作品詳細 |
| /characters/{character} | キャラクター詳細 |

## 6. 公開ルール

公開ページに表示されるのは、status が published のデータのみです。

draft / private は管理画面では見えますが、公開ページには表示しません。

## 7. 開発フロー

設計
→ Migration
→ Model
→ Repository
→ Service
→ Request
→ Controller
→ View
→ Test
→ Git

## 8. v1.0 までの完成範囲

- 作品 CRUD
- キャラクター CRUD
- 関係性 CRUD
- タグ CRUD
- 管理トップ
- 管理ナビ
- 検索
- タグ絞り込み
- 公開作品一覧
- 公開作品詳細
- 公開キャラクター詳細
- ステータス制御
- 基本テスト
- README / docs 整備
