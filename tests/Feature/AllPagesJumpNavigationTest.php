<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AllPagesJumpNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_have_both_jump_buttons(): void
    {
        $work = Work::factory()->create([
            'status' => 'published',
        ]);

        foreach ([
            route('public.works.index'),
            route('public.works.show', $work),
            '/writing-tool',
        ] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('id="page-top"', false)
                ->assertSee('id="page-bottom"', false)
                ->assertSee('href="#page-bottom"', false)
                ->assertSee('href="#page-top"', false)
                ->assertSee('最下部へ')
                ->assertSee('最上部へ');
        }
    }

    public function test_admin_and_writer_pages_have_both_buttons(): void
    {
        $admin = User::factory()->create([
            'status' => 'active',
            'is_super_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('最下部へ')
            ->assertSee('最上部へ');

        $role = Role::query()->firstOrCreate(
            ['name' => User::ROLE_WRITER],
            [
                'label' => '一般執筆ユーザー',
                'description' => '小説執筆補助機能',
            ]
        );

        $writer = User::factory()->create([
            'status' => 'active',
            'role_id' => $role->id,
            'is_super_admin' => false,
        ]);

        $this->actingAs($writer)
            ->get(route('writer.dashboard'))
            ->assertOk()
            ->assertSee('最下部へ')
            ->assertSee('最上部へ');
    }

    public function test_guest_layout_has_both_jump_buttons(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('id="page-top"', false)
            ->assertSee('id="page-bottom"', false)
            ->assertSee('最下部へ')
            ->assertSee('最上部へ');
    }

    public function test_standalone_views_include_shared_navigation(): void
    {
        $paths = [
            'public/about/show.blade.php',
            'public/characters/show.blade.php',
            'public/coming-soon.blade.php',
            'public/contact/create.blade.php',
            'public/contributor/apply.blade.php',
            'public/staff/show.blade.php',
            'public/tags/index.blade.php',
            'public/works/index.blade.php',
            'public/works/show.blade.php',
            'public/writing-tool.blade.php',
            'welcome.blade.php',
            'writer/original_characters/_layout.blade.php',
        ];

        foreach ($paths as $path) {
            $contents = file_get_contents(
                resource_path('views/' . $path)
            );

            $this->assertStringContainsString(
                'partials.page-jump-navigation',
                $contents,
                $path
            );
            $this->assertStringContainsString(
                'id="page-top"',
                $contents,
                $path
            );
            $this->assertStringContainsString(
                'id="page-bottom"',
                $contents,
                $path
            );
        }
    }

    public function test_split_writer_layout_has_both_jump_buttons(): void
    {
        $start = file_get_contents(
            resource_path(
                'views/writer/original_characters/'
                . '_layout_start.blade.php'
            )
        );

        $end = file_get_contents(
            resource_path(
                'views/writer/original_characters/'
                . '_layout_end.blade.php'
            )
        );

        $this->assertStringContainsString(
            'partials.page-jump-navigation',
            $start
        );
        $this->assertStringContainsString(
            'id="page-top"',
            $start
        );
        $this->assertStringContainsString(
            'partials.page-jump-navigation',
            $end
        );
        $this->assertStringContainsString(
            'id="page-bottom"',
            $end
        );
    }

    public function test_shared_partial_uses_requested_labels(): void
    {
        $partial = file_get_contents(
            resource_path(
                'views/partials/'
                . 'page-jump-navigation.blade.php'
            )
        );

        $this->assertStringContainsString(
            '最下部へ',
            $partial
        );
        $this->assertStringContainsString(
            '最上部へ',
            $partial
        );
    }
}
