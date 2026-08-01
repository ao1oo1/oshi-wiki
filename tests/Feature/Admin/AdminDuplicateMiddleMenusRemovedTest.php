<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class AdminDuplicateMiddleMenusRemovedTest extends TestCase
{
    public function test_target_admin_pages_use_full_width_without_duplicate_navigation(): void
    {
        $views = [
            'views/admin/works/edit.blade.php' =>
                '<main class="oshi-admin-main work-editor-page w-full max-w-none">',
            'views/admin/work_story_sections/text_import.blade.php' =>
                '<main class="oshi-admin-main w-full max-w-none">',
            'views/admin/work_story_sections/show.blade.php' =>
                '<main class="oshi-admin-main w-full max-w-none">',
            'views/admin/work_story_sections/edit.blade.php' =>
                '<main class="oshi-admin-main w-full max-w-none">',
        ];

        foreach ($views as $path => $expectedMain) {
            $view = file_get_contents(
                resource_path($path)
            );

            $this->assertStringContainsString(
                '<div class="w-full">',
                $view,
                $path
            );

            $this->assertStringContainsString(
                $expectedMain,
                $view,
                $path
            );

            $this->assertStringNotContainsString(
                "@include('admin.partials.navigation')",
                $view,
                $path
            );

            $this->assertStringNotContainsString(
                'class="oshi-admin-layout"',
                $view,
                $path
            );
        }
    }
}
