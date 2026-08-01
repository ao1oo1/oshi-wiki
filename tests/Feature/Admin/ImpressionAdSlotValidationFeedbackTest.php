<?php

namespace Tests\Feature\Admin;

use App\Models\MonetizationService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpressionAdSlotValidationFeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_errors_are_visible_and_input_remains(): void
    {
        $user = $this->superAdmin();
        $service = $this->impressionService();

        $payload = [
            'monetization_service_id' => $service->id,
            'name' => 'Writer左広告',
            'page_scope' => 'all_public',
            'position' => 'writer_sidebar_1',
            'device_type' => 'all',
            'priority' => 0,
            'is_active' => 1,
        ];

        $this->actingAs($user)
            ->from(route('admin.monetization.ad-slots.index'))
            ->post(
                route('admin.monetization.ad-slots.store'),
                $payload
            )
            ->assertRedirect(
                route('admin.monetization.ad-slots.index')
            )
            ->assertSessionHasErrors('page_scope')
            ->assertSessionHasInput('name', 'Writer左広告');

        $this->followingRedirects()
            ->actingAs($user)
            ->from(route('admin.monetization.ad-slots.index'))
            ->post(
                route('admin.monetization.ad-slots.store'),
                $payload
            )
            ->assertOk()
            ->assertSee('入力内容をご確認ください。')
            ->assertSee(
                'Writer用の表示位置では、対象ページを'
                . '「Writer画面すべて」にしてください。'
            )
            ->assertSee('value="Writer左広告"', false);

        $this->assertDatabaseCount('impression_ad_slots', 0);
    }

    public function test_required_errors_are_visible(): void
    {
        $user = $this->superAdmin();
        $this->impressionService();

        $this->followingRedirects()
            ->actingAs($user)
            ->from(route('admin.monetization.ad-slots.index'))
            ->post(route('admin.monetization.ad-slots.store'), [
                'name' => '',
                'priority' => -1,
            ])
            ->assertOk()
            ->assertSee('入力内容をご確認ください。')
            ->assertSee('広告サービスを選択してください。')
            ->assertSee('スロット名を入力してください。')
            ->assertSee('対象ページを選択してください。')
            ->assertSee('表示位置を選択してください。')
            ->assertSee('表示端末を選択してください。')
            ->assertSee('利用状態を選択してください。');
    }

    public function test_service_validation_error_keeps_input(): void
    {
        $user = $this->superAdmin();

        $service = MonetizationService::query()->create([
            'name' => '無効広告',
            'slug' => 'inactive-ad',
            'category' => 'other',
            'revenue_model' => 'impression',
            'impression_ad_format' => 'script',
            'impression_script' =>
                '<script src="https://ads.example/ad.js"></script>',
            'allowed_script_hosts' => ['ads.example'],
            'ad_identifier' => 'inactive-ad',
            'priority' => 0,
            'is_active' => false,
        ]);

        $this->actingAs($user)
            ->from(route('admin.monetization.ad-slots.index'))
            ->post(route('admin.monetization.ad-slots.store'), [
                'monetization_service_id' => $service->id,
                'name' => '無効サービス',
                'page_scope' => 'home',
                'position' => 'page_top',
                'device_type' => 'all',
                'priority' => 0,
                'is_active' => 1,
            ])
            ->assertRedirect(
                route('admin.monetization.ad-slots.index')
            )
            ->assertSessionHasErrors('monetization_service_id')
            ->assertSessionHasInput('name', '無効サービス');
    }

    private function impressionService(): MonetizationService
    {
        return MonetizationService::query()->create([
            'name' => '広告サービス',
            'slug' => 'validation-ad',
            'category' => 'other',
            'revenue_model' => 'impression',
            'impression_ad_format' => 'script',
            'impression_script' =>
                '<script src="https://ads.example/ad.js"></script>',
            'allowed_script_hosts' => ['ads.example'],
            'ad_identifier' => 'validation-ad',
            'priority' => 0,
            'is_active' => true,
        ]);
    }

    private function superAdmin(): User
    {
        return User::factory()->create([
            'status' => 'active',
            'is_super_admin' => true,
        ]);
    }
}
