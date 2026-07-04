<?php
/**
 * Oshi-Wiki - 設定ファイル
 */

function env_value(string $key, string $default = ''): string
{
    static $env = null;

    if ($env === null) {
        $env = [];
        $path = __DIR__ . '/../.env';

        if (file_exists($path)) {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                $line = trim($line);

                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }

                [$name, $value] = array_pad(explode('=', $line, 2), 2, '');

                $name = trim($name);
                $value = trim($value);
                $value = trim($value, "\"'");

                $env[$name] = $value;
            }
        }
    }

    return $env[$key] ?? $default;
}

return [
    'site_name' => env_value('APP_NAME', 'Oshi-Wiki'),
    'site_tagline' => '推しの情報を、創作しやすい形へ。',

    'db_driver' => env_value('DB_CONNECTION', 'mysql'),

    'sqlite_path' => __DIR__ . '/../data/oshi_wiki.sqlite',

    'mysql' => [
        'host'     => env_value('DB_HOST', '127.0.0.1'),
        'port'     => env_value('DB_PORT', '3306'),
        'dbname'   => env_value('DB_DATABASE', 'oshi_wiki'),
        'user'     => env_value('DB_USERNAME', 'root'),
        'password' => env_value('DB_PASSWORD', ''),
        'charset'  => env_value('DB_CHARSET', 'utf8mb4'),
    ],

    'initial_admin' => [
        'email'    => 'admin@example.com',
        'password' => 'oshiwiki-admin',
        'name'     => '最高管理者',
    ],

    'seed_sample_data' => true,
];
