<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * テストが通常DBへ接続する事故を防止します。
     */
    protected function setUp(): void
    {
        $environment = $this->environmentValue('APP_ENV');
        $connection = $this->environmentValue('DB_CONNECTION');
        $database = $this->environmentValue('DB_DATABASE');

        if (
            $environment !== 'testing'
            || $connection !== 'mysql'
            || $database !== 'oshi_wiki_test'
        ) {
            throw new RuntimeException(
                sprintf(
                    '危険なテスト接続を停止しました。APP_ENV=%s / DB_CONNECTION=%s / DB_DATABASE=%s',
                    $environment ?: '(未設定)',
                    $connection ?: '(未設定)',
                    $database ?: '(未設定)',
                )
            );
        }

        parent::setUp();

        $configuredConnection = (string) config('database.default');
        $configuredDatabase = (string) config(
            "database.connections.{$configuredConnection}.database"
        );

        if (
            ! app()->environment('testing')
            || $configuredConnection !== 'mysql'
            || $configuredDatabase !== 'oshi_wiki_test'
        ) {
            throw new RuntimeException(
                sprintf(
                    'LaravelのテストDB設定が安全ではありません。APP_ENV=%s / DB_CONNECTION=%s / DB_DATABASE=%s',
                    app()->environment(),
                    $configuredConnection,
                    $configuredDatabase,
                )
            );
        }
    }

    private function environmentValue(string $key): string
    {
        $value = $_ENV[$key]
            ?? $_SERVER[$key]
            ?? getenv($key)
            ?: '';

        return (string) $value;
    }
}
