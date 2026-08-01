<?php

namespace Tests\Feature;

use Tests\TestCase;

class MobileBottomJumpCircleTest extends TestCase
{
    public function test_shared_jump_partial_has_mobile_icon_classes(): void
    {
        $contents = file_get_contents(
            resource_path(
                'views/partials/page-jump-navigation.blade.php'
            )
        );

        $this->assertStringContainsString(
            'page-jump-link-bottom',
            $contents
        );
        $this->assertStringContainsString(
            'page-jump-link-top',
            $contents
        );
        $this->assertStringContainsString(
            'page-jump-icon',
            $contents
        );
        $this->assertStringContainsString(
            'page-jump-label',
            $contents
        );
        $this->assertStringContainsString(
            'aria-label=',
            $contents
        );
    }

    public function test_mobile_circle_css_targets_only_bottom_jump(): void
    {
        $contents = file_get_contents(
            resource_path('css/app.css')
        );

        $this->assertStringContainsString(
            '@media (max-width: 767px)',
            $contents
        );
        $this->assertStringContainsString(
            '.page-jump-link-bottom {',
            $contents
        );
        $this->assertStringContainsString(
            'border-radius: 9999px;',
            $contents
        );
        $this->assertStringContainsString(
            '.page-jump-link-bottom .page-jump-label',
            $contents
        );
        $this->assertStringNotContainsString(
            '.page-jump-link-top .page-jump-label {',
            $contents
        );
    }
}
