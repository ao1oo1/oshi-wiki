<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminListCountAndIdTest extends TestCase
{
    use RefreshDatabase;

    public function test_four_admin_lists_render_result_counts(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);

        foreach ([
            'admin.works.index',
            'admin.characters.index',
            'admin.character-relationships.index',
            'admin.tags.index',
        ] as $routeName) {
            $this->actingAs($user)
                ->get(route($routeName))
                ->assertOk()
                ->assertSee('検索結果')
                ->assertSee('全体')
                ->assertSee('data-admin-result-count', false);
        }
    }

    public function test_result_count_partial_defines_single_result_count_marker(): void
    {
        $content = file_get_contents(
            resource_path(
                'views/admin/partials/'
                . 'list-result-count.blade.php'
            )
        );

        $this->assertSame(
            1,
            substr_count(
                $content,
                'data-admin-result-count'
            )
        );
    }

    public function test_four_views_have_single_id_column_and_value_definition(): void
    {
        $files = [
            resource_path('views/admin/works/index.blade.php'),
            resource_path('views/admin/characters/index.blade.php'),
            resource_path(
                'views/admin/character_relationships/index.blade.php'
            ),
            resource_path('views/admin/tags/index.blade.php'),
        ];

        foreach ($files as $file) {
            $content = file_get_contents($file);

            $expectedCountPartialUses = str_contains(
                $file,
                'tags/index.blade.php'
            ) ? 2 : 1;

            $this->assertSame(
                $expectedCountPartialUses,
                substr_count(
                    $content,
                    'admin.partials.list-result-count'
                ),
                $file
            );

            $this->assertSame(
                1,
                substr_count($content, 'data-admin-id-column'),
                $file
            );

            $this->assertSame(
                1,
                substr_count($content, 'data-admin-id-value'),
                $file
            );

            $this->assertStringContainsString(
                '>ID</th>',
                $content,
                $file
            );

            $this->assertMatchesRegularExpression(
                '/\{\{\s*\$(work|character|relation|tag)->id\s*\}\}/',
                $content,
                $file
            );
        }
    }
}
