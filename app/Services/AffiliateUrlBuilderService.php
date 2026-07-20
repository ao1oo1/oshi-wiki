<?php

namespace App\Services;

use App\Models\WorkMonetizationLink;
use Illuminate\Validation\ValidationException;

class AffiliateUrlBuilderService
{
    private const ALLOWED_PLACEHOLDERS = [
        'product_code',
        'affiliate_identifier',
        'work_id',
        'campaign_code',
        'sub_id',
    ];

    public function build(WorkMonetizationLink $link, ?string $subId = null): string
    {
        $link->loadMissing('affiliateProgram');
        $program = $link->affiliateProgram;

        if (! $program || ! $program->is_active) {
            throw ValidationException::withMessages([
                'affiliate_program' => '有効な提携プログラムが設定されていません。',
            ]);
        }

        $this->validateProductCode($link->product_code, $program->code_validation_pattern);
        $this->validateTemplate($program->url_template);

        $values = [
            'product_code' => $link->product_code,
            'affiliate_identifier' => $program->affiliate_identifier ?? '',
            'work_id' => (string) $link->work_id,
            'campaign_code' => $link->campaign_code ?? '',
            'sub_id' => $subId ?? '',
        ];

        $url = $program->url_template;

        foreach ($values as $key => $value) {
            $url = str_replace('{'.$key.'}', rawurlencode($value), $url);
        }

        $this->validateGeneratedUrl($url, $program->allowed_hosts ?? []);

        return $url;
    }

    private function validateTemplate(string $template): void
    {
        preg_match_all('/\{([a-z_]+)\}/', $template, $matches);

        $unknown = array_values(array_diff(
            array_unique($matches[1] ?? []),
            self::ALLOWED_PLACEHOLDERS
        ));

        if ($unknown !== []) {
            throw ValidationException::withMessages([
                'url_template' => '使用できない置換項目があります: '.implode(', ', $unknown),
            ]);
        }
    }

    private function validateProductCode(string $productCode, ?string $pattern): void
    {
        if (str_contains($productCode, '://') || preg_match('/[<>"\'\r\n]/', $productCode)) {
            throw ValidationException::withMessages([
                'product_code' => '商品コードの形式が正しくありません。',
            ]);
        }

        if (filled($pattern) && @preg_match($pattern, $productCode) !== 1) {
            throw ValidationException::withMessages([
                'product_code' => '商品コードがサービスの指定形式と一致しません。',
            ]);
        }
    }

    private function validateGeneratedUrl(string $url, array $allowedHosts): void
    {
        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));

        if (! in_array($scheme, ['https', 'http'], true) || $host === '') {
            throw ValidationException::withMessages([
                'url_template' => '生成されたリンク先URLが正しくありません。',
            ]);
        }

        $allowedHosts = array_map(
            static fn ($value): string => strtolower(trim((string) $value)),
            $allowedHosts
        );

        $allowed = collect($allowedHosts)->contains(
            fn (string $allowedHost): bool =>
                $host === $allowedHost || str_ends_with($host, '.'.$allowedHost)
        );

        if (! $allowed) {
            throw ValidationException::withMessages([
                'allowed_hosts' => '許可されていないリンク先ホストです。',
            ]);
        }
    }
}
