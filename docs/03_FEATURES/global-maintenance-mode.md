# 全画面メンテナンスモード

## 概要

従来のトップページ限定Coming Soon機能を廃止し、
Laravel標準のメンテナンスモードへ置き換える。

メンテナンス中は公開画面、Writer画面、管理画面を含む
Oshi-Wiki配下の画面を503メンテナンスページへ切り替える。

URL直打ちやページ更新でも通常画面には入れない。
メンテナンス開始前に保存されていない入力内容は保持されない。

## コマンド

```bash
php artisan site:maintenance on
php artisan site:maintenance off
php artisan site:maintenance status
```

本番環境ではPHPの絶対パスを使用する。

```bash
/usr/local/bin/php artisan site:maintenance on
/usr/local/bin/php artisan site:maintenance off
/usr/local/bin/php artisan site:maintenance status
```

## メンテナンスページ

`resources/views/errors/503.blade.php`

外部CSSやJavaScriptへ依存しない。
メンテナンス中でも単体で表示できるインラインCSS構成とする。

公式X:
`https://x.com/Oshi_Wiki`
