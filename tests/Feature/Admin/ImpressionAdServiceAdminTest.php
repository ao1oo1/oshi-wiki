<?php

namespace Tests\Feature\Admin;

use App\Models\MonetizationService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpressionAdServiceAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_register_shinobi_admax(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->forceFill(['is_super_admin' => true])->save();

        $script = '<script async '
            . 'src="https://adm.shinobi.jp/st/auto.js" '
            . 'data-admax-id="c1ab53ba195c669c5a67b27cc42cbb83">'
            . '</script>';

        $this->actingAs($user)
            ->post(route('admin.monetization.services.store'), [
                'name' => '忍者AdMax',
                'slug' => 'shinobi-admax',
                'category' => 'other',
                'revenue_model' => 'impression',
                'impression_script' => $script,
                'allowed_script_hosts_text' => 'adm.shinobi.jp',
                'ad_identifier' =>
                    'c1ab53ba195c669c5a67b27cc42cbb83',
                'description' => 'インプレッション課金型広告',
                'default_button_label' => null,
                'priority' => 0,
                'is_active' => 1,
            ])
            ->assertRedirect(
                route('admin.monetization.services.index')
            );

        $service = MonetizationService::query()->firstOrFail();

        $this->assertSame('impression', $service->revenue_model);
        $this->assertSame(
            ['adm.shinobi.jp'],
            $service->allowed_script_hosts
        );
        $this->assertSame($script, $service->impression_script);
    }

    public function test_unapproved_script_host_is_rejected(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->forceFill(['is_super_admin' => true])->save();

        $this->actingAs($user)
            ->from(route('admin.monetization.services.index'))
            ->post(route('admin.monetization.services.store'), [
                'name' => '不正広告',
                'slug' => 'invalid-ad',
                'category' => 'other',
                'revenue_model' => 'impression',
                'impression_script' =>
                    '<script src="https://evil.example/ad.js"></script>',
                'allowed_script_hosts_text' => 'adm.shinobi.jp',
                'ad_identifier' => 'invalid-ad',
                'description' => null,
                'default_button_label' => null,
                'priority' => 0,
                'is_active' => 1,
            ])
            ->assertRedirect(
                route('admin.monetization.services.index')
            )
            ->assertSessionHasErrors('impression_script');

        $this->assertDatabaseCount('monetization_services', 0);
    }

    public function test_inline_script_is_rejected(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->forceFill(['is_super_admin' => true])->save();

        $this->actingAs($user)
            ->from(route('admin.monetization.services.index'))
            ->post(route('admin.monetization.services.store'), [
                'name' => '不正広告',
                'slug' => 'inline-ad',
                'category' => 'other',
                'revenue_model' => 'impression',
                'impression_script' =>
                    '<script>alert("xss")</script>',
                'allowed_script_hosts_text' => 'adm.shinobi.jp',
                'ad_identifier' => 'inline-ad',
                'description' => null,
                'default_button_label' => null,
                'priority' => 0,
                'is_active' => 1,
            ])
            ->assertRedirect(
                route('admin.monetization.services.index')
            )
            ->assertSessionHasErrors('impression_script');
    }
}
