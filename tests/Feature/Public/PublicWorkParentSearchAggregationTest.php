<?php

namespace Tests\Feature\Public;

use App\Models\Character;
use App\Models\Tag;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWorkParentSearchAggregationTest extends TestCase
{
    use RefreshDatabase;

    public function test_child_title_search_returns_parent_only(): void
    {
        $parent = Work::factory()->create([
            'title' => '検索集約親作品',
            'status' => 'published',
        ]);

        Work::factory()->create([
            'title' => '固有の子作品検索語',
            'parent_work_id' => $parent->id,
            'status' => 'published',
        ]);

        $this->get(route('public.works.index', [
            'keyword' => '固有の子作品検索語',
        ]))
            ->assertOk()
            ->assertSee('検索集約親作品')
            ->assertDontSee(
                'href="'
                . route(
                    'public.works.show',
                    Work::query()
                        ->where('title', '固有の子作品検索語')
                        ->firstOrFail()
                )
                . '"',
                false
            );
    }

    public function test_child_character_search_returns_parent(): void
    {
        $parent = Work::factory()->create([
            'title' => 'キャラ検索親作品',
            'status' => 'published',
        ]);

        $child = Work::factory()->create([
            'title' => 'キャラ検索子作品',
            'parent_work_id' => $parent->id,
            'status' => 'published',
        ]);

        $character = Character::factory()->create([
            'name' => '子作品専用検索キャラ',
            'work_id' => $child->id,
            'status' => 'published',
        ]);

        $child->linkedCharacters()->sync([
            $character->id => [
                'is_primary' => true,
                'sort_order' => 0,
            ],
        ]);

        $this->get(route('public.works.index', [
            'keyword' => '子作品専用検索キャラ',
        ]))
            ->assertOk()
            ->assertSee('キャラ検索親作品');
    }

    public function test_child_tag_filter_returns_parent(): void
    {
        $parent = Work::factory()->create([
            'title' => 'タグ検索親作品',
            'status' => 'published',
        ]);

        $child = Work::factory()->create([
            'title' => 'タグ検索子作品',
            'parent_work_id' => $parent->id,
            'status' => 'published',
        ]);

        $tag = Tag::factory()->create([
            'name' => '子作品専用タグ',
            'status' => 'published',
        ]);

        $child->tags()->sync([$tag->id]);

        $this->get(route('public.works.index', [
            'tag_id' => $tag->id,
        ]))
            ->assertOk()
            ->assertSee('タグ検索親作品');
    }
}
