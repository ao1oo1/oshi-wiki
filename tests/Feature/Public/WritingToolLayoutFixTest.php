<?php

namespace Tests\Feature\Public;

use Tests\TestCase;

class WritingToolLayoutFixTest extends TestCase
{
    public function test_layout_fix_css_exists(): void
    {
        $source = file_get_contents(
            resource_path('views/public/writing-tool.blade.php')
        );

        $this->assertStringContainsString(
            'WRITING_TOOL_LAYOUT_FIX_V2',
            $source
        );
        $this->assertStringContainsString(
            'grid-template-columns: 1fr !important;',
            $source
        );
        $this->assertStringContainsString(
            'min-height: 194px !important;',
            $source
        );
        $this->assertStringContainsString(
            'margin-left: 0 !important;',
            $source
        );
        $this->assertStringContainsString(
            'display: block !important;',
            $source
        );
    }

    public function test_disclosure_is_split_into_two_lines(): void
    {
        $this->get('/writing-tool')
            ->assertOk()
            ->assertSee('writing-lp-ai-disclosure-line', false)
            ->assertSee('AIを使用していることを明記してください。')
            ->assertSee('また、差し支えなければ')
            ->assertSee('#oshiwiki')
            ->assertSee('投稿された作品を読みに伺います。');
    }
}
