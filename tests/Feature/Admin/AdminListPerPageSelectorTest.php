<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class AdminListPerPageSelectorTest extends TestCase
{
    public function test_target_admin_views_offer_expected_page_sizes(): void
    {
        $views = [
            'views/admin/works/index.blade.php',
            'views/admin/characters/index.blade.php',
            'views/admin/character_relationships/index.blade.php',
            'views/admin/tags/index.blade.php',
        ];

        foreach ($views as $viewPath) {
            $view = file_get_contents(
                resource_path($viewPath)
            );

            $this->assertStringContainsString(
                'name="per_page"',
                $view,
                $viewPath
            );

            $this->assertStringContainsString(
                '@foreach ([30, 50, 100, 500] as $size)',
                $view,
                $viewPath
            );

            $this->assertStringContainsString(
                'onchange="this.form.submit()"',
                $view,
                $viewPath
            );
        }
    }

    public function test_target_controllers_whitelist_expected_page_sizes(): void
    {
        $controllers = [
            'Http/Controllers/Admin/WorkController.php',
            'Http/Controllers/Admin/CharacterController.php',
            'Http/Controllers/Admin/CharacterRelationshipController.php',
            'Http/Controllers/Admin/TagController.php',
        ];

        foreach ($controllers as $controllerPath) {
            $controller = file_get_contents(
                app_path($controllerPath)
            );

            $this->assertStringContainsString(
                '$allowedPerPage = [30, 50, 100, 500];',
                $controller,
                $controllerPath
            );

            $this->assertStringContainsString(
                "request('per_page', 30)",
                $controller,
                $controllerPath
            );

            $this->assertStringContainsString(
                '$perPage = 30;',
                $controller,
                $controllerPath
            );
        }
    }

    public function test_each_list_uses_selected_page_size(): void
    {
        $serviceControllers = [
            'Http/Controllers/Admin/WorkController.php',
            'Http/Controllers/Admin/CharacterController.php',
            'Http/Controllers/Admin/CharacterRelationshipController.php',
        ];

        foreach ($serviceControllers as $controllerPath) {
            $controller = file_get_contents(
                app_path($controllerPath)
            );

            $this->assertStringContainsString(
                '$perPage,',
                $controller,
                $controllerPath
            );
        }

        $tagController = file_get_contents(
            app_path('Http/Controllers/Admin/TagController.php')
        );

        $this->assertStringContainsString(
            '->paginate($perPage)',
            $tagController
        );
    }
}
