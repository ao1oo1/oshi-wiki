# 範囲別メンテナンスモード

## 対象範囲

以下の3区分を個別または組み合わせて切り替えられる。

- `public`：公開サイト全体（`/`、`/works/*`、`/characters/*`、固定ページ等）
- `writer`：`/writer` 配下とWriter用ダッシュボード
- `contributor`：コントリビューターが利用する `/admin` 配下

最高管理者でログインしている場合、`/admin` 配下は常に
メンテナンス対象外とする。

最高管理者がログインするための通常ログイン画面も利用できる。

## コマンド例

```bash
php artisan site:maintenance on public
php artisan site:maintenance on writer
php artisan site:maintenance on contributor
php artisan site:maintenance on public writer
php artisan site:maintenance on public writer contributor
php artisan site:maintenance on all

php artisan site:maintenance off writer
php artisan site:maintenance off public contributor
php artisan site:maintenance off all

php artisan site:maintenance status
```

本番環境では次のPHPを使用する。

```bash
/usr/local/bin/php artisan site:maintenance on public writer
/usr/local/bin/php artisan site:maintenance off all
/usr/local/bin/php artisan site:maintenance status
```

## 保存場所

状態は以下へJSONで保存する。

`storage/framework/oshi-wiki-maintenance.json`

Laravel標準の全面停止状態は使用しない。
これにより、最高管理者の管理画面を維持したまま
各範囲だけをメンテナンス表示へ切り替えられる。

## public範囲の除外

最高管理者やWriterがログインできるよう、以下はpublic範囲から除外する。

- `/login`などの共通認証画面
- `/writer`配下
- `/admin`配下
- `/dashboard`
- `/profile`配下
- Stripe Webhook
- ヘルスチェック `/up`
