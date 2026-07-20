<?php

namespace Tests\Feature\Public;

use App\Models\AffiliateProgram;
use App\Models\MonetizationService;
use App\Models\Work;
use App\Models\WorkMonetizationLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWorkMonetizationDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_page_displays_available_affiliate_link(): void
    {
        [$work, $service, $program] = $this->setupWork();

        WorkMonetizationLink::query()->create([
            'work_id' => $work->id,
            'service_id' => $service->id,
            'affiliate_program_id' => $program->id,
            'product_code' => 'B012345678',
            'product_type' => 'series',
            'title' => '作品ページ',
            'availability_status' => 'available',
            'priority' => 0,
            'is_active' => true,
        ]);

        $this->get(route('public.works.show', $work))
            ->assertOk()
            ->assertSee('この作品を楽しむ')
            ->assertSee('広告・アフィリエイトリンク')
            ->assertSee('Amazonで見る')
            ->assertSee(
                route(
                    'public.monetization.redirect',
                    WorkMonetizationLink::query()->firstOrFail()->public_key
                ),
                false
            )
            ->assertDontSee(
                'https://www.amazon.co.jp/dp/B012345678?tag=oshi-22',
                false
            )
            ->assertSee('sponsored noopener noreferrer', false);
    }

    public function test_unavailable_or_disabled_links_are_hidden(): void
    {
        [$work, $service, $program] = $this->setupWork();

        WorkMonetizationLink::query()->create([
            'work_id' => $work->id,
            'service_id' => $service->id,
            'affiliate_program_id' => $program->id,
            'product_code' => 'B012345678',
            'product_type' => 'series',
            'availability_status' => 'checking',
            'priority' => 0,
            'is_active' => true,
        ]);

        $this->get(route('public.works.show', $work))
            ->assertOk()
            ->assertDontSee('この作品を楽しむ')
            ->assertDontSee('B012345678');
    }

    public function test_child_can_inherit_parent_links(): void
    {
        [$parent, $service, $program] = $this->setupWork();

        $child = Work::factory()->create([
            'parent_work_id' => $parent->id,
            'status' => 'published',
            'monetization_enabled' => true,
            'monetization_inheritance' => 'parent',
        ]);

        WorkMonetizationLink::query()->create([
            'work_id' => $parent->id,
            'service_id' => $service->id,
            'affiliate_program_id' => $program->id,
            'product_code' => 'B012345678',
            'product_type' => 'series',
            'title' => '親作品の商品',
            'availability_status' => 'available',
            'priority' => 0,
            'is_active' => true,
        ]);

        $this->get(route('public.works.show', $child))
            ->assertOk()
            ->assertSee('親作品の商品')
            ->assertSee($parent->title . 'の登録情報');
    }

    public function test_monetization_disabled_hides_links_but_keeps_official_store(): void
    {
        [$work, $service, $program] = $this->setupWork();

        $work->update([
            'monetization_enabled' => false,
            'official_store_url' => 'https://official.example.com/store',
        ]);

        WorkMonetizationLink::query()->create([
            'work_id' => $work->id,
            'service_id' => $service->id,
            'affiliate_program_id' => $program->id,
            'product_code' => 'B012345678',
            'product_type' => 'series',
            'availability_status' => 'available',
            'priority' => 0,
            'is_active' => true,
        ]);

        $this->get(route('public.works.show', $work))
            ->assertOk()
            ->assertDontSee('Amazonで見る')
            ->assertSee('公式販売ページを見る')
            ->assertSee('https://official.example.com/store', false);
    }

    private function setupWork(): array
    {
        $work = Work::factory()->create([
            'status' => 'published',
            'monetization_enabled' => true,
            'monetization_inheritance' => 'self',
        ]);

        $service = MonetizationService::query()->create([
            'name' => 'Amazon',
            'slug' => 'amazon-public',
            'category' => 'goods',
            'default_button_label' => 'Amazonで見る',
            'priority' => 0,
            'is_active' => true,
        ]);

        $program = AffiliateProgram::query()->create([
            'service_id' => $service->id,
            'name' => 'Amazon提携',
            'provider_name' => 'ASP',
            'url_template' =>
                'https://www.amazon.co.jp/dp/{product_code}'
                . '?tag={affiliate_identifier}',
            'affiliate_identifier' => 'oshi-22',
            'allowed_hosts' => ['amazon.co.jp'],
            'code_validation_pattern' => '/^[A-Z0-9]{10}$/',
            'priority' => 0,
            'is_default' => true,
            'is_affiliate' => true,
            'is_active' => true,
        ]);

        return [$work, $service, $program];
    }
}
