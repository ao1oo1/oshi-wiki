<?php

namespace Tests\Feature\Public;

use Tests\TestCase;

class WritingToolCtaCautionAlignTest extends TestCase
{
    public function test_cta_and_caution_adjustment_css_exists(): void
    {
        $source = file_get_contents(
            resource_path('views/public/writing-tool.blade.php')
        );

        $this->assertStringContainsString(
            'WRITING_TOOL_CTA_CAUTION_FIX_V4',
            $source
        );

        $this->assertStringContainsString(
            'align-items: center !important;',
            $source
        );

        $this->assertStringContainsString(
            'background: #fde7ef !important;',
            $source
        );

        $this->assertStringContainsString(
            'font-size: clamp(42px, 5.4vw, 74px) !important;',
            $source
        );
    }

    public function test_public_page_renders_target_sections(): void
    {
        $this->get('/writing-tool')
            ->assertOk()
            ->assertSee('ご利用前に')
            ->assertSee('創作を補助するためのツールです')
            ->assertSee('無料で始められます')
            ->assertSee('設定整理から、小説づくりをもっとスムーズに。');
    }
}
