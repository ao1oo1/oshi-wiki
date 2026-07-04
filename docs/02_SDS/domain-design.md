# 第4.5章 ドメイン設計

## 4.5.1 ドメイン設計の目的

Oshi-Wikiは、単なるキャラクターWikiではなく、創作支援とAI活用を前提とした構造化データベースである。

そのため、テーブルや画面を先に考えるのではなく、サービスが扱う業務領域をドメインとして整理する。

---

## 4.5.2 Core Domain

Oshi-Wikiの中核となる領域。

### 作品ドメイン

対象：

- Work
- World
- Term
- Organization
- Location

責務：

- 作品情報を管理する
- 世界観を整理する
- 用語・組織・場所を紐づける

---

### キャラクタードメイン

対象：

- Character
- SpeechProfile
- Appellation
- Relationship

責務：

- キャラクター基本情報を管理する
- 一人称・二人称・口調を管理する
- キャラ同士の呼称を管理する
- 人間関係を管理する

---

### 創作支援ドメイン

対象：

- CreativeNote
- WritingTip
- SpeechPattern
- AIContext

責務：

- 創作時に役立つ情報を整理する
- AIが参照しやすい構造化情報を提供する
- 口調チェック・会話生成・創作補助の基盤となる

---

## 4.5.3 Supporting Domain

中核機能を支える領域。

### 出典ドメイン

対象：

- Source
- SourceReference

責務：

- 情報の根拠を管理する
- 公式情報・要約・考察を区別する
- 出典と各データを紐づける

---

### 投稿・承認ドメイン

対象：

- Submission
- Review

責務：

- 一般ユーザーからの情報提供を受け付ける
- レビュアー・キュレーター・管理者による確認を管理する
- 承認後に公開データへ反映する

---

### キュレータードメイン

対象：

- CuratorAssignment

責務：

- 作品ごとの担当者を管理する
- 編集可能範囲を制御する
- コミュニティ運営を支える

---

## 4.5.4 Generic Domain

汎用的なシステム領域。

### 認証・権限ドメイン

対象：

- AdminUser
- Role
- Permission
- RolePermission

責務：

- 管理者ログイン
- 権限判定
- 管理画面へのアクセス制御

---

### ログドメイン

対象：

- AuditLog

責務：

- 編集履歴を記録する
- 承認・削除・権限変更などの重要操作を追跡する
- 問題発生時に復元・確認できるようにする

---

### タグドメイン

対象：

- Tag
- Tagging

責務：

- 作品・キャラクター・用語などにタグを付与する
- 検索性を高める

---

## 4.5.5 Entity

Oshi-WikiでEntityとして扱うもの。

- Work
- Character
- SpeechProfile
- Appellation
- Relationship
- Term
- World
- Organization
- Location
- Source
- SourceReference
- Submission
- Review
- AdminUser
- Role
- Permission
- CuratorAssignment
- AuditLog
- Tag

---

## 4.5.6 Value Object

値として扱うもの。

- Slug
- Email
- Url
- Status
- ReviewStatus
- SourceType
- SubmissionCategory
- PermissionKey
- CharacterName
- AppellationText

Value Objectは、同じ値であれば同じものとして扱う。

---

## 4.5.7 Aggregate

### Work Aggregate

Root：

- Work

含まれるもの：

- Character
- Term
- World
- Organization
- Location

---

### Character Aggregate

Root：

- Character

含まれるもの：

- SpeechProfile
- Appellation
- Relationship

---

### Submission Aggregate

Root：

- Submission

含まれるもの：

- Review

---

### Auth Aggregate

Root：

- AdminUser

含まれるもの：

- Role
- Permission

---

## 4.5.8 Repository

RepositoryはDBアクセスを担当する。

- WorkRepository
- CharacterRepository
- SpeechProfileRepository
- AppellationRepository
- SourceRepository
- SubmissionRepository
- AdminUserRepository
- AuditLogRepository

Controllerから直接SQLを実行してはいけない。

---

## 4.5.9 Domain Service

複数Entityをまたぐ処理はServiceに分離する。

- SearchService
- SubmissionReviewService
- CharacterProfileService
- SourceReferenceService
- PermissionService
- AuditLogService
- AiContextBuilderService

---

## 4.5.10 ドメイン設計原則

- WorkとCharacterを中心に設計する
- 口調・呼称・関係性はAI利用を前提に構造化する
- 公式情報・要約・考察・創作用メモを分離する
- 出典は独立Entityとして扱う
- 投稿と公開データは直接混ぜない
- 権限はRoleとPermissionで分離する
- ログは後から復元・監査できる形で保存する