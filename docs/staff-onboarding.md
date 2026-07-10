# Oshi-Wiki スタッフ登用仕様

## 目的

Oshi-Wiki の情報入力スタッフを、最高管理者が申請内容を確認したうえで登用開始できるようにする。

スタッフは仮パスワードで初回ログインし、自分で正式なパスワードへ変更した後、情報入力を開始する。

## ユーザー区分

### 最高管理者

- users.is_super_admin = 1
- role_id は不要
- 全管理機能を利用できる

### 管理スタッフ

- users.is_super_admin = 0
- roles.name = staff
- 管理画面にアクセスできる
- 許可された情報入力機能のみ利用できる

### 一般執筆ユーザー

- users.is_super_admin = 0
- roles.name = writer
- 執筆補助ツールを利用できる

## メールアドレス重複仕様

メールアドレスは、最高管理者・管理スタッフ・一般執筆ユーザーのすべてで重複不可とする。

ただし、削除フラグが付いているデータは重複チェック対象外とする。

### 申請時に登録不可

- users.email に同じメールアドレスが存在し、deleted_at が NULL
- contributor_applications.email に同じメールアドレスの申請中データが存在し、deleted_at が NULL
- contributor_applications.email に同じメールアドレスの登用中データが存在し、deleted_at が NULL

### 申請時に登録可能

- contributor_applications で削除フラグが付いた申請メールアドレス
- users で削除フラグが付いたユーザーメールアドレス
- 見送り済み rejected の申請メールアドレス

## スタッフ登用フロー

1. スタッフ希望者が /contributor/apply から申請する
2. 申請時にメールアドレス重複チェックを行う
3. 最高管理者が /admin/contributor-applications で申請を確認する
4. 最高管理者が「登用開始」を押す
5. システムがランダムな仮パスワードを発行する
6. users に管理スタッフユーザーを作成または更新する
7. 申請データを active にし、started_at を保存する
8. 申請者メールアドレス宛に登用開始メールを送信する
9. メールには、管理スタッフ用ログインURL、メールアドレス、仮パスワード、初回パスワード変更案内を記載する
10. スタッフは /admin/login から仮パスワードでログインする
11. must_change_password = true の場合、/profile に誘導する
12. スタッフ本人が新しいパスワードを設定する
13. パスワード変更後、must_change_password = false にする
14. 初回パスワード変更後は一度ログアウトさせ、再ログインを促す
15. 再ログイン後、管理スタッフとして情報入力を開始する

## 登用開始時の users 保存内容

- name = 申請ユーザーネーム
- public_username = 申請ユーザーネーム
- email = 申請メールアドレス
- role_id = staff ロールID
- status = active
- is_super_admin = false
- contributor_application_id = 申請ID
- must_change_password = true
- email_verified_at = now()
- password = 仮パスワードのハッシュ
- staff_public_id = STAFF-000001 形式

## 関連画面

### 公開側スタッフ申請フォーム

/contributor/apply

### 管理側スタッフ申請一覧

/admin/contributor-applications

### 管理スタッフ用ログイン

/admin/login

### パスワード変更画面

/profile

## 注意事項

- 仮パスワードはメールで送信されるため、初回ログイン後は必ず変更させる
- canAccessAdmin() は管理画面に入れるかどうかの判定にのみ使う
- 最高管理者専用操作には isSuperAdmin() を使う
- role 文字列カラムは使わない
- 権限は is_super_admin と roles.name で判定する
