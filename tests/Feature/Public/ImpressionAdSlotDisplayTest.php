<?php

namespace Tests\Feature\Public;

use App\Models\ImpressionAdSlot;
use App\Models\MonetizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpressionAdSlotDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_displays_active_top_ad(): void
    {
        $service = $this->service();

        ImpressionAdSlot::query()->create([
            'monetization_service_id' => $service->id,
            'name' => '全公開上部',
            'page_scope' => 'all_public',
            'position' => 'page_top',
            'device_type' => 'all',
            'priority' => 0,
            'is_active' => true,
        ]);

        $this->get(route('public.home'))
            ->assertOk()
            ->assertSee('data-ad-slot-id=', false)
            ->assertSee('data-admax-id="test-id"', false);
    }

    public function test_inactive_and_expired_ads_are_hidden(): void
    {
        $service = $this->service();

        ImpressionAdSlot::query()->create([
            'monetization_service_id' => $service->id,
            'name' => '期限切れ',
            'page_scope' => 'all_public',
            'position' => 'page_top',
            'device_type' => 'all',
            'priority' => 0,
            'is_active' => true,
            'ends_at' => now()->subMinute(),
        ]);

        $this->get(route('public.home'))
            ->assertOk()
            ->assertDontSee('data-ad-slot-id=', false);
    }

    private function service(): MonetizationService
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
