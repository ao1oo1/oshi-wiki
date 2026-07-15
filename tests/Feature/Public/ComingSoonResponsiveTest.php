<?php

namespace Tests\Feature\Public;

use Tests\TestCase;

class ComingSoonResponsiveTest extends TestCase
{
    public function test_coming_soon_view_contains_responsive_layout_rules(): void
    {
        $view = file_get_contents(
            resource_path('views/public/coming-soon.blade.php')
        );

        $this->assertIsString($view);
        $this->assertStringContainsString(
            'viewport-fit=cover',
            $view
        );
        $this->assertStringContainsString(
            'min-height: 100svh',
            $view
        );
        $this->assertStringContainsString(
            '@media (max-width: 640px)',
            $view
        );
        $this->assertStringContainsString(
            'overflow-x: hidden',
            $view
        );
        $this->assertStringContainsString(
            'max-width: 360px',
            $view
        );
        $this->assertStringContainsString(
            '.lead br',
            $view
        );
    }
}
