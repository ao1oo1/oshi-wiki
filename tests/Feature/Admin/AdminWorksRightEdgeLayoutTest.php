<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class AdminWorksRightEdgeLayoutTest extends TestCase
{
    public function test_admin_main_can_shrink_inside_grid(): void
    {
        $css = file_get_contents(
            resource_path('css/app.css')
        );

        $this->assertIsString($css);
        $this->assertStringContainsString(
            '.oshi-admin-main {',
            $css
        );
        $this->assertStringContainsString(
            'min-width: 0;',
            $css
        );
        $this->assertStringContainsString(
            'max-width: none;',
            $css
        );
    }

    public function test_work_table_scrolls_inside_its_container(): void
    {
        $css = file_get_contents(
            resource_path('css/app.css')
        );

        $this->assertIsString($css);
        $this->assertStringContainsString(
            '.admin-index-shell .oshi-table-wrap',
            $css
        );
        $this->assertStringContainsString(
            'overflow-x: auto;',
            $css
        );
        $this->assertStringContainsString(
            'max-width: 100%;',
            $css
        );
    }

    public function test_work_index_uses_responsive_table_wrapper(): void
    {
        $blade = file_get_contents(
            resource_path('views/admin/works/index.blade.php')
        );

        $this->assertIsString($blade);
        $this->assertStringContainsString(
            'staff-work-mobile-table-shell oshi-table-wrap',
            $blade
        );
        $this->assertStringContainsString(
            'admin-index-shell',
            $blade
        );
    }
}
