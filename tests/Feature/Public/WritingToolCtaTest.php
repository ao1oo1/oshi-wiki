<?php

namespace Tests\Feature\Public;

use Tests\TestCase;

class WritingToolCtaTest extends TestCase
{
    public function test_public_work_index_contains_writing_tool_registration_cta(): void
    {
        $view = file_get_contents(
            resource_path('views/public/works/index.blade.php')
        );

        $this->assertIsString($view);
        $this->assertStringContainsString(
            '小説執筆補助ツールのご利用はこちら',
            $view
        );
        $this->assertStringContainsString(
            "route('register')",
            $view
        );
        $this->assertStringContainsString(
            '無料で新規登録する',
            $view
        );

        $heroEnd = strpos($view, '</section>');
        $ctaPosition = strpos($view, 'oshi-writing-tool-cta');
        $tagPosition = strpos($view, 'タグから探す');

        $this->assertNotFalse($heroEnd);
        $this->assertNotFalse($ctaPosition);
        $this->assertNotFalse($tagPosition);
        $this->assertGreaterThan($heroEnd, $ctaPosition);
        $this->assertGreaterThan($ctaPosition, $tagPosition);
    }

    public function test_writing_tool_cta_has_responsive_styles(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString(
            '.oshi-writing-tool-cta',
            $css
        );
        $this->assertStringContainsString(
            '@media (max-width: 720px)',
            $css
        );
        $this->assertStringContainsString(
            'width: 100%',
            $css
        );
    }
}
