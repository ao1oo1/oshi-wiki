<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

class ImpressionAdScriptService
{
    private const SCRIPT_ATTRIBUTES = [
        'src', 'async', 'defer', 'crossorigin', 'referrerpolicy',
    ];

    private const LINK_ATTRIBUTES = [
        'href', 'rel', 'target', 'title', 'class',
    ];

    private const IMAGE_ATTRIBUTES = [
        'src', 'border', 'width', 'height', 'alt',
        'loading', 'referrerpolicy',
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
                ! filter_var('https://' . $host, FILTER_VALIDATE_URL)
                || str_contains($host, ':')
            ) {
                throw ValidationException::withMessages([
                    'allowed_script_hosts_text' =>
                        '許可広告ホストはURLではなく、ホスト名のみを入力してください。',
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

        if (preg_match(
            '#^<script\b([^>]*)>\s*</script>$#is',
            $script,
            $matches
        )) {
            return $this->validateExternalScript(
                $script,
                $matches[1],
                $allowedHosts
            );
        }

        if (preg_match(
            '#^<a\b([^>]*)>(.*?)</a>\s*<img\b([^>]*)/?>$#is',
            $script,
            $matches
        )) {
            return $this->validateLinkWithTrackingPixel(
                $script,
                $matches[1],
                $matches[2],
                $matches[3],
                $allowedHosts
            );
        }

        throw ValidationException::withMessages([
            'impression_script' =>
                '広告コードは、外部scriptタグ1個、またはテキストリンクと1×1計測画像の組み合わせだけ登録できます。',
        ]);
    }

    private function validateExternalScript(
        string $script,
        string $attributeSource,
        array $allowedHosts
    ): string {
        $attributes = $this->parseAttributes(
            $attributeSource,
            self::SCRIPT_ATTRIBUTES,
            'script',
            true
        );

        $src = $attributes['src'] ?? '';

        if ($src === '') {
            throw ValidationException::withMessages([
                'impression_script' => '広告スクリプトにはsrc属性が必要です。',
            ]);
        }

        $this->validateHttpsUrl($src, $allowedHosts, '広告スクリプトのsrc');

        return $script;
    }

    private function validateLinkWithTrackingPixel(
        string $script,
        string $linkAttributeSource,
        string $linkText,
        string $imageAttributeSource,
        array $allowedHosts
    ): string {
        if (trim($linkText) === '' || strip_tags($linkText) !== $linkText) {
            throw ValidationException::withMessages([
                'impression_script' =>
                    '広告リンク内にはプレーンテキストだけを入力してください。',
            ]);
        }

        $linkAttributes = $this->parseAttributes(
            $linkAttributeSource,
            self::LINK_ATTRIBUTES,
            'a'
        );

        $href = $linkAttributes['href'] ?? '';

        if ($href === '') {
            throw ValidationException::withMessages([
                'impression_script' => '広告リンクにはhref属性が必要です。',
            ]);
        }

        $this->validateHttpsUrl($href, $allowedHosts, '広告リンクのhref');

        $rel = preg_split(
            '/\s+/',
            strtolower(trim((string) ($linkAttributes['rel'] ?? '')))
        ) ?: [];

        if (! in_array('nofollow', $rel, true)) {
            throw ValidationException::withMessages([
                'impression_script' =>
                    '広告リンクのrel属性にはnofollowが必要です。',
            ]);
        }

        if (
            isset($linkAttributes['target'])
            && ! in_array($linkAttributes['target'], ['_blank', '_self'], true)
        ) {
            throw ValidationException::withMessages([
                'impression_script' =>
                    '広告リンクのtarget属性は_blankまたは_selfだけ使用できます。',
            ]);
        }

        $imageAttributes = $this->parseAttributes(
            $imageAttributeSource,
            self::IMAGE_ATTRIBUTES,
            'img'
        );

        $src = $imageAttributes['src'] ?? '';

        if ($src === '') {
            throw ValidationException::withMessages([
                'impression_script' => '計測画像にはsrc属性が必要です。',
            ]);
        }

        $this->validateHttpsUrl($src, $allowedHosts, '計測画像のsrc');

        if (
            (string) ($imageAttributes['width'] ?? '') !== '1'
            || (string) ($imageAttributes['height'] ?? '') !== '1'
        ) {
            throw ValidationException::withMessages([
                'impression_script' =>
                    '計測画像はwidth="1" height="1"で登録してください。',
            ]);
        }

        if (
            isset($imageAttributes['border'])
            && (string) $imageAttributes['border'] !== '0'
        ) {
            throw ValidationException::withMessages([
                'impression_script' => '計測画像のborderは0だけ使用できます。',
            ]);
        }

        return $script;
    }

    private function validateHttpsUrl(
        string $url,
        array $allowedHosts,
        string $label
    ): void {
        $parts = parse_url($url);

        if (
            ! is_array($parts)
            || strtolower((string) ($parts['scheme'] ?? '')) !== 'https'
            || blank($parts['host'] ?? null)
        ) {
            throw ValidationException::withMessages([
                'impression_script' => "{$label}はhttps URLで入力してください。",
            ]);
        }

        $host = strtolower((string) $parts['host']);

        $allowed = collect($allowedHosts)->contains(
            fn (string $allowedHost): bool =>
                $host === $allowedHost
                || str_ends_with($host, '.' . $allowedHost)
        );

        if (! $allowed) {
            throw ValidationException::withMessages([
                'impression_script' =>
                    "{$label}のホスト「{$host}」が許可広告ホストに含まれていません。",
            ]);
        }
    }

    private function parseAttributes(
        string $source,
        array $allowedAttributes,
        string $tagName,
        bool $allowDataAttributes = false
    ): array {
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

            $isAllowedDataAttribute =
                $allowDataAttributes
                && str_starts_with($name, 'data-');

            if (
                str_starts_with($name, 'on')
                || (
                    ! in_array($name, $allowedAttributes, true)
                    && ! $isAllowedDataAttribute
                )
            ) {
                throw ValidationException::withMessages([
                    'impression_script' =>
                        "{$tagName}タグで許可されていない属性です: {$name}",
                ]);
            }

            if (array_key_exists($name, $attributes)) {
                throw ValidationException::withMessages([
                    'impression_script' =>
                        "{$tagName}タグの{$name}属性が重複しています。",
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
