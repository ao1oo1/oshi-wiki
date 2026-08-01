<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class AdminStorySectionsMiddleMenuRemovedTest extends TestCase
{
    public function test_story_sections_index_has_no_duplicate_middle_navigation(): void
    {
        $view = file_get_contents(
            resource_path(
                'views/admin/work_story_sections/index.blade.php'
            )
        );

        $this->assertStringNotContainsString(
            "@include('admin.partials.navigation')",
            $view
        );

        $this->assertStringContainsString(
            "@include('admin.partials.flash')",
            $view
        );

        $this->assertStringContainsString(
            '章・編ごとの物語詳細',
            $view
        );

        $this->assertStringContainsString(
            '        <main class="oshi-admin-main">',
            $view
        );
    }
}
