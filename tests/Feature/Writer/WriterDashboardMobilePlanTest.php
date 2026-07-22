<?php

namespace Tests\Feature\Writer;

use App\Models\BillingPlan;
use App\Models\Role;
use App\Models\User;
use App\Models\UserBillingProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WriterDashboardMobilePlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_user_sees_mobile_plan_registration_card(): void
    {
        $user = $this->writer();

        $this->actingAs($user)
            ->get(route('writer.dashboard'))
            ->assertOk()
            ->assertSee('プラン管理')
            ->assertSee('無料プラン')
            ->assertSee('Oshi-Wiki Plusに登録')
            ->assertSee('md:hidden');
    }

    public function test_plus_user_sees_contract_confirmation_card(): void
    {
        $user = $this->plusWriter();

        $this->actingAs($user)
            ->get(route('writer.dashboard'))
            ->assertOk()
            ->assertSee('Oshi-Wiki Plus')
            ->assertSee('利用中')
            ->assertSee('契約内容を確認');
    }

    public function test_canceling_user_sees_end_date(): void
    {
        $user = $this->plusWriter();

        $user->billingProfile()->update([
            'status' => 'canceling',
            'current_period_end' => now()->addWeek()->startOfDay(),
        ]);

        $user = $user->fresh('billingProfile.plan');

        $this->actingAs($user)
            ->get(route('writer.dashboard'))
            ->assertOk()
            ->assertSee('解約予定')
            ->assertSee(
                $user->billingProfile
                    ->current_period_end
                    ->format('Y年n月j日')
            );
    }

    public function test_retention_user_sees_retention_deadline(): void
    {
        $user = $this->plusWriter();

        $user->billingProfile()->update([
            'status' => 'canceled',
            'current_period_end' => now()->subDay(),
            'retention_started_at' => now()->subDay(),
            'retention_ends_at' => now()->addMonths(3)->startOfDay(),
        ]);

        $user = $user->fresh('billingProfile.plan');

        $this->actingAs($user)
            ->get(route('writer.dashboard'))
            ->assertOk()
            ->assertSee('データ保管期間')
            ->assertSee(
                $user->billingProfile
                    ->retention_ends_at
                    ->format('Y年n月j日')
            );
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

    private function plusWriter(): User
    {
        $user = $this->writer();

        $plan = BillingPlan::query()->updateOrCreate(
            ['slug' => 'plus'],
            [
                'name' => 'Oshi-Wiki Plus',
                'monthly_price' => 480,
                'yearly_price' => 4800,
                'limits' => config('billing.plans.plus.limits'),
                'is_active' => true,
            ]
        );

        UserBillingProfile::query()->create([
            'user_id' => $user->id,
            'billing_plan_id' => $plan->id,
            'status' => 'active',
            'current_period_end' => now()->addMonth(),
        ]);

        return $user->fresh('billingProfile.plan');
    }
}
