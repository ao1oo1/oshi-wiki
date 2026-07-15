<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CharacterListColumnLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_character_list_uses_fixed_balanced_column_widths(): void
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
            ->assertSee('<colgroup>', false)
            ->assertSee("w-[18%]", false)
            ->assertSee("w-[24%]", false)
            ->assertSee("w-[16%]", false)
            ->assertSee('whitespace-nowrap">キャラクター名', false)
            ->assertSee('whitespace-nowrap">作品', false)
            ->assertSee('whitespace-nowrap">所属', false);
    }

    public function test_character_and_work_cells_prevent_awkward_japanese_breaks(): void
    {
        $view = file_get_contents(
            resource_path('views/admin/characters/index.blade.php')
        );

        $this->assertStringContainsString(
            'font-bold leading-7 text-[#2D3748] break-keep',
            $view
        );

        $this->assertStringContainsString(
            'leading-7 text-[#4A5568] break-keep',
            $view
        );

        $this->assertStringContainsString(
            'px-4 py-4 align-middle text-[#4A5568] break-words',
            $view
        );
    }
}
