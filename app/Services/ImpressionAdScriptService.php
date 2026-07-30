<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

class ImpressionAdScriptService
{
    private const ALLOWED_ATTRIBUTES = [
        'src',
        'async',
        'defer',
        'crossorigin',
        'referrerpolicy',
    ];

    public function normalizeHosts(string $hostsText): array
    {
        $hosts = preg_split('/[\r\n,]+/', $hostsText) ?: [];

        $hosts = array_values(array_unique(array_filter(array_map(
            function (string $host): string {
                $host = strtolower(trim($host));
                $host = preg_replace('#^https?://#', '', $host) ?? $host;
                return rtrim(explode('/', $host, 2)[0], '.');
            },
            $hosts
        ))));

        foreach ($hosts as $host) {
            if (
                ! filter_var(
                    'https://' . $host,
                    FILTER_VALIDATE_URL
                )
                || str_contains($host, ':')
            ) {
                throw ValidationException::withMessages([
                    'allowed_script_hosts_text' =>
                        '許可スクリプトホストはURLではなく、'
                        . 'ホスト名のみを入力してください。',
                ]);
            }
        }

        return $hosts;
    }

    public function validateAndNormalize(
        string $script,
        array $allowedHosts
    ): string {
        $script = trim($script);

        if (! preg_match(
            '#^<script\b([^>]*)>\s*</script>$#is',
            $script,
            $matches
        )) {
            throw ValidationException::withMessages([
                'impression_script' =>
                    '広告コードは、外部URLを読み込むscriptタグ1個のみ'
                    . '登録できます。インラインJavaScriptは登録できません。',
            ]);
        }

        $attributes = $this->parseAttributes($matches[1]);

        $src = $attributes['src'] ?? null;

        if (! is_string($src) || $src === '') {
            throw ValidationException::withMessages([
                'impression_script' =>
                    '広告スクリプトにはsrc属性が必要です。',
            ]);
        }

        $parts = parse_url($src);

        if (
            ! is_array($parts)
            || ($parts['scheme'] ?? null) !== 'https'
            || blank($parts['host'] ?? null)
        ) {
            throw ValidationException::withMessages([
                'impression_script' =>
                    '広告スクリプトのsrcはhttps URLで入力してください。',
            ]);
        }

        $host = strtolower((string) $parts['host']);

        if (! in_array($host, $allowedHosts, true)) {
            throw ValidationException::withMessages([
                'impression_script' =>
                    '広告スクリプトのホストが許可ホストに含まれていません。',
            ]);
        }

        return $script;
    }

    private function parseAttributes(string $source): array
    {
        preg_match_all(
            '/([a-zA-Z_:][-a-zA-Z0-9_:.]*)'
            . '(?:\s*=\s*("[^"]*"|\'[^\']*\'|[^\s"\'>]+))?/',
            $source,
            $matches,
            PREG_SET_ORDER
        );

        $attributes = [];

        foreach ($matches as $match) {
            $name = strtolower($match[1]);

            if (
                ! in_array($name, self::ALLOWED_ATTRIBUTES, true)
                && ! str_starts_with($name, 'data-')
            ) {
                throw ValidationException::withMessages([
                    'impression_script' =>
                        "許可されていないscript属性です: {$name}",
                ]);
            }

            if (str_starts_with($name, 'on')) {
                throw ValidationException::withMessages([
                    'impression_script' =>
                        'イベントハンドラ属性は登録できません。',
                ]);
            }

            $value = $match[2] ?? '';

            if ($value !== '') {
                $value = trim($value, "\"'");
            }

            $attributes[$name] = $value;
        }

        return $attributes;
    }
}
