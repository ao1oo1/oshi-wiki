<?php

namespace Tests\Feature\Admin;

use App\Models\Tag;
use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWorksCompactTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_work_table_has_fixed_columns_and_compact_actions(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);

        $work = Work::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.works.index'))
            ->assertOk()
            ->assertSee('admin-works-table', false)
            ->assertSee('admin-works-col-actions', false)
            ->assertSee('admin-works-actions', false)
            ->assertSee(route('admin.works.show', $work), false)
            ->assertSee(route('admin.works.edit', $work), false);
    }

    public function test_only_four_tags_and_remaining_count_are_shown(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);

        $work = Work::factory()->create();

        $tags = collect(range(1, 7))->map(
            fn (int $number): Tag => Tag::factory()->create([
                'name' => '一覧タグ'.$number,
            ])
        );

        $work->tags()->sync($tags->pluck('id'));

        $response = $this->actingAs($user)
            ->get(route('admin.works.index'));

        $response
            ->assertOk()
            ->assertSee('一覧タグ1')
            ->assertSee('一覧タグ4')
            ->assertDontSee('一覧タグ5</span>', false)
            ->assertSee('＋3件');
    }

    public function test_compact_table_css_is_scoped_to_works_table(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString(
            '.admin-works-table {',
            $css
        );

        $this->assertStringContainsString(
            '.admin-works-actions-cell',
            $css
        );

        $this->assertStringContainsString(
            '.admin-works-tag {',
            $css
        );
    }
}
