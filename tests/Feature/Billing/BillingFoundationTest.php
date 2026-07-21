<?php

namespace Tests\Feature\Billing;

use App\Models\BillingPlan;
use App\Models\User;
use App\Models\UserBillingProfile;
use App\Services\BillingEntitlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BillingFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_billing_tables_exist(): void
    {
        $this->assertTrue(Schema::hasTable('billing_plans'));
        $this->assertTrue(Schema::hasTable('user_billing_profiles'));
        $this->assertTrue(Schema::hasTable('billing_webhook_events'));
    }

    public function test_active_plus_profile_has_paid_access(): void
    {
        $user = User::factory()->create();
        $plan = BillingPlan::query()->updateOrCreate(
            ['slug' => 'plus'],
            [
                'name' => 'Oshi-Wiki Plus',
            'monthly_price' => 480,
            'yearly_price' => 4800,
            'limits' => config('billing.plans.plus.limits'),
            'is_active' => true,
        ]);

        UserBillingProfile::query()->create([
            'user_id' => $user->id,
            'billing_plan_id' => $plan->id,
            'status' => 'active',
            'current_period_end' => now()->addMonth(),
        ]);

        $user->load('billingProfile.plan');

        $service = app(BillingEntitlementService::class);

        $this->assertTrue($service->hasPlusAccess($user));
        $this->assertSame(
            150,
            $service->limit($user, 'original_characters')
        );
    }

    public function test_expired_profile_falls_back_to_free(): void
    {
        $user = User::factory()->create();
        $plan = BillingPlan::query()->updateOrCreate(
            ['slug' => 'plus'],
            [
                'name' => 'Oshi-Wiki Plus',
            'monthly_price' => 480,
            'yearly_price' => 4800,
            'limits' => config('billing.plans.plus.limits'),
            'is_active' => true,
        ]);

        UserBillingProfile::query()->create([
            'user_id' => $user->id,
            'billing_plan_id' => $plan->id,
            'status' => 'canceled',
            'current_period_end' => now()->subDay(),
        ]);

        $service = app(BillingEntitlementService::class);

        $this->assertFalse($service->hasPlusAccess($user->load('billingProfile.plan')));
        $this->assertSame(
            30,
            $service->limit($user, 'original_characters')
        );
    }
}
