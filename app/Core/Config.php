<?php

namespace App\Core;

class Config
{
    private static array $items = [];

    public static function load(): void
    {
        self::loadEnv();

        $configFiles = glob(__DIR__ . '/../../config/*.php');

        foreach ($configFiles as $file) {
            $key = basename($file, '.php');
            self::$items[$key] = require $file;
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = self::$items;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    private static function loadEnv(): void
    {
        $path = __DIR__ . '/../../.env';

        if (!file_exists($path)) {
            return;
        }

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

            $_ENV[$name] = $value;
        }
    }
}

function env_value(string $key, mixed $default = null): mixed
{
    return $_ENV[$key] ?? $default;
}