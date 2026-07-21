<?php

namespace Tests\Feature\Billing;

use App\Models\BillingPlan;
use App\Models\Role;
use App\Models\User;
use App\Models\UserBillingProfile;
use App\Support\WritingAssistLimits;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StripeBillingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_writer_can_start_checkout(): void
    {
        config([
            'services.stripe.secret' => 'sk_test_example',
            'services.stripe.monthly_price_id' => 'price_monthly',
        ]);

        Http::fake([
            'api.stripe.com/v1/checkout/sessions' =>
                Http::response([
                    'id' => 'cs_test_1',
                    'url' => 'https://checkout.stripe.com/test',
                ]),
        ]);

        $user = $this->writer();

        $this->actingAs($user)
            ->post(route('writer.billing.checkout'))
            ->assertRedirect('https://checkout.stripe.com/test');

        Http::assertSent(fn ($request): bool =>
            $request->url()
                === 'https://api.stripe.com/v1/checkout/sessions'
            && $request['mode'] === 'subscription'
            && $request['line_items[0][price]']
                === 'price_monthly'
            && $request['line_items[0][quantity]'] === 1
        );
    }

    public function test_webhook_activates_plus_and_is_idempotent(): void
    {
        config([
            'services.stripe.secret' => 'sk_test_example',
            'services.stripe.webhook_secret' => 'whsec_test',
            'services.stripe.monthly_price_id' => 'price_monthly',
        ]);

        $user = $this->writer();
        BillingPlan::query()->updateOrCreate(
            ['slug' => 'plus'],
            [
                'name' => 'Oshi-Wiki Plus',
            'monthly_price' => 480,
            'yearly_price' => 4800,
            'limits' => config('billing.plans.plus.limits'),
            'is_active' => true,
        ]);

        Http::fake([
            'api.stripe.com/v1/subscriptions/sub_test_1' =>
                Http::response([
                    'id' => 'sub_test_1',
                    'customer' => 'cus_test_1',
                    'status' => 'active',
                    'current_period_start' => now()->timestamp,
                    'current_period_end' => now()->addMonth()->timestamp,
                    'cancel_at_period_end' => false,
                    'metadata' => ['user_id' => (string) $user->id],
                ]),
        ]);

        $event = [
            'id' => 'evt_test_1',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'client_reference_id' => (string) $user->id,
                    'customer' => 'cus_test_1',
                    'subscription' => 'sub_test_1',
                ],
            ],
        ];

        $payload = json_encode($event);
        $timestamp = time();
        $signature = hash_hmac(
            'sha256',
            $timestamp . '.' . $payload,
            'whsec_test'
        );
        $header = "t={$timestamp},v1={$signature}";

        $this->withHeader('Stripe-Signature', $header)
            ->postJson(route('stripe.webhook'), $event)
            ->assertOk();

        $this->withHeader('Stripe-Signature', $header)
            ->postJson(route('stripe.webhook'), $event)
            ->assertOk();

        $profile = UserBillingProfile::query()
            ->where('user_id', $user->id)
            ->firstOrFail();

        $this->assertSame('active', $profile->status);
        $this->assertSame('cus_test_1', $profile->stripe_customer_id);
        $this->assertDatabaseCount('billing_webhook_events', 1);

        $user->load('billingProfile.plan');

        $this->assertSame(
            150,
            WritingAssistLimits::originalCharactersPerUser($user)
        );
        $this->assertSame(
            200,
            WritingAssistLimits::storiesPerUser($user)
        );
    }

    public function test_invalid_webhook_signature_is_rejected(): void
    {
        config([
            'services.stripe.webhook_secret' => 'whsec_test',
        ]);

        $this->withHeader(
            'Stripe-Signature',
            't=' . time() . ',v1=invalid'
        )
            ->postJson(route('stripe.webhook'), [
                'id' => 'evt_invalid',
                'type' => 'invoice.paid',
                'data' => ['object' => []],
            ])
            ->assertStatus(400);
    }

    private function writer(): User
    {
        $role = Role::query()->firstOrCreate(
            ['name' => User::ROLE_WRITER],
            ['display_name' => 'Writer']
        );

        return User::factory()->create([
            'role_id' => $role->id,
            'status' => 'active',
        ]);
    }
}
