<?php

use function App\Core\env_value;

return [
    'default' => env_value('DB_CONNECTION', 'mysql'),

    'connections' => [
        'mysql' => [
            'host' => env_value('DB_HOST', '127.0.0.1'),
            'port' => env_value('DB_PORT', '3306'),
            'database' => env_value('DB_DATABASE', 'oshi_wiki'),
            'username' => env_value('DB_USERNAME', 'root'),
            'password' => env_value('DB_PASSWORD', ''),
            'charset' => env_value('DB_CHARSET', 'utf8mb4'),
        ],
    ],
];