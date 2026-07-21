<?php

namespace Tests\Feature\Billing;

use App\Models\Role;
use App\Models\User;
use App\Models\UserBillingProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlusCancellationDataPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_billing_page_explains_data_retention_policy(): void
    {
        $user = $this->writer();

        $this->actingAs($user)
            ->get(route('writer.billing.index'))
            ->assertOk()
            ->assertSee('Plusを解約した後のデータについて')
            ->assertSee('創作データは3か月間保管されます')
            ->assertSee('閲覧とCSVエクスポート')
            ->assertSee('自動的に削除され、復元できません');
    }

    public function test_canceling_user_sees_end_date_notice(): void
    {
        $user = $this->writer();

        UserBillingProfile::query()->create([
            'user_id' => $user->id,
            'status' => 'canceling',
            'stripe_customer_id' => 'cus_canceling',
            'stripe_subscription_id' => 'sub_canceling',
            'current_period_end' => now()->addMonth(),
        ]);

        $this->actingAs($user)
            ->get(route('writer.billing.index'))
            ->assertOk()
            ->assertSee('Plusは解約予約済みです')
            ->assertSee('期日後は無料プランへ切り替わります');
    }

    private function writer(): User
    {
        $role = Role::query()->firstOrCreate(
            ['name' => User::ROLE_WRITER],
            ['label' => 'Writer', 'description' => 'Writer会員']
        );

        return User::factory()->create([
            'role_id' => $role->id,
            'status' => 'active',
        ]);
    }
}
