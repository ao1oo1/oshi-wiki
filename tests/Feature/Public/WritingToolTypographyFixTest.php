<?php

namespace Tests\Feature\Public;

use Tests\TestCase;

class WritingToolTypographyFixTest extends TestCase
{
    public function test_hero_heading_has_two_lines(): void
    {
        $source = $this->bladeSource();

        $this->assertStringContainsString(
            'キャラクター設定から<br>',
            $source
        );

        $this->assertStringContainsString(
            '小説用プロンプトまで、ひとつに。',
            $source
        );
    }

    public function test_typography_and_alignment_css_exists(): void
    {
        $source = $this->bladeSource();

        $this->assertStringContainsString(
            'WRITING_TOOL_TYPO_FIX_V3',
            $source
        );

        $this->assertStringContainsString(
            'text-align: left !important;',
            $source
        );

        $this->assertStringContainsString(
            'white-space: nowrap !important;',
            $source
        );

        $this->assertStringContainsString(
            'font-size: clamp(28px, 3vw, 44px) !important;',
            $source
        );
    }

    public function test_adjusted_page_is_rendered(): void
    {
        $this->get('/writing-tool')
            ->assertOk()
            ->assertSee('創作を補助するためのツールです')
            ->assertSee('創作に必要な情報をまとめて管理')
            ->assertSee('キャラクター設定から')
            ->assertSee('小説用プロンプトまで、ひとつに。');
    }

    private function bladeSource(): string
    {
        return file_get_contents(
            resource_path('views/public/writing-tool.blade.php')
        );
    }
}
