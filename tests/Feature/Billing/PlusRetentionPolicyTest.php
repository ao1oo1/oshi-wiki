<?php

namespace Tests\Feature\Billing;

use App\Models\BillingPlan;
use App\Models\OriginalCharacter;
use App\Models\User;
use App\Models\UserBillingProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PlusRetentionPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_canceled_subscription_starts_three_month_retention(): void
    {
        $user = User::factory()->create();
        $plan = BillingPlan::query()->firstOrCreate(
            ['slug' => 'plus'],
            [
                'name' => 'Oshi-Wiki Plus',
                'monthly_price' => 480,
                'yearly_price' => 4800,
                'limits' => [],
                'priority' => 20,
                'is_active' => true,
            ]
        );

        $profile = UserBillingProfile::query()->create([
            'user_id' => $user->id,
            'billing_plan_id' => $plan->id,
            'status' => 'canceled',
            'retention_started_at' => now(),
            'retention_ends_at' => now()->addMonthsNoOverflow(3),
        ]);

        $this->assertTrue($profile->isInRetentionPeriod());
        $this->assertFalse($profile->retentionHasExpired());
    }

    public function test_retention_user_can_view_and_export_but_cannot_write(): void
    {
        $user = User::factory()->create();

        $user->role()->associate(
            \App\Models\Role::query()->where('name', 'writer')->firstOrFail()
        );
        $user->status = 'active';
        $user->save();

        UserBillingProfile::query()->create([
            'user_id' => $user->id,
            'status' => 'canceled',
            'retention_started_at' => now(),
            'retention_ends_at' => now()->addMonthsNoOverflow(3),
        ]);

        $character = OriginalCharacter::query()->create([
            'user_id' => $user->id,
            'name' => '保管対象',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('writer.original-characters.index'))
            ->assertOk();

        $this->actingAs($user)
            ->post(route('writer.original-characters.store'), [
                'name' => '登録不可',
            ])
            ->assertRedirect(route('writer.billing.index'));

        $this->assertDatabaseHas('original_characters', [
            'id' => $character->id,
        ]);

        $this->actingAs($user)
            ->delete(
                route(
                    'writer.original-characters.destroy',
                    $character
                )
            )
            ->assertRedirect(route('writer.billing.index'));

        $this->assertDatabaseHas('original_characters', [
            'id' => $character->id,
        ]);
    }

    public function test_expired_retention_command_deletes_writer_data(): void
    {
        $user = User::factory()->create();

        $profile = UserBillingProfile::query()->create([
            'user_id' => $user->id,
            'status' => 'canceled',
            'retention_started_at' => now()->subMonths(4),
            'retention_ends_at' => now()->subMonth(),
        ]);

        OriginalCharacter::query()->create([
            'user_id' => $user->id,
            'name' => '削除対象',
            'status' => 'active',
        ]);

        Artisan::call('billing:purge-expired-writer-data');

        $this->assertDatabaseMissing('original_characters', [
            'user_id' => $user->id,
        ]);

        $this->assertNotNull(
            $profile->fresh()->writer_data_deleted_at
        );
    }
}
