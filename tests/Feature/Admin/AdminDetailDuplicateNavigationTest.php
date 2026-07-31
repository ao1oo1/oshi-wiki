<?php

namespace Tests\Feature\Admin;

use App\Models\Character;
use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDetailDuplicateNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_work_detail_does_not_render_inner_admin_navigation(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);

        $work = Work::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('admin.works.show', $work));

        $response
            ->assertOk()
            ->assertSee($work->title);

        $view = file_get_contents(
            resource_path('views/admin/works/show.blade.php')
        );

        $this->assertStringNotContainsString(
            "@include('admin.partials.navigation')",
            $view
        );

        $this->assertStringNotContainsString(
            'class="oshi-admin-layout"',
            $view
        );
    }

    public function test_character_detail_does_not_render_top_admin_navigation(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);

        $work = Work::factory()->create();

        $character = Character::factory()->create([
            'work_id' => $work->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('admin.characters.show', $character));

        $response
            ->assertOk()
            ->assertSee($character->name);

        $view = file_get_contents(
            resource_path('views/admin/characters/show.blade.php')
        );

        $this->assertStringNotContainsString(
            "@include('admin.partials.navigation')",
            $view
        );
    }

    public function test_common_sidebar_partial_is_not_modified(): void
    {
        $navigation = resource_path(
            'views/admin/partials/navigation.blade.php'
        );

        $this->assertFileExists($navigation);

        $contents = file_get_contents($navigation);

        $this->assertStringContainsString(
            '作品管理',
            $contents
        );

        $this->assertStringContainsString(
            'キャラクター管理',
            $contents
        );
    }
}
