<?php

namespace App\Services;

use RuntimeException;

class StripeWebhookSignatureService
{
    public function verify(
        string $payload,
        string $signatureHeader
    ): void {
        $secret = (string) config('services.stripe.webhook_secret');

        if ($secret === '') {
            throw new RuntimeException(
                'Stripe Webhook署名シークレットが未設定です。'
            );
        }

        $parts = [];

        foreach (explode(',', $signatureHeader) as $item) {
            [$key, $value] = array_pad(
                explode('=', trim($item), 2),
                2,
                null
            );

            if ($key !== null && $value !== null) {
                $parts[$key][] = $value;
            }
        }

        $timestamp = (int) ($parts['t'][0] ?? 0);
        $signatures = $parts['v1'] ?? [];

        if (
            $timestamp <= 0
            || abs(time() - $timestamp) > 300
            || $signatures === []
        ) {
            throw new RuntimeException(
                'Stripe Webhook署名が無効または期限切れです。'
            );
        }

        $expected = hash_hmac(
            'sha256',
            $timestamp . '.' . $payload,
            $secret
        );

        foreach ($signatures as $signature) {
            if (hash_equals($expected, $signature)) {
                return;
            }
        }

        throw new RuntimeException(
            'Stripe Webhook署名を確認できませんでした。'
        );
    }
}
