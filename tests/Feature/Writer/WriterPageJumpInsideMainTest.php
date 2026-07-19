<?php

namespace Tests\Feature\Writer;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WriterPageJumpInsideMainTest extends TestCase
{
    use RefreshDatabase;

    public function test_writer_layout_puts_top_jump_inside_main(): void
    {
        $contents = file_get_contents(
            resource_path(
                'views/writer/original_characters/'
                . '_layout_start.blade.php'
            )
        );

        $mainPosition = strpos(
            $contents,
            '<main class="flex-1'
        );

        $contentPosition = strpos(
            $contents,
            'mx-auto max-w-6xl'
        );

        $jumpPosition = strpos(
            $contents,
            'id="page-top"'
        );

        $this->assertNotFalse($mainPosition);
        $this->assertNotFalse($contentPosition);
        $this->assertNotFalse($jumpPosition);

        $this->assertGreaterThan(
            $mainPosition,
            $jumpPosition
        );

        $this->assertGreaterThan(
            $contentPosition,
            $jumpPosition
        );
    }

    public function test_writer_layout_puts_bottom_jump_inside_main(): void
    {
        $contents = file_get_contents(
            resource_path(
                'views/writer/original_characters/'
                . '_layout_end.blade.php'
            )
        );

        $jumpPosition = strpos(
            $contents,
            'id="page-bottom"'
        );

        $mainEndPosition = strpos(
            $contents,
            '</main>'
        );

        $this->assertNotFalse($jumpPosition);
        $this->assertNotFalse($mainEndPosition);

        $this->assertLessThan(
            $mainEndPosition,
            $jumpPosition
        );
    }

    public function test_writer_dashboard_renders_admin_style_jump_layout(): void
    {
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

        $response = $this->actingAs($writer)
            ->get(route('writer.dashboard'));

        $response
            ->assertOk()
            ->assertSee('id="page-top"', false)
            ->assertSee('id="page-bottom"', false)
            ->assertSee('最下部へ')
            ->assertSee('最上部へ');

        $html = $response->getContent();

        $mainPosition = strpos(
            $html,
            '<main class="flex-1'
        );

        $topJumpPosition = strpos(
            $html,
            'id="page-top"'
        );

        $bottomJumpPosition = strpos(
            $html,
            'id="page-bottom"'
        );

        $mainEndPosition = strpos(
            $html,
            '</main>'
        );

        $this->assertNotFalse($mainPosition);
        $this->assertNotFalse($topJumpPosition);
        $this->assertNotFalse($bottomJumpPosition);
        $this->assertNotFalse($mainEndPosition);

        $this->assertGreaterThan(
            $mainPosition,
            $topJumpPosition
        );

        $this->assertLessThan(
            $mainEndPosition,
            $bottomJumpPosition
        );
    }
}
