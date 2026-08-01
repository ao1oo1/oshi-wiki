<?php

namespace Tests\Feature\Admin;

use App\Models\MonetizationService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonetizationServiceValidationFeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_validation_errors_are_visible(): void
    {
        $user = $this->superAdmin();

        $response = $this->actingAs($user)
            ->from(route('admin.monetization.services.index'))
            ->post(route('admin.monetization.services.store'), [
                'name' => '',
                'slug' => 'Invalid Slug',
                'category' => 'other',
                'revenue_model' => 'impression',
                'impression_script' => '',
                'allowed_script_hosts_text' => '',
                'ad_identifier' => 'invalid id!',
                'priority' => -1,
                'is_active' => '1',
            ])
            ->assertRedirect(
                route('admin.monetization.services.index')
            )
            ->assertSessionHasErrors([
                'name',
                'slug',
                'impression_script',
                'allowed_script_hosts_text',
                'ad_identifier',
                'priority',
            ]);

        $this->followingRedirects()
            ->actingAs($user)
            ->from(route('admin.monetization.services.index'))
            ->post(route('admin.monetization.services.store'), [
                'name' => '',
                'slug' => 'Invalid Slug',
                'category' => 'other',
                'revenue_model' => 'impression',
                'impression_script' => '',
                'allowed_script_hosts_text' => '',
                'ad_identifier' => 'invalid id!',
                'priority' => -1,
                'is_active' => '1',
            ])
            ->assertOk()
            ->assertSee('入力内容をご確認ください。')
            ->assertSee('サービス名を入力してください。')
            ->assertSee(
                '登録・更新できなかった理由は以下のとおりです。'
            );
    }

    public function test_update_service_error_is_visible_and_input_remains(): void
    {
        $user = $this->superAdmin();

        $service = MonetizationService::query()->create([
            'name' => '更新対象',
            'slug' => 'update-target',
            'category' => 'other',
            'revenue_model' => 'affiliate_link',
            'priority' => 0,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->from(route(
                'admin.monetization.services.edit',
                $service
            ))
            ->put(route(
                'admin.monetization.services.update',
                $service
            ), [
                'name' => '更新対象',
                'slug' => 'update-target',
                'category' => 'other',
                'revenue_model' => 'impression',
                'impression_script' =>
                    '<script src="https://blocked.example/ad.js"></script>',
                'allowed_script_hosts_text' => 'allowed.example',
                'ad_identifier' => 'update-ad',
                'priority' => 0,
                'is_active' => '1',
            ])
            ->assertRedirect(route(
                'admin.monetization.services.edit',
                $service
            ))
            ->assertSessionHasErrors('impression_script')
            ->assertSessionHasInput(
                'ad_identifier',
                'update-ad'
            );

        $this->followingRedirects()
            ->actingAs($user)
            ->from(route(
                'admin.monetization.services.edit',
                $service
            ))
            ->put(route(
                'admin.monetization.services.update',
                $service
            ), [
                'name' => '更新対象',
                'slug' => 'update-target',
                'category' => 'other',
                'revenue_model' => 'impression',
                'impression_script' =>
                    '<script src="https://blocked.example/ad.js"></script>',
                'allowed_script_hosts_text' => 'allowed.example',
                'ad_identifier' => 'update-ad',
                'priority' => 0,
                'is_active' => '1',
            ])
            ->assertOk()
            ->assertSee('入力内容をご確認ください。')
            ->assertSee(
                '登録・更新できなかった理由は以下のとおりです。'
            );
    }

    private function superAdmin(): User
    {
        return User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);
    }
}
