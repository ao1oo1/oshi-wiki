<?php
namespace Tests\Feature\Admin;

use App\Models\MonetizationService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonetizationServiceAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_manage_services(): void
    {
        $user = $this->superAdmin();

        $this->actingAs($user)
            ->get(route('admin.monetization.services.index'))
            ->assertOk()
            ->assertSee('配信・販売サービス管理');

        $this->actingAs($user)
            ->post(route('admin.monetization.services.store'), [
                'name' => 'DMM TV',
                'slug' => 'dmm-tv',
                'category' => 'vod',
                'description' => '動画配信サービス',
                'default_button_label' => 'DMM TVで見る',
                'priority' => 10,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.monetization.services.index'));

        $service = MonetizationService::query()
            ->where('slug', 'dmm-tv')
            ->firstOrFail();

        $this->actingAs($user)
            ->put(route('admin.monetization.services.update', $service), [
                'name' => 'DMM TV 更新',
                'slug' => 'dmm-tv',
                'category' => 'vod',
                'description' => '更新後',
                'default_button_label' => '作品を見る',
                'priority' => 5,
                'is_active' => 0,
            ])
            ->assertRedirect(route('admin.monetization.services.index'));

        $this->assertDatabaseHas('monetization_services', [
            'id' => $service->id,
            'name' => 'DMM TV 更新',
            'priority' => 5,
            'is_active' => false,
        ]);

        $this->actingAs($user)
            ->delete(route('admin.monetization.services.destroy', $service))
            ->assertRedirect(route('admin.monetization.services.index'));

        $this->assertSoftDeleted('monetization_services', ['id' => $service->id]);
    }

    public function test_non_super_admin_cannot_access_service_management(): void
    {
        $staff = User::factory()->create([
            'status' => 'active',
            'is_super_admin' => false,
        ]);

        $this->actingAs($staff)
            ->get(route('admin.monetization.services.index'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->post(route('admin.monetization.services.store'), [
                'name' => '不正登録',
                'category' => 'vod',
                'priority' => 0,
                'is_active' => 1,
            ])
            ->assertForbidden();
    }

    public function test_navigation_is_visible_only_to_super_admin(): void
    {
        $superAdmin = $this->superAdmin();

        $this->actingAs($superAdmin)
            ->get(route('admin.dashboard'))
            ->assertSee('収益管理');

        $staff = User::factory()->create([
            'status' => 'active',
            'is_super_admin' => false,
        ]);

        $this->actingAs($staff)
            ->get(route('admin.dashboard'))
            ->assertDontSee('収益管理');
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->forceFill(['is_super_admin' => true])->save();

        return $user->refresh();
    }
}
