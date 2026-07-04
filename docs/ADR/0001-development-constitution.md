# ADR 0001: Development Constitution

## Status

Accepted

## Context

Oshi-Wiki は、作品・キャラクター・関係性を長期的に管理するためのアプリケーションです。

機能追加が増えると、Controller や View に処理が散らばりやすくなります。

そのため、初期段階から開発ルールを固定します。

## Decision

以下のアーキテクチャを採用します。

Controller
→ FormRequest
→ Service
→ Repository
→ Model
→ Database

## Rules

- Controller はリクエストの受け渡しに集中する
- FormRequest はバリデーションを担当する
- Service はビジネスロジックを担当する
- Repository は DB 操作を担当する
- Model はリレーションと属性定義を担当する
- View は表示に集中する
- 1 Feature ごとに Git commit する

## Important Product Rule

Oshi-Wiki の Wiki 機能と、AI 二次創作補助機能は分離する。

Wiki 機能では、作品・キャラクターの客観的な情報を管理する。

AI 二次創作補助機能では、ヒロイン、夢主、プロット、生成補助などを扱う。
