<?php

namespace Tests\Feature\Admin;

use App\Models\ImpressionAdSlot;
use App\Models\MonetizationService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpressionAdSlotAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_manage_impression_ad_slots(): void
    {
        $user = $this->superAdmin();
        $service = $this->impressionService();

        $payload = [
            'monetization_service_id' => $service->id,
            'name' => '作品詳細下部',
            'page_scope' => 'work_show',
            'position' => 'page_bottom',
            'device_type' => 'all',
            'priority' => 10,
            'is_active' => 1,
            'starts_at' => null,
            'ends_at' => null,
        ];

        $this->actingAs($user)
            ->post(
                route('admin.monetization.ad-slots.store'),
                $payload
            )
            ->assertRedirect(
                route('admin.monetization.ad-slots.index')
            );

        $slot = ImpressionAdSlot::query()->firstOrFail();

        $payload['name'] = '作品詳細下部更新';
        $payload['device_type'] = 'mobile';

        $this->actingAs($user)
            ->put(
                route(
                    'admin.monetization.ad-slots.update',
                    $slot
                ),
                $payload
            )
            ->assertRedirect(
                route('admin.monetization.ad-slots.index')
            );

        $this->assertDatabaseHas('impression_ad_slots', [
            'id' => $slot->id,
            'name' => '作品詳細下部更新',
            'device_type' => 'mobile',
        ]);

        $this->actingAs($user)
            ->delete(
                route(
                    'admin.monetization.ad-slots.destroy',
                    $slot
                )
            )
            ->assertRedirect(
                route('admin.monetization.ad-slots.index')
            );

        $this->assertSoftDeleted('impression_ad_slots', [
            'id' => $slot->id,
        ]);
    }

    public function test_affiliate_service_cannot_be_assigned(): void
    {
        $user = $this->superAdmin();

        $service = MonetizationService::query()->create([
            'name' => 'Amazon',
            'slug' => 'amazon',
            'category' => 'goods',
            'revenue_model' => 'affiliate_link',
            'priority' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->from(route('admin.monetization.ad-slots.index'))
            ->post(route('admin.monetization.ad-slots.store'), [
                'monetization_service_id' => $service->id,
                'name' => '不正',
                'page_scope' => 'all_public',
                'position' => 'page_top',
                'device_type' => 'all',
                'priority' => 0,
                'is_active' => 1,
                'starts_at' => null,
                'ends_at' => null,
            ])
            ->assertSessionHasErrors('monetization_service_id');
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->forceFill(['is_super_admin' => true])->save();

        return $user->refresh();
    }

    private function impressionService(): MonetizationService
    {
        return MonetizationService::query()->create([
            'name' => '忍者AdMax',
            'slug' => 'shinobi-admax',
            'category' => 'other',
            'revenue_model' => 'impression',
            'impression_script' =>
                '<script async src="https://adm.shinobi.jp/st/auto.js" '
                . 'data-admax-id="test-id"></script>',
            'allowed_script_hosts' => ['adm.shinobi.jp'],
            'ad_identifier' => 'test-id',
            'priority' => 0,
            'is_active' => true,
        ]);
    }
}
