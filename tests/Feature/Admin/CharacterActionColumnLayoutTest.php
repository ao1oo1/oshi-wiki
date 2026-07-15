<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CharacterActionColumnLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_character_action_column_has_enough_width_and_no_wrapping(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)
            ->get(route('admin.characters.index'));

        $response
            ->assertOk()
            ->assertSee('min-w-[1050px] table-fixed', false)
            ->assertSee("w-[17%]", false)
            ->assertSee('admin-index-action-head', false)
            ->assertSee('>操作</th>', false);
    }

    public function test_character_action_layout_exists_in_blade_source(): void
    {
        $view = file_get_contents(
            resource_path('views/admin/characters/index.blade.php')
        );

        $this->assertStringContainsString(
            'mx-auto inline-flex w-fit flex-col items-stretch justify-center gap-2 whitespace-nowrap',
            $view
        );

        $this->assertGreaterThanOrEqual(
            3,
            substr_count($view, 'px-4 py-2')
        );

        $this->assertStringContainsString(
            "? 'w-[17%]' : 'w-[15%]'",
            $view
        );
    }
}
