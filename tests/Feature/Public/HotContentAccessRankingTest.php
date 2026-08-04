<?php
namespace Tests\Feature\Public;

use Tests\TestCase;

class HotContentAccessRankingTest extends TestCase
{
    public function test_hot_content_is_registered_before_media_section(): void
    {
        $providers=file_get_contents(base_path('bootstrap/providers.php'));
        $middleware=file_get_contents(app_path('Http/Middleware/RecordPublicContentView.php'));
        $service=file_get_contents(app_path('Services/HotContentService.php'));
        $home=file_get_contents(base_path('resources/views/public/works/index.blade.php'));
        $partial=file_get_contents(resource_path('views/public/partials/hot-content.blade.php'));

        $this->assertStringContainsString('HotContentServiceProvider::class',$providers);
        $this->assertStringContainsString("'public.works.show'",$middleware);
        $this->assertStringContainsString("'public.characters.show'",$middleware);
        $this->assertStringContainsString('THROTTLE_MINUTES=30',$middleware);
        $this->assertStringContainsString('PERIOD_DAYS=7',$service);
        $this->assertStringContainsString('ITEM_LIMIT=6',$service);
        $this->assertStringContainsString('HOT作品',$partial);
        $this->assertStringContainsString('HOTキャラクター',$partial);
        $this->assertLessThan(
            strpos($home,'媒体から探す'),
            strpos($home,"public.partials.hot-content")
        );
    }
}
