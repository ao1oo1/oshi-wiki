# Oshi-Wiki エンティティ設計

## 1. エンティティ設計方針

Oshi-Wikiでは、創作支援とAI活用を前提として、情報をできるだけ構造化して保存する。

単なる説明文だけではなく、一人称・二人称・呼称・語尾・感情別の話し方・出典・承認状態などを個別のエンティティとして管理する。

---

## 2. エンティティ分類

### Core

- works
- characters
- worlds
- terms
- organizations
- locations

### Character Detail

- speech_profiles
- appellations
- relationships
- appearances
- personalities
- backstories
- abilities
- weapons
- items

### World Detail

- countries
- regions
- eras
- timelines
- calendars
- currencies
- world_rules

### Source

- sources
- episodes
- chapters
- scenes
- source_references

### Community / Workflow

- submissions
- reviews
- curator_assignments
- comments
- reports

### Admin / Auth

- admin_users
- roles
- permissions
- role_permissions
- audit_logs
- login_logs

### AI

- ai_embeddings
- ai_prompts
- ai_memories
- ai_conversations
- ai_search_indexes

### Utility

- tags
- taggings
- aliases
- external_links
- favorites
- view_histories

---

## 3. v1.0で実装する必須テーブル

v1.0では、以下を優先して実装する。

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
- source_references
- submissions
- reviews
- admin_users
- roles
- permissions
- role_permissions
- curator_assignments
- audit_logs
- tags
- taggings

---

## 4. v2以降で実装するテーブル

- appearances
- personalities
- backstories
- abilities
- weapons
- items
- countries
- regions
- eras
- timelines
- calendars
- currencies
- world_rules
- episodes
- chapters
- scenes
- comments
- reports
- login_logs
- ai_embeddings
- ai_prompts
- ai_memories
- ai_conversations
- ai_search_indexes
- aliases
- external_links
- favorites
- view_histories

---

## 5. 主要リレーション

### 作品とキャラクター

works 1 : N characters

1つの作品は複数のキャラクターを持つ。

### キャラクターと口調

characters 1 : 1 speech_profiles

1人のキャラクターは基本口調データを1つ持つ。

### キャラクターと呼称

characters N : N characters

appellationsを中間テーブルとして、呼ぶ側・呼ばれる側を管理する。

### キャラクターと関係性

characters N : N characters

relationshipsを使い、家族・友人・敵対・師弟などを管理する。

### 作品と用語

works 1 : N terms

1つの作品は複数の用語を持つ。

### 情報と出典

各種情報 N : N sources

source_references を使って、キャラクター・口調・呼称・用語などに出典を紐づける。

### 投稿と承認

submissions 1 : N reviews

1つの情報提供に対し、複数回の確認・差戻し・承認履歴を持てる。

### 管理者と権限

admin_users N : 1 roles

roles N : N permissions

---

## 6. ER図 Mermaid版

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