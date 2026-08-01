<?php

namespace Tests\Feature\Admin;

use App\Models\MonetizationService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WriterAdSlotPageScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_writer_position_requires_writer_scope(): void
    {
        $user = $this->superAdmin();
        $service = $this->impressionService();

        $this->actingAs($user)
            ->from(route('admin.monetization.ad-slots.index'))
            ->post(route('admin.monetization.ad-slots.store'), [
                'monetization_service_id' => $service->id,
                'name' => 'Writer左広告',
                'page_scope' => 'all_public',
                'position' => 'writer_sidebar_1',
                'device_type' => 'all',
                'priority' => 0,
                'is_active' => 1,
            ])
            ->assertRedirect(
                route('admin.monetization.ad-slots.index')
            )
            ->assertSessionHasErrors('page_scope');

        $this->assertDatabaseCount('impression_ad_slots', 0);
    }

    public function test_writer_scope_requires_writer_position(): void
    {
        $user = $this->superAdmin();
        $service = $this->impressionService();

        $this->actingAs($user)
            ->from(route('admin.monetization.ad-slots.index'))
            ->post(route('admin.monetization.ad-slots.store'), [
                'monetization_service_id' => $service->id,
                'name' => '不正なWriter広告',
                'page_scope' => 'writer_all',
                'position' => 'page_bottom',
                'device_type' => 'all',
                'priority' => 0,
                'is_active' => 1,
            ])
            ->assertRedirect(
                route('admin.monetization.ad-slots.index')
            )
            ->assertSessionHasErrors('position');

        $this->assertDatabaseCount('impression_ad_slots', 0);
    }

    public function test_writer_position_and_scope_can_be_registered(): void
    {
        $user = $this->superAdmin();
        $service = $this->impressionService();

        $this->actingAs($user)
            ->post(route('admin.monetization.ad-slots.store'), [
                'monetization_service_id' => $service->id,
                'name' => 'Writer左広告',
                'page_scope' => 'writer_all',
                'position' => 'writer_sidebar_1',
                'device_type' => 'all',
                'priority' => 0,
                'is_active' => 1,
            ])
            ->assertRedirect(
                route('admin.monetization.ad-slots.index')
            )
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('impression_ad_slots', [
            'page_scope' => 'writer_all',
            'position' => 'writer_sidebar_1',
        ]);
    }

    private function impressionService(): MonetizationService
    {
        return MonetizationService::query()->create([
            'name' => 'Writer広告',
            'slug' => 'writer-ad',
            'category' => 'other',
            'revenue_model' => 'impression',
            'impression_ad_format' => 'script',
            'impression_script' =>
                '<script src="https://ads.example/ad.js"></script>',
            'allowed_script_hosts' => ['ads.example'],
            'ad_identifier' => 'writer-ad',
            'priority' => 0,
            'is_active' => true,
        ]);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);
        $user->forceFill(['is_super_admin' => true])->save();

        return $user;
    }
}
