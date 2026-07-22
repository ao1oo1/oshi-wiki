<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class AdminBreadcrumbsTest extends TestCase
{
    public function test_layout_places_breadcrumbs_before_top_page_jump_navigation(): void
    {
        $view = file_get_contents(resource_path('views/layouts/app.blade.php'));

        $breadcrumbPosition = strpos(
            $view,
            "@include('admin.partials.breadcrumbs')"
        );
        $jumpPosition = strpos(
            $view,
            "'partials.page-jump-navigation'",
            $breadcrumbPosition
        );

        $this->assertNotFalse($breadcrumbPosition);
        $this->assertNotFalse($jumpPosition);
        $this->assertLessThan($jumpPosition, $breadcrumbPosition);
    }

    public function test_breadcrumb_view_has_links_and_current_page_semantics(): void
    {
        $view = file_get_contents(
            resource_path('views/admin/partials/breadcrumbs.blade.php')
        );

        $this->assertStringContainsString(
            'aria-label="パンくずリスト"',
            $view
        );
        $this->assertStringContainsString('<a', $view);
        $this->assertStringContainsString('aria-current="page"', $view);
        $this->assertStringContainsString('＞', $view);
    }

    public function test_breadcrumb_builder_supports_real_work_and_story_section_names(): void
    {
        $support = file_get_contents(app_path('Support/AdminBreadcrumbs.php'));

        $this->assertStringContainsString(
            "self::modelLabel('work', ['title', 'name'], '作品')",
            $support
        );
        $this->assertStringContainsString(
            "self::labelFromModel(\$section, ['title', 'name'], '章・編')",
            $support
        );
        $this->assertStringContainsString(
            "self::routeParameter('storySection')",
            $support
        );
    }
}
