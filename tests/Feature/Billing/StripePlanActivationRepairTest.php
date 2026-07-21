<?php

namespace Tests\Feature\Billing;

use App\Models\BillingPlan;
use App\Models\User;
use App\Services\StripeApiService;
use App\Services\StripeWebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class StripePlanActivationRepairTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_creates_plus_plan_when_it_is_missing(): void
    {
        $user = User::factory()->create();

        $subscription = [
            'id' => 'sub_test_repair',
            'customer' => 'cus_test_repair',
            'status' => 'active',
            'cancel_at_period_end' => false,
            'current_period_start' => now()->timestamp,
            'current_period_end' => now()->addMonth()->timestamp,
            'metadata' => [
                'user_id' => (string) $user->id,
            ],
        ];

        $stripe = Mockery::mock(StripeApiService::class);
        $stripe
            ->shouldReceive('retrieveSubscription')
            ->once()
            ->with('sub_test_repair')
            ->andReturn($subscription);

        $service = new StripeWebhookService($stripe);

        $service->handle([
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'client_reference_id' => (string) $user->id,
                    'customer' => 'cus_test_repair',
                    'subscription' => 'sub_test_repair',
                ],
            ],
        ]);

        $plan = BillingPlan::query()
            ->where('slug', 'plus')
            ->first();

        $this->assertNotNull($plan);
        $this->assertSame(480, $plan->monthly_price);

        $profile = $user->fresh()->billingProfile;

        $this->assertNotNull($profile);
        $this->assertSame('active', $profile->status);
        $this->assertSame($plan->id, $profile->billing_plan_id);
        $this->assertTrue($profile->hasPaidAccess());
    }
}
