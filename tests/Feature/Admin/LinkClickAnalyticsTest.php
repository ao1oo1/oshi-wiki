<?php

namespace Tests\Feature\Admin;

use App\Models\AffiliateProgram;
use App\Models\LinkClick;
use App\Models\MonetizationService;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkMonetizationLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinkClickAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_click_analytics(): void
    {
        [$work, $service, $program, $link] = $this->records();

        LinkClick::query()->create([
            'work_monetization_link_id' => $link->id,
            'work_id' => $work->id,
            'service_id' => $service->id,
            'affiliate_program_id' => $program->id,
            'visitor_hash' => str_repeat('a', 64),
            'user_agent_hash' => str_repeat('b', 64),
            'referer_host' => 'oshi-wiki.example',
            'referer_path' => '/works/1',
            'clicked_at' => now(),
        ]);

        $user = User::factory()->create(['status' => 'active']);
        $user->forceFill(['is_super_admin' => true])->save();

        $this->actingAs($user)
            ->get(route('admin.monetization.analytics.index'))
            ->assertOk()
            ->assertSee('クリック集計')
            ->assertSee($work->title)
            ->assertSee($service->name)
            ->assertDontSee(str_repeat('a', 64));
    }

    public function test_staff_cannot_view_click_analytics(): void
    {
        $staff = User::factory()->create([
            'status' => 'active',
            'is_super_admin' => false,
        ]);

        $this->actingAs($staff)
            ->get(route('admin.monetization.analytics.index'))
            ->assertForbidden();
    }

    private function records(): array
    {
        $work = Work::factory()->create();

        $service = MonetizationService::query()->create([
            'name' => 'サービス',
            'slug' => 'analytics-service',
            'category' => 'goods',
            'priority' => 0,
            'is_active' => true,
        ]);

        $program = AffiliateProgram::query()->create([
            'service_id' => $service->id,
            'name' => 'プログラム',
            'url_template' =>
                'https://shop.example.com/{product_code}',
            'allowed_hosts' => ['shop.example.com'],
            'priority' => 0,
            'is_default' => true,
            'is_affiliate' => true,
            'is_active' => true,
        ]);

        $link = WorkMonetizationLink::query()->create([
            'work_id' => $work->id,
            'service_id' => $service->id,
            'affiliate_program_id' => $program->id,
            'product_code' => 'ABC123',
            'product_type' => 'series',
            'availability_status' => 'available',
            'priority' => 0,
            'is_active' => true,
        ]);

        return [$work, $service, $program, $link];
    }
}
