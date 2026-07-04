# ADR-0001 Oshi-Wiki v1.0 開発憲章

## 決定事項

Oshi-Wiki v1.0では以下の構成を固定する。

- Framework: Laravel 12
- PHP: 8.5以上
- Database: MySQL
- ORM: Eloquent
- View: Blade
- CSS: Tailwind CSS
- Auth: Laravel Starter Kit / Blade
- Permission: 独自 Role / Permission
- Architecture: Controller → FormRequest → Service → Repository → Model
- Version Control: Git + GitHub
- Editor: Cursor

## 採用しないもの

v1.0では以下を採用しない。

- 独自PHPフレームワーク
- Livewire
- React
- Vue
- Inertia
- 完全DDDディレクトリ構成

## 理由

v1.0では、さくらレンタルサーバーで安定運用できること、開発速度、保守性を優先する。

## 今後のルール

v1.0完成までは、技術スタック・アーキテクチャを変更しない。
変更が必要な場合は、必ずADRとして記録する。
