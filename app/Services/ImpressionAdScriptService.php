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
                $host = preg_replace(
                    '#^https?://#',
                    '',
                    $host
                ) ?? $host;

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
                        '許可広告ホストはURLではなく、'
                        . 'ホスト名のみを入力してください。',
                ]);
            }
        }

        return $hosts;
    }

    public function validateAndNormalize(
        string $script,
        array $allowedHosts,
        string $format = 'script'
    ): string {
        $script = trim($script);

        return match ($format) {
            'script' => $this->validateScriptFormat(
                $script,
                $allowedHosts
            ),
            'text' => $this->validateTextFormat(
                $script,
                $allowedHosts
            ),
            'image' => $this->validateImageFormat(
                $script,
                $allowedHosts
            ),
            default => throw ValidationException::withMessages([
                'impression_ad_format' =>
                    '広告形式の値が正しくありません。',
            ]),
        };
    }

    private function validateScriptFormat(
        string $script,
        array $allowedHosts
    ): string {
        if (! preg_match(
            '#^<script\b([^>]*)>\s*</script>$#is',
            $script,
            $matches
        )) {
            throw ValidationException::withMessages([
                'impression_script' =>
                    'スクリプト広告には外部scriptタグ1個を'
                    . '登録してください。',
            ]);
        }

        return $this->validateExternalScript(
            $script,
            $matches[1],
            $allowedHosts
        );
    }

    private function validateTextFormat(
        string $script,
        array $allowedHosts
    ): string {
        if (! preg_match(
            '#^<a\b([^>]*)>(.*?)</a>\s*'
            . '<img\b([^>]*)/?>$#is',
            $script,
            $matches
        )) {
            throw ValidationException::withMessages([
                'impression_script' =>
                    'テキスト広告には、テキストリンクと'
                    . '1×1計測画像の組み合わせを登録してください。',
            ]);
        }

        if (
            trim($matches[2]) === ''
            || strip_tags($matches[2]) !== $matches[2]
        ) {
            throw ValidationException::withMessages([
                'impression_script' =>
                    'テキスト広告のリンク内には'
                    . 'プレーンテキストだけを入力してください。',
            ]);
        }

        $this->validateLink(
            $matches[1],
            $allowedHosts
        );
        $this->validateTrackingPixel(
            $matches[3],
            $allowedHosts
        );

        return $script;
    }

    private function validateImageFormat(
        string $script,
        array $allowedHosts
    ): string {
        if (! preg_match(
            '#^<a\b([^>]*)>\s*'
            . '<img\b([^>]*)/?>\s*</a>\s*'
            . '<img\b([^>]*)/?>$#is',
            $script,
            $matches
        )) {
            throw ValidationException::withMessages([
                'impression_script' =>
                    '画像広告には、リンク内のバナー画像と'
                    . 'リンク外の1×1計測画像を登録してください。',
            ]);
        }

        $this->validateLink(
            $matches[1],
            $allowedHosts
        );
        $this->validateBannerImage(
            $matches[2],
            $allowedHosts
        );
        $this->validateTrackingPixel(
            $matches[3],
            $allowedHosts
        );

        return $script;
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
                'impression_script' =>
                    '広告スクリプトにはsrc属性が必要です。',
            ]);
        }

        $this->validateHttpsUrl(
            $src,
            $allowedHosts,
            '広告スクリプトのsrc'
        );

        return $script;
    }

    private function validateLink(
        string $attributeSource,
        array $allowedHosts
    ): void {
        $attributes = $this->parseAttributes(
            $attributeSource,
            self::LINK_ATTRIBUTES,
            'a'
        );

        $href = $attributes['href'] ?? '';

        if ($href === '') {
            throw ValidationException::withMessages([
                'impression_script' =>
                    '広告リンクにはhref属性が必要です。',
            ]);
        }

        $this->validateHttpsUrl(
            $href,
            $allowedHosts,
            '広告リンクのhref'
        );

        $rel = preg_split(
            '/\s+/',
            strtolower(trim((string) (
                $attributes['rel'] ?? ''
            )))
        ) ?: [];

        if (! in_array('nofollow', $rel, true)) {
            throw ValidationException::withMessages([
                'impression_script' =>
                    '広告リンクのrel属性にはnofollowが必要です。',
            ]);
        }

        if (
            isset($attributes['target'])
            && ! in_array(
                $attributes['target'],
                ['_blank', '_self'],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'impression_script' =>
                    '広告リンクのtarget属性は_blankまたは_self'
                    . 'だけ使用できます。',
            ]);
        }
    }

    private function validateBannerImage(
        string $attributeSource,
        array $allowedHosts
    ): void {
        $attributes = $this->parseAttributes(
            $attributeSource,
            self::IMAGE_ATTRIBUTES,
            'img'
        );

        $src = $attributes['src'] ?? '';

        if ($src === '') {
            throw ValidationException::withMessages([
                'impression_script' =>
                    'バナー画像にはsrc属性が必要です。',
            ]);
        }

        $this->validateHttpsUrl(
            $src,
            $allowedHosts,
            'バナー画像のsrc'
        );

        $width = filter_var(
            $attributes['width'] ?? null,
            FILTER_VALIDATE_INT
        );
        $height = filter_var(
            $attributes['height'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (
            $width === false
            || $height === false
            || $width < 2
            || $height < 2
            || $width > 2000
            || $height > 2000
        ) {
            throw ValidationException::withMessages([
                'impression_script' =>
                    'バナー画像のwidthとheightは2〜2000の'
                    . '整数で指定してください。',
            ]);
        }

        $this->validateBorder($attributes, 'バナー画像');
    }

    private function validateTrackingPixel(
        string $attributeSource,
        array $allowedHosts
    ): void {
        $attributes = $this->parseAttributes(
            $attributeSource,
            self::IMAGE_ATTRIBUTES,
            'img'
        );

        $src = $attributes['src'] ?? '';

        if ($src === '') {
            throw ValidationException::withMessages([
                'impression_script' =>
                    '計測画像にはsrc属性が必要です。',
            ]);
        }

        $this->validateHttpsUrl(
            $src,
            $allowedHosts,
            '計測画像のsrc'
        );

        if (
            (string) ($attributes['width'] ?? '') !== '1'
            || (string) ($attributes['height'] ?? '') !== '1'
        ) {
            throw ValidationException::withMessages([
                'impression_script' =>
                    '計測画像はwidth="1" height="1"で'
                    . '登録してください。',
            ]);
        }

        $this->validateBorder($attributes, '計測画像');
    }

    private function validateBorder(
        array $attributes,
        string $label
    ): void {
        if (
            isset($attributes['border'])
            && (string) $attributes['border'] !== '0'
        ) {
            throw ValidationException::withMessages([
                'impression_script' =>
                    "{$label}のborderは0だけ使用できます。",
            ]);
        }
    }

    private function validateHttpsUrl(
        string $url,
        array $allowedHosts,
        string $label
    ): void {
        $parts = parse_url($url);

        if (
            ! is_array($parts)
            || strtolower(
                (string) ($parts['scheme'] ?? '')
            ) !== 'https'
            || blank($parts['host'] ?? null)
        ) {
            throw ValidationException::withMessages([
                'impression_script' =>
                    "{$label}はhttps URLで入力してください。",
            ]);
        }

        $host = strtolower((string) $parts['host']);

        $allowed = collect($allowedHosts)->contains(
            fn (string $allowedHost): bool =>
                $host === $allowedHost
                || str_ends_with(
                    $host,
                    '.' . $allowedHost
                )
        );

        if (! $allowed) {
            throw ValidationException::withMessages([
                'impression_script' =>
                    "{$label}のホスト「{$host}」が"
                    . '許可広告ホストに含まれていません。',
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
            . '(?:\s*=\s*("[^"]*"|\'[^\']*\'|'
            . '[^\s"\'>]+))?/',
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
                    ! in_array(
                        $name,
                        $allowedAttributes,
                        true
                    )
                    && ! $isAllowedDataAttribute
                )
            ) {
                throw ValidationException::withMessages([
                    'impression_script' =>
                        "{$tagName}タグで許可されていない"
                        . "属性です: {$name}",
                ]);
            }

            if (array_key_exists($name, $attributes)) {
                throw ValidationException::withMessages([
                    'impression_script' =>
                        "{$tagName}タグの{$name}属性が"
                        . '重複しています。',
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
