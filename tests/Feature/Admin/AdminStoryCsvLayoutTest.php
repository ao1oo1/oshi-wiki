<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class AdminStoryCsvLayoutTest extends TestCase
{
    public function test_story_csv_page_uses_one_column_layout(): void
    {
        $view = file_get_contents(
            resource_path(
                'views/admin/work_story_sections/csv.blade.php'
            )
        );

        $this->assertStringContainsString(
            'grid grid-cols-1 gap-5',
            $view
        );

        $this->assertStringNotContainsString(
            'lg:grid-cols-3',
            $view
        );

        $this->assertStringContainsString(
            'oshi-card w-full max-w-none',
            $view
        );
    }

    public function test_story_csv_page_removes_duplicate_navigation(): void
    {
        $view = file_get_contents(
            resource_path(
                'views/admin/work_story_sections/csv.blade.php'
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
    }

    public function test_story_csv_page_keeps_all_three_csv_cards(): void
    {
        $view = file_get_contents(
            resource_path(
                'views/admin/work_story_sections/csv.blade.php'
            )
        );

        $this->assertStringContainsString('章・編CSV', $view);
        $this->assertStringContainsString('物語詳細CSV', $view);
        $this->assertStringContainsString(
            '登場キャラクターCSV',
            $view
        );
    }
}
