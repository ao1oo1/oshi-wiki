<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class StripeApiService
{
    public function configured(): bool
    {
        return filled(config('services.stripe.secret'))
            && filled(config('services.stripe.monthly_price_id'));
    }

    public function createCheckoutSession(User $user): array
    {
        $this->ensureConfigured();

        $payload = [
            'mode' => 'subscription',
            'success_url' => route('writer.billing.index')
                . '?checkout=success&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('writer.billing.index')
                . '?checkout=canceled',
            'client_reference_id' => (string) $user->id,
            'customer_email' => $user->email,
            'line_items[0][price]' =>
                config('services.stripe.monthly_price_id'),
            'line_items[0][quantity]' => 1,
            'allow_promotion_codes' => 'false',
            'billing_address_collection' => 'auto',
            'metadata[user_id]' => (string) $user->id,
            'subscription_data[metadata][user_id]' =>
                (string) $user->id,
        ];

        if ($user->billingProfile?->stripe_customer_id) {
            unset($payload['customer_email']);
            $payload['customer'] =
                $user->billingProfile->stripe_customer_id;
        }

        return $this->post('/v1/checkout/sessions', $payload);
    }

    public function createPortalSession(User $user): array
    {
        $this->ensureConfigured();

        $customerId = $user->billingProfile?->stripe_customer_id;

        if (! $customerId) {
            throw new RuntimeException(
                'Stripeの顧客情報が登録されていません。'
            );
        }

        return $this->post('/v1/billing_portal/sessions', [
            'customer' => $customerId,
            'return_url' => route('writer.billing.index'),
        ]);
    }

    public function retrieveSubscription(string $subscriptionId): array
    {
        $this->ensureSecret();

        return $this->get(
            '/v1/subscriptions/' . rawurlencode($subscriptionId)
        );
    }

    private function post(string $path, array $payload): array
    {
        $response = $this->client()->asForm()->post(
            'https://api.stripe.com' . $path,
            $payload
        );

        return $this->decode($response->json(), $response->status());
    }

    private function get(string $path): array
    {
        $response = $this->client()->get(
            'https://api.stripe.com' . $path
        );

        return $this->decode($response->json(), $response->status());
    }

    private function client(): PendingRequest
    {
        return Http::withBasicAuth(
            (string) config('services.stripe.secret'),
            ''
        )
            ->acceptJson()
            ->timeout(20)
            ->connectTimeout(8)
            ->retry(1, 250, throw: false);
    }

    private function decode(
        mixed $body,
        int $status
    ): array {
        $data = is_array($body) ? $body : [];

        if ($status < 200 || $status >= 300) {
            $message = data_get(
                $data,
                'error.message',
                'Stripeとの通信に失敗しました。'
            );

            throw new RuntimeException((string) $message);
        }

        return $data;
    }

    private function ensureConfigured(): void
    {
        $this->ensureSecret();

        if (! filled(config('services.stripe.monthly_price_id'))) {
            throw new RuntimeException(
                'Stripe月額Price IDが設定されていません。'
            );
        }
    }

    private function ensureSecret(): void
    {
        if (! filled(config('services.stripe.secret'))) {
            throw new RuntimeException(
                'Stripeシークレットキーが設定されていません。'
            );
        }
    }
}
