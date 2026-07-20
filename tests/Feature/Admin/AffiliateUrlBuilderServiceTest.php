<?php

namespace Tests\Feature\Admin;

use App\Models\AffiliateProgram;
use App\Models\MonetizationService;
use App\Models\Work;
use App\Models\WorkMonetizationLink;
use App\Services\AffiliateUrlBuilderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AffiliateUrlBuilderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_builds_url_without_storing_the_completed_affiliate_url(): void
    {
        [$program, $link] = $this->makeLink();

        $url = app(AffiliateUrlBuilderService::class)->build($link, 'work-page');

        $this->assertSame(
            'https://shop.example.com/items/B012345678?aff=oshi-tag&sub=work-page',
            $url
        );
        $this->assertSame('B012345678', $link->fresh()->product_code);
    }

    public function test_it_rejects_an_unapproved_destination_host(): void
    {
        [$program, $link] = $this->makeLink();

        $program->update([
            'url_template' => 'https://malicious.example.net/{product_code}',
        ]);

        $this->expectException(ValidationException::class);

        app(AffiliateUrlBuilderService::class)->build($link);
    }

    private function makeLink(): array
    {
        $work = Work::query()->create([
            'title' => '収益化テスト作品',
            'slug' => 'monetization-test-work',
            'status' => 'published',
            'monetization_enabled' => true,
        ]);

        $service = MonetizationService::query()->create([
            'name' => 'テストストア',
            'slug' => 'test-store',
            'category' => 'goods',
            'is_active' => true,
        ]);

        $program = AffiliateProgram::query()->create([
            'service_id' => $service->id,
            'name' => 'テスト提携',
            'url_template' => 'https://shop.example.com/items/{product_code}?aff={affiliate_identifier}&sub={sub_id}',
            'affiliate_identifier' => 'oshi-tag',
            'allowed_hosts' => ['shop.example.com'],
            'code_validation_pattern' => '/^[A-Z0-9]{10}$/',
            'is_active' => true,
        ]);

        $link = WorkMonetizationLink::query()->create([
            'work_id' => $work->id,
            'service_id' => $service->id,
            'affiliate_program_id' => $program->id,
            'product_code' => 'B012345678',
            'product_type' => 'goods',
            'availability_status' => 'available',
            'is_active' => true,
        ]);

        return [$program, $link];
    }
}
