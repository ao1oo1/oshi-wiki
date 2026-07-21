<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use InvalidArgumentException;

class ScopedMaintenanceService
{
    public const SCOPES = [
        'public' => '公開トップページ',
        'writer' => 'Writerページ',
        'contributor' => 'コントリビューター画面',
    ];

    public function activeScopes(): array
    {
        $path = $this->path();

        if (! File::exists($path)) {
            return [];
        }

        $decoded = json_decode(
            (string) File::get($path),
            true
        );

        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_intersect(
            array_keys(self::SCOPES),
            array_values($decoded['scopes'] ?? [])
        ));
    }

    public function enable(array $scopes): array
    {
        $scopes = $this->normalize($scopes);
        $active = array_values(array_unique(array_merge(
            $this->activeScopes(),
            $scopes
        )));

        $this->write($active);

        return $active;
    }

    public function disable(array $scopes): array
    {
        $scopes = $this->normalize($scopes);

        if ($scopes === array_keys(self::SCOPES)) {
            $this->clear();

            return [];
        }

        $active = array_values(array_diff(
            $this->activeScopes(),
            $scopes
        ));

        $this->write($active);

        return $active;
    }

    public function isActive(string $scope): bool
    {
        return in_array(
            $scope,
            $this->activeScopes(),
            true
        );
    }

    public function labels(array $scopes): array
    {
        return array_map(
            fn (string $scope): string =>
                self::SCOPES[$scope],
            $scopes
        );
    }

    private function normalize(array $scopes): array
    {
        $scopes = array_values(array_filter(array_map(
            fn ($scope): string =>
                strtolower(trim((string) $scope)),
            $scopes
        )));

        if ($scopes === [] || in_array('all', $scopes, true)) {
            return array_keys(self::SCOPES);
        }

        $invalid = array_diff(
            $scopes,
            array_keys(self::SCOPES)
        );

        if ($invalid !== []) {
            throw new InvalidArgumentException(
                '不明な範囲です: '.implode(', ', $invalid)
            );
        }

        return array_values(array_unique($scopes));
    }

    private function write(array $scopes): void
    {
        $path = $this->path();

        File::ensureDirectoryExists(dirname($path));

        if ($scopes === []) {
            $this->clear();

            return;
        }

        File::put(
            $path,
            json_encode(
                [
                    'scopes' => array_values($scopes),
                    'updated_at' => now()->toIso8601String(),
                ],
                JSON_PRETTY_PRINT
                | JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            )
        );
    }

    private function clear(): void
    {
        File::delete($this->path());
    }

    private function path(): string
    {
        return storage_path(
            'framework/oshi-wiki-maintenance.json'
        );
    }
}
