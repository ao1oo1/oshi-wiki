<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminIndexResponsiveUiTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);
    }

    public function test_four_admin_lists_use_unified_responsive_layout(): void
    {
        $user = $this->superAdmin();

        foreach ([
            'admin.works.index',
            'admin.characters.index',
            'admin.character-relationships.index',
            'admin.tags.index',
        ] as $route) {
            $this->actingAs($user)
                ->get(route($route))
                ->assertOk()
                ->assertSee('admin-index-shell', false)
                ->assertSee('admin-index-header', false)
                ->assertSee('admin-index-filter-form', false)
                ->assertSee('admin-index-filter-grid', false)
                ->assertSee('キーワード（完全一致）')
                ->assertSee('すべての状態');
        }
    }

    public function test_common_css_has_mobile_layout(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('ADMIN_INDEX_UI_UNIFY_START', $css);
        $this->assertStringContainsString('repeat(auto-fit, minmax(210px, 1fr))', $css);
        $this->assertStringContainsString('@media (max-width: 767px)', $css);
        $this->assertStringContainsString('grid-template-columns: 1fr', $css);
    }
    public function test_work_filter_form_does_not_keep_legacy_flex_layout(): void
    {
        $view = file_get_contents(resource_path('views/admin/works/index.blade.php'));
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString(
            'class="admin-index-filter-form"',
            $view
        );

        $this->assertStringNotContainsString(
            'class="mb-6 flex flex-wrap items-end gap-3 admin-index-filter-form"',
            $view
        );

        $this->assertStringContainsString('display: block !important', $css);
        $this->assertStringContainsString('repeat(6, minmax(0, 1fr))', $css);
        $this->assertStringContainsString(
            '@media (min-width: 768px) and (max-width: 1279px)',
            $css
        );
    }

}
