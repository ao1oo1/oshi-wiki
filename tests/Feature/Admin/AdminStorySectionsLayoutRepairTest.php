<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class AdminStorySectionsLayoutRepairTest extends TestCase
{
    public function test_story_sections_index_uses_full_width_single_column_layout(): void
    {
        $view = file_get_contents(
            resource_path(
                'views/admin/work_story_sections/index.blade.php'
            )
        );

        $this->assertStringContainsString(
            '<div class="w-full">',
            $view
        );

        $this->assertStringContainsString(
            '<main class="oshi-admin-main w-full max-w-none">',
            $view
        );

        $this->assertStringNotContainsString(
            'class="oshi-admin-layout"',
            $view
        );

        $this->assertStringNotContainsString(
            "@include('admin.partials.navigation')",
            $view
        );

        $this->assertStringContainsString(
            '章・編ごとの物語詳細',
            $view
        );
    }
}
