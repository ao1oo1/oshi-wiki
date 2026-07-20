<?php

namespace App\Services;

use App\Models\BillingPlan;
use App\Models\User;
use App\Models\UserBillingProfile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StripeWebhookService
{
    public function __construct(
        private readonly StripeApiService $stripe
    ) {
    }

    public function handle(array $event): void
    {
        $type = (string) ($event['type'] ?? '');
        $object = (array) data_get($event, 'data.object', []);

        match ($type) {
            'checkout.session.completed' =>
                $this->handleCheckout($object),
            'customer.subscription.created',
            'customer.subscription.updated',
            'customer.subscription.deleted' =>
                $this->syncSubscription($object),
            'invoice.paid' =>
                $this->handleInvoicePaid($object),
            'invoice.payment_failed' =>
                $this->handleInvoiceFailed($object),
            default => null,
        };
    }

    private function handleCheckout(array $session): void
    {
        $userId = (int) (
            $session['client_reference_id']
            ?? data_get($session, 'metadata.user_id', 0)
        );

        $user = User::query()->find($userId);

        if (! $user) {
            throw new RuntimeException(
                'Checkout対象ユーザーが見つかりません。'
            );
        }

        $profile = UserBillingProfile::query()->firstOrNew([
            'user_id' => $user->id,
        ]);

        $profile->stripe_customer_id =
            (string) ($session['customer'] ?? '');
        $profile->stripe_subscription_id =
            (string) ($session['subscription'] ?? '');
        $profile->save();

        if ($profile->stripe_subscription_id !== '') {
            $this->syncSubscription(
                $this->stripe->retrieveSubscription(
                    $profile->stripe_subscription_id
                )
            );
        }
    }

    private function syncSubscription(array $subscription): void
    {
        $subscriptionId = (string) ($subscription['id'] ?? '');
        $customerId = (string) ($subscription['customer'] ?? '');
        $userId = (int) data_get(
            $subscription,
            'metadata.user_id',
            0
        );

        $profile = UserBillingProfile::query()
            ->when(
                $subscriptionId !== '',
                fn ($query) => $query->where(
                    'stripe_subscription_id',
                    $subscriptionId
                )
            )
            ->when(
                $subscriptionId === '' && $customerId !== '',
                fn ($query) => $query->where(
                    'stripe_customer_id',
                    $customerId
                )
            )
            ->first();

        if (! $profile && $userId > 0) {
            $profile = UserBillingProfile::query()->firstOrNew([
                'user_id' => $userId,
            ]);
        }

        if (! $profile) {
            throw new RuntimeException(
                '契約対象の課金プロフィールが見つかりません。'
            );
        }

        $status = (string) ($subscription['status'] ?? 'incomplete');
        $cancelAtPeriodEnd = (bool) (
            $subscription['cancel_at_period_end'] ?? false
        );

        $plan = BillingPlan::query()
            ->where('slug', 'plus')
            ->first();

        DB::transaction(function () use (
            $profile,
            $plan,
            $subscription,
            $subscriptionId,
            $customerId,
            $status,
            $cancelAtPeriodEnd
        ): void {
            $profile->billing_plan_id = $plan?->id;
            $profile->stripe_customer_id =
                $customerId ?: $profile->stripe_customer_id;
            $profile->stripe_subscription_id =
                $subscriptionId ?: $profile->stripe_subscription_id;
            $profile->billing_cycle = 'monthly';
            $profile->status = $this->mapStatus(
                $status,
                $cancelAtPeriodEnd
            );
            $profile->current_period_start =
                $this->timestamp(
                    $subscription['current_period_start'] ?? null
                );
            $profile->current_period_end =
                $this->timestamp(
                    $subscription['current_period_end'] ?? null
                );
            $profile->cancel_at =
                $this->timestamp($subscription['cancel_at'] ?? null);
            $profile->canceled_at =
                $this->timestamp($subscription['canceled_at'] ?? null);

            if ($status === 'past_due') {
                $profile->grace_period_ends_at =
                    now()->addDays(
                        (int) config('billing.grace_days', 7)
                    );
            } elseif (
                in_array($status, ['active', 'trialing'], true)
            ) {
                $profile->grace_period_ends_at = null;
            }

            $profile->save();
        });
    }

    private function handleInvoicePaid(array $invoice): void
    {
        $profile = $this->profileFromInvoice($invoice);

        if (! $profile) {
            return;
        }

        $profile->last_payment_succeeded_at = now();
        $profile->last_payment_failure_code = null;
        $profile->last_payment_failed_at = null;
        $profile->grace_period_ends_at = null;

        if ($profile->status === 'past_due_grace') {
            $profile->status = 'active';
        }

        $profile->save();
    }

    private function handleInvoiceFailed(array $invoice): void
    {
        $profile = $this->profileFromInvoice($invoice);

        if (! $profile) {
            return;
        }

        $profile->status = 'past_due_grace';
        $profile->last_payment_failed_at = now();
        $profile->last_payment_failure_code = (string) data_get(
            $invoice,
            'last_finalization_error.code',
            'payment_failed'
        );
        $profile->grace_period_ends_at = now()->addDays(
            (int) config('billing.grace_days', 7)
        );
        $profile->save();
    }

    private function profileFromInvoice(
        array $invoice
    ): ?UserBillingProfile {
        $subscriptionId = (string) (
            $invoice['subscription'] ?? ''
        );
        $customerId = (string) ($invoice['customer'] ?? '');

        return UserBillingProfile::query()
            ->where(function ($query) use (
                $subscriptionId,
                $customerId
            ): void {
                if ($subscriptionId !== '') {
                    $query->where(
                        'stripe_subscription_id',
                        $subscriptionId
                    );
                }

                if ($customerId !== '') {
                    $query->orWhere(
                        'stripe_customer_id',
                        $customerId
                    );
                }
            })
            ->first();
    }

    private function mapStatus(
        string $status,
        bool $cancelAtPeriodEnd
    ): string {
        if ($cancelAtPeriodEnd && $status === 'active') {
            return 'canceling';
        }

        return match ($status) {
            'active', 'trialing' => $status,
            'past_due' => 'past_due_grace',
            'canceled' => 'canceled',
            'unpaid' => 'unpaid',
            default => $status,
        };
    }

    private function timestamp(mixed $value): ?Carbon
    {
        $timestamp = (int) $value;

        return $timestamp > 0
            ? Carbon::createFromTimestamp($timestamp)
            : null;
    }
}
