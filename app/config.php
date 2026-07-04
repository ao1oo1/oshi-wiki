<?php
/**
 * OshiBase - 設定ファイル
 *
 * ローカル開発: このままでOK（SQLiteを使用）
 * さくらのレンタルサーバ: DB_DRIVER を 'mysql' に変更し、
 * さくらのコントロールパネルで作成したMySQLの接続情報を入力する。
 */

return [
    // サイト名
    'site_name' => 'OshiBase',
    'site_tagline' => '推しの情報を、創作しやすい形へ。',

    // 'sqlite' または 'mysql'
    'db_driver' => 'sqlite',

    // SQLite設定（ローカル / さくらでもSQLite利用可）
    'sqlite_path' => __DIR__ . '/../data/oshibase.sqlite',

    // MySQL設定（さくらのレンタルサーバ用）
    'mysql' => [
        'host'     => 'mysqlXXX.db.sakura.ne.jp',
        'dbname'   => 'アカウント名_oshibase',
        'user'     => 'アカウント名',
        'password' => 'ここにパスワード',
        'charset'  => 'utf8mb4',
    ],

    // 初回セットアップ時に作成される最高管理者
    // ログイン後、必ずパスワードを変更してください
    'initial_admin' => [
        'email'    => 'admin@example.com',
        'password' => 'oshibase-admin',
        'name'     => '最高管理者',
    ],

    // デモ用サンプルデータを初回に投入するか
    'seed_sample_data' => true,
];
