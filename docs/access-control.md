# Oshi-Wiki 権限仕様

## 基本方針

権限判定は以下に統一する。

最高管理者：users.is_super_admin = 1
管理スタッフ：users.is_super_admin = 0 かつ roles.name = staff
一般ユーザー：users.is_super_admin = 0 かつ roles.name = writer

## 使うメソッド

最高管理者判定：auth()->user()?->isSuperAdmin()
管理画面に入れるか：auth()->user()?->canAccessAdmin()
writer画面に入れるか：auth()->user()?->canAccessWriter()

## 禁止・非推奨

auth()->user()?->is_super_admin の直接参照は避ける。
$user->role === "super_admin" のような文字列role判定は使わない。
$user->role === "staff" のような文字列role判定は使わない。
$user->role === "writer" のような文字列role判定は使わない。

## 注意

role という文字列カラムは使わない。
最高管理者は role_id ではなく is_super_admin で判定する。
スタッフと一般ユーザーは role_id 経由で roles.name を見る。
