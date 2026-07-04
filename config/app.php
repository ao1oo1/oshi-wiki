<?php

use function App\Core\env_value;

return [
    'name' => env_value('APP_NAME', 'Oshi-Wiki'),
    'env' => env_value('APP_ENV', 'local'),
    'url' => env_value('APP_URL', 'http://localhost:8080'),
];