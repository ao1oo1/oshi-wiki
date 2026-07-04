# EPIC-001 Oshi-Wiki Framework構築

## 目的

Oshi-Wikiを長期的に拡張できるよう、独自の軽量PHPフレームワーク構成を整備する。

## 対象

- Router
- Request
- Response
- Controller
- View
- Config
- Database
- Session
- Auth
- Middleware

## 完了条件

- public/index.php を入口にできる
- routes/web.php でURLを管理できる
- Controllerで処理を分離できる
- Viewで画面表示できる
- DB接続を共通化できる
- .env設定を読み込める
- 管理画面と公開画面を分離できる

## Issues

### ISSUE-001 ディレクトリ構成整理

目的：
正式版のフォルダ構成を作成する。

作成するフォルダ：

- app/Core
- app/Controllers/Public
- app/Controllers/Admin
- app/Models
- app/Repositories
- app/Services
- app/Middleware
- app/Policies
- app/Validators
- app/DTO
- app/Exceptions
- app/Helpers
- app/View
- bootstrap
- config
- routes
- resources/views
- storage/logs
- storage/cache
- tests

完了条件：
上記フォルダが作成され、Gitに保存されている。

---

### ISSUE-002 Router実装

目的：
URLとControllerを紐づけるRouterを実装する。

---

### ISSUE-003 Request / Response実装

目的：
HTTP入力と出力を共通化する。

---

### ISSUE-004 Controller基底クラス実装

目的：
全Controllerで共通利用する処理を整理する。

---

### ISSUE-005 View描画機能実装

目的：
画面テンプレートを共通処理で表示できるようにする。

---

### ISSUE-006 Database接続共通化

目的：
PDO接続を共通化し、Repositoryから利用できるようにする。