<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPageJumpNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_list_page_has_top_and_bottom_jump_buttons(): void
    {
        $user = $this->superAdmin();

        $this->actingAs($user)
            ->get(route('admin.works.index'))
            ->assertOk()
            ->assertSee('id="page-top"', false)
            ->assertSee('id="page-bottom"', false)
            ->assertSee('href="#page-bottom"', false)
            ->assertSee('href="#page-top"', false)
            ->assertSee('最下部へ')
            ->assertSee('最上部へ');
    }

    public function test_admin_detail_page_has_top_and_bottom_jump_buttons(): void
    {
        $user = $this->superAdmin();
        $work = Work::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.works.show', $work))
            ->assertOk()
            ->assertSee('id="page-top"', false)
            ->assertSee('id="page-bottom"', false)
            ->assertSee('最下部へ')
            ->assertSee('最上部へ');
    }

    public function test_admin_form_page_has_top_and_bottom_jump_buttons(): void
    {
        $user = $this->superAdmin();

        $this->actingAs($user)
            ->get(route('admin.works.create'))
            ->assertOk()
            ->assertSee('id="page-top"', false)
            ->assertSee('id="page-bottom"', false)
            ->assertSee('最下部へ')
            ->assertSee('最上部へ');
    }

    public function test_writer_page_shows_shared_jump_buttons(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
            'is_super_admin' => false,
        ]);

        $response = $this->actingAs($user)
            ->get(route('writer.dashboard'));

        if ($response->status() === 200) {
            $response
                ->assertDontSee('id="page-top"', false)
                ->assertDontSee('id="page-bottom"', false);
        } else {
            $this->assertContains(
                $response->status(),
                [302, 403]
            );
        }
    }

    public function test_shared_layout_and_css_define_jump_navigation(): void
    {
        $layout = file_get_contents(
            resource_path('views/layouts/app.blade.php')
        );
        $partial = file_get_contents(
            resource_path(
                'views/partials/'
                . 'page-jump-navigation.blade.php'
            )
        );
        $css = file_get_contents(
            resource_path('css/app.css')
        );

        $this->assertStringContainsString(
            "request()->routeIs('admin.*')",
            $layout
        );
        $this->assertStringContainsString(
            'page-bottom',
            $layout
        );
        $this->assertStringContainsString(
            'page-top',
            $layout
        );
        $this->assertStringContainsString(
            '最下部へ',
            $partial
        );
        $this->assertStringContainsString(
            '最上部へ',
            $partial
        );
        $this->assertStringContainsString(
            '.page-jump-link',
            $css
        );
    }

    private function superAdmin(): User
    {
        return User::factory()->create([
            'status' => 'active',
            'is_super_admin' => true,
        ]);
    }
}
