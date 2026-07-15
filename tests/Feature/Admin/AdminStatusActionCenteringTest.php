<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class AdminStatusActionCenteringTest extends TestCase
{
    public function test_four_admin_lists_use_common_centering_classes(): void
    {
        $views = [
            'admin/works/index.blade.php',
            'admin/characters/index.blade.php',
            'admin/character_relationships/index.blade.php',
            'admin/tags/index.blade.php',
        ];

        foreach ($views as $viewPath) {
            $view = file_get_contents(resource_path('views/' . $viewPath));

            $this->assertStringContainsString('admin-index-status-head', $view, $viewPath);
            $this->assertStringContainsString('admin-index-action-head', $view, $viewPath);
            $this->assertStringContainsString('admin-index-status-cell', $view, $viewPath);
            $this->assertStringContainsString('admin-index-action-cell', $view, $viewPath);
        }
    }

    public function test_common_css_centers_status_and_action_cells(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('ADMIN_INDEX_STATUS_ACTION_CENTERING_START', $css);
        $this->assertStringContainsString('text-align: center !important', $css);
        $this->assertStringContainsString('vertical-align: middle !important', $css);
        $this->assertStringContainsString('justify-content: center !important', $css);
        $this->assertStringContainsString('.admin-index-action-cell form', $css);
    }
}
