<?php

namespace Tests\Feature\Public;

use App\Models\AffiliateProgram;
use App\Models\LinkClick;
use App\Models\MonetizationService;
use App\Models\Work;
use App\Models\WorkMonetizationLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonetizationRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_key_redirects_and_records_hashed_click(): void
    {
        $link = $this->link();

        $response = $this
            ->withHeader('User-Agent', 'TestBrowser/1.0')
            ->withHeader(
                'Referer',
                'https://oshi-wiki.example/works/1?secret=value'
            )
            ->get(route('public.monetization.redirect', $link->public_key));

        $response
            ->assertRedirect(
                'https://www.amazon.co.jp/dp/B012345678?tag=oshi-22'
            )
            ->assertHeader('Cache-Control', 'no-store, private');

        $click = LinkClick::query()->firstOrFail();

        $this->assertSame($link->id, $click->work_monetization_link_id);
        $this->assertSame('oshi-wiki.example', $click->referer_host);
        $this->assertSame('/works/1', $click->referer_path);
        $this->assertSame(64, strlen($click->visitor_hash));
        $this->assertSame(64, strlen($click->user_agent_hash));
        $this->assertDatabaseMissing('link_clicks', [
            'referer_path' => '/works/1?secret=value',
        ]);
    }

    public function test_duplicate_click_within_ten_seconds_is_not_counted(): void
    {
        $link = $this->link();

        $this->withHeader('User-Agent', 'SameBrowser')
            ->get(route('public.monetization.redirect', $link->public_key));
        $this->withHeader('User-Agent', 'SameBrowser')
            ->get(route('public.monetization.redirect', $link->public_key));

        $this->assertDatabaseCount('link_clicks', 1);
    }

    public function test_invalid_or_inactive_link_returns_not_found(): void
    {
        $link = $this->link();
        $link->update(['availability_status' => 'ended']);

        $this->get(
            route('public.monetization.redirect', $link->public_key)
        )->assertNotFound();

        $this->assertDatabaseCount('link_clicks', 0);
    }

    public function test_destination_query_parameter_is_ignored(): void
    {
        $link = $this->link();

        $this->get(
            route(
                'public.monetization.redirect',
                $link->public_key
            ) . '?url=https://evil.example'
        )->assertRedirect(
            'https://www.amazon.co.jp/dp/B012345678?tag=oshi-22'
        );
    }

    private function link(): WorkMonetizationLink
    {
        $work = Work::factory()->create([
            'status' => 'published',
            'monetization_enabled' => true,
            'monetization_inheritance' => 'self',
        ]);

        $service = MonetizationService::query()->create([
            'name' => 'Amazon',
            'slug' => 'amazon-redirect',
            'category' => 'goods',
            'default_button_label' => 'Amazonで見る',
            'priority' => 0,
            'is_active' => true,
        ]);

        $program = AffiliateProgram::query()->create([
            'service_id' => $service->id,
            'name' => 'Amazon提携',
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

        return WorkMonetizationLink::query()->create([
            'work_id' => $work->id,
            'service_id' => $service->id,
            'affiliate_program_id' => $program->id,
            'product_code' => 'B012345678',
            'product_type' => 'series',
            'availability_status' => 'available',
            'priority' => 0,
            'is_active' => true,
        ]);
    }
}
