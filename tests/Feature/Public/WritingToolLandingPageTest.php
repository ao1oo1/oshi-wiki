<?php

namespace Tests\Feature\Public;

use Tests\TestCase;

class WritingToolLandingPageTest extends TestCase
{
    public function test_public_writing_tool_landing_page_is_available(): void
    {
        $response = $this->get(route('public.writing-tool.show'));

        $response
            ->assertOk()
            ->assertSee('キャラクター設定から')
            ->assertSee('小説用プロンプトまで、ひとつに。')
            ->assertSee('無料で新規登録する');
    }

    public function test_public_work_index_links_to_writing_tool_landing_page(): void
    {
        $view = file_get_contents(
            resource_path('views/public/works/index.blade.php')
        );

        $this->assertStringContainsString(
            '小説執筆補助ツールとは？',
            $view
        );
        $this->assertStringContainsString(
            "route('public.writing-tool.show')",
            $view
        );
    }
}
