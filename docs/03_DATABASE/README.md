# Oshi-Wiki データベース設計書

## 1. データベース設計方針

Oshi-Wikiは、単なるWikiではなく、創作支援とAI活用を前提とした構造化キャラクターデータベースである。

すべてのデータは、人間が読みやすいだけでなく、将来的にAIが参照・検索・文章生成に利用しやすい形で保存する。

## 2. 基本原則

### 2.1 AI First

一人称、二人称、呼称、語尾、口癖、敬語、感情別の話し方などは、可能な限り項目を分けて保存する。

### 2.2 Source First

すべての情報には、可能な限り出典を紐づける。

### 2.3 Human + AI Friendly

人間が読むための説明文と、AIが扱うための構造化データを分けて管理する。

### 2.4 Approval Based

一般ユーザーの投稿は直接公開せず、承認後に反映する。

### 2.5 Extensible

将来的なAI検索、口調チェック、会話生成、創作支援機能を前提に拡張可能な設計にする。

## 3. 主要エンティティ

- works
- characters
- speech_profiles
- appellations
- relationships
- terms
- worlds
- organizations
- locations
- sources
- submissions
- reviews
- admin_users
- roles
- permissions
- audit_logs
- ai_embeddings
- ai_prompts
- ai_memories

## 4. 次に設計する内容

次章以降で以下を定義する。

- エンティティ一覧
- ER図
- テーブル定義
- カラム定義
- インデックス
- 外部キー
- 初期データ
- Migration方針
