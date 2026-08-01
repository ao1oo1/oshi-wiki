<?php

namespace Tests\Feature\Public;

use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorksIndexAllPublishedParentWorksTest extends TestCase
{
    use RefreshDatabase;

    public function test_works_index_displays_all_published_parent_works_beyond_twenty_items(): void
    {
        foreach (range(1, 21) as $index) {
            Work::factory()->create([
                'title' => "公開親作品{$index}",
                'status' => 'published',
                'parent_work_id' => null,
                'created_at' => now()->subMinutes($index),
            ]);
        }

        $olderParent = Work::factory()->create([
            'title' => '忍たま乱太郎（落第忍者乱太郎）',
            'status' => 'published',
            'parent_work_id' => null,
            'created_at' => now()->subYear(),
        ]);

        $response = $this->get(
            route('public.works.index')
        );

        $response
            ->assertOk()
            ->assertSee($olderParent->title);
    }

    public function test_works_index_excludes_child_and_unpublished_works(): void
    {
        $parent = Work::factory()->create([
            'title' => '公開親作品',
            'status' => 'published',
            'parent_work_id' => null,
        ]);

        Work::factory()->create([
            'title' => '直接表示しない子作品',
            'status' => 'published',
            'parent_work_id' => $parent->id,
        ]);

        Work::factory()->create([
            'title' => '非公開親作品',
            'status' => 'draft',
            'parent_work_id' => null,
        ]);

        $this->get(route('public.works.index'))
            ->assertOk()
            ->assertSee('公開親作品')
            ->assertDontSee('直接表示しない子作品')
            ->assertDontSee('非公開親作品');
    }

    public function test_works_index_search_still_filters_results(): void
    {
        Work::factory()->create([
            'title' => '忍たま乱太郎（落第忍者乱太郎）',
            'status' => 'published',
            'parent_work_id' => null,
        ]);

        Work::factory()->create([
            'title' => '別の公開作品',
            'status' => 'published',
            'parent_work_id' => null,
        ]);

        $this->get(route('public.works.index', [
            'keyword' => '忍たま',
        ]))
            ->assertOk()
            ->assertSee('忍たま乱太郎（落第忍者乱太郎）')
            ->assertDontSee('別の公開作品');
    }
}
