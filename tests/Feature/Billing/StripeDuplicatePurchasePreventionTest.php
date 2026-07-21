<?php

namespace Tests\Feature\Billing;

use App\Models\Role;
use App\Models\User;
use App\Models\UserBillingProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StripeDuplicatePurchasePreventionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.stripe.secret' => 'sk_test_example',
            'services.stripe.monthly_price_id' => 'price_monthly',
        ]);

        Cache::flush();
    }

    public function test_local_active_subscription_blocks_checkout(): void
    {
        $user = $this->writer();

        UserBillingProfile::query()->create([
            'user_id' => $user->id,
            'status' => 'active',
            'stripe_customer_id' => 'cus_active',
            'stripe_subscription_id' => 'sub_active',
        ]);

        Http::fake();

        $this
            ->actingAs($user)
            ->post(route('writer.billing.checkout'))
            ->assertRedirect(route('writer.billing.index'))
            ->assertSessionHas(
                'status',
                'すでにPlus契約が登録されています。'
                .'契約内容の確認・変更をご利用ください。'
            );

        Http::assertNothingSent();
    }

    public function test_stripe_active_subscription_blocks_checkout_when_local_status_is_stale(): void
    {
        $user = $this->writer();

        UserBillingProfile::query()->create([
            'user_id' => $user->id,
            'status' => 'expired',
            'stripe_customer_id' => 'cus_stale',
            'stripe_subscription_id' => 'sub_stale',
        ]);

        Http::fake([
            'api.stripe.com/v1/subscriptions/sub_stale' =>
                Http::response([
                    'id' => 'sub_stale',
                    'status' => 'active',
                    'items' => [
                        'data' => [[
                            'price' => [
                                'id' => 'price_monthly',
                            ],
                        ]],
                    ],
                ]),
        ]);

        $this
            ->actingAs($user)
            ->post(route('writer.billing.checkout'))
            ->assertRedirect(route('writer.billing.index'))
            ->assertSessionHasErrors('billing');

        Http::assertNotSent(
            fn ($request): bool =>
                $request->method() === 'POST'
                && $request->url()
                    === 'https://api.stripe.com/v1/checkout/sessions'
        );
    }

    public function test_repeated_checkout_request_reuses_same_session(): void
    {
        $user = $this->writer();

        Http::fake([
            'api.stripe.com/v1/checkout/sessions' =>
                Http::response([
                    'id' => 'cs_same',
                    'url' => 'https://checkout.stripe.com/same',
                ]),
        ]);

        $this
            ->actingAs($user)
            ->post(route('writer.billing.checkout'))
            ->assertRedirect('https://checkout.stripe.com/same');

        $this
            ->actingAs($user)
            ->post(route('writer.billing.checkout'))
            ->assertRedirect('https://checkout.stripe.com/same');

        Http::assertSentCount(1);
    }

    private function writer(): User
    {
        $role = Role::query()->firstOrCreate(
            ['name' => User::ROLE_WRITER],
            [
                'label' => 'Writer',
                'description' => 'Writer会員',
            ]
        );

        return User::factory()->create([
            'role_id' => $role->id,
            'status' => 'active',
        ]);
    }
}
