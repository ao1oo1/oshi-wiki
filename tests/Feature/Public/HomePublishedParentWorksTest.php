<?php

namespace Tests\Feature\Public;

use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePublishedParentWorksTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_displays_all_published_parent_works_beyond_nine_items(): void
    {
        foreach (range(1, 10) as $index) {
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
            route('public.home')
        );

        $response
            ->assertOk()
            ->assertSee($olderParent->title);
    }

    public function test_home_excludes_child_and_unpublished_works(): void
    {
        $parent = Work::factory()->create([
            'title' => '公開親作品',
            'status' => 'published',
            'parent_work_id' => null,
        ]);

        Work::factory()->create([
            'title' => 'トップに直接出さない子作品',
            'status' => 'published',
            'parent_work_id' => $parent->id,
        ]);

        Work::factory()->create([
            'title' => '非公開親作品',
            'status' => 'draft',
            'parent_work_id' => null,
        ]);

        $this->get(route('public.home'))
            ->assertOk()
            ->assertSee('公開親作品')
            ->assertDontSee('トップに直接出さない子作品')
            ->assertDontSee('非公開親作品');
    }
}
