<?php

namespace Tests\Feature\Public;

use App\Models\Character;
use App\Models\Work;
use App\Models\WorkStorySection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWorkStorySectionPhase2Test extends TestCase
{
    use RefreshDatabase;

    public function test_public_work_detail_shows_only_published_story_sections(): void
    {
        $work = Work::factory()->create(['status' => 'published']);
        WorkStorySection::query()->create(['work_id' => $work->id, 'section_type' => 'chapter', 'title' => '公開章', 'status' => 'published']);
        WorkStorySection::query()->create(['work_id' => $work->id, 'section_type' => 'chapter', 'title' => '非公開章', 'status' => 'private']);
        $this->get(route('public.works.show', $work))->assertOk()->assertSee('公開章')->assertDontSee('非公開章');
    }

    public function test_major_spoiler_section_is_collapsed_by_default(): void
    {
        $work = Work::factory()->create(['status' => 'published']);
        WorkStorySection::query()->create(['work_id' => $work->id, 'section_type' => 'chapter', 'title' => '重大ネタバレ章', 'spoiler_level' => 'major', 'status' => 'published']);
        $response = $this->get(route('public.works.show', $work));
        $response->assertOk()->assertSee('重大ネタバレ章')->assertSee('重大なネタバレ');
        $this->assertMatchesRegularExpression('/<details[^>]*>\s*<summary[^>]*>.*重大ネタバレ章/s', $response->getContent());
    }

    public function test_search_finds_work_by_story_section_title(): void
    {
        $work = Work::factory()->create(['title' => '検索対象作品', 'status' => 'published']);
        WorkStorySection::query()->create(['work_id' => $work->id, 'section_type' => 'chapter', 'title' => '真紅の暴君', 'status' => 'published']);
        $this->get(route('public.works.index', ['keyword' => '真紅の暴君']))->assertOk()->assertSee('検索対象作品');
    }

    public function test_search_finds_work_by_story_event_text(): void
    {
        $work = Work::factory()->create(['title' => 'イベント検索作品', 'status' => 'published']);
        $section = WorkStorySection::query()->create(['work_id' => $work->id, 'section_type' => 'chapter', 'title' => '第1章', 'status' => 'published']);
        $section->events()->create(['title' => '寮での騒動', 'summary' => '特別な魔法石を巡る事件', 'sort_order' => 1]);
        $this->get(route('public.works.index', ['keyword' => '特別な魔法石']))->assertOk()->assertSee('イベント検索作品');
    }

    public function test_search_finds_work_by_character_snapshot(): void
    {
        $work = Work::factory()->create(['title' => '時点検索作品', 'status' => 'published']);
        $character = Character::factory()->create(['work_id' => $work->id, 'name' => '時点キャラ', 'status' => 'published']);
        $work->linkedCharacters()->sync([$character->id => ['is_primary' => true, 'sort_order' => 0]]);
        $section = WorkStorySection::query()->create(['work_id' => $work->id, 'section_type' => 'chapter', 'title' => '第2章', 'status' => 'published']);
        $section->characters()->attach($character->id, ['appearance_type' => 'main', 'age_at_section' => '16歳', 'school_grade_at_section' => '1年生', 'affiliation_at_section' => '特別寮', 'sort_order' => 1]);
        $this->get(route('public.works.index', ['keyword' => '特別寮']))->assertOk()->assertSee('時点検索作品');
    }

    public function test_child_work_story_section_search_returns_parent_work(): void
    {
        $parent = Work::factory()->create(['title' => '親作品', 'status' => 'published', 'parent_work_id' => null]);
        $child = Work::factory()->create(['title' => '子作品', 'status' => 'published', 'parent_work_id' => $parent->id]);
        WorkStorySection::query()->create(['work_id' => $child->id, 'section_type' => 'chapter', 'title' => '子作品限定章名', 'status' => 'published']);
        $response = $this->get(route('public.works.index', ['keyword' => '子作品限定章名']));
        $response->assertOk()->assertSee('親作品')->assertDontSee('>子作品<', false);
    }

    public function test_private_story_section_is_not_searchable(): void
    {
        $work = Work::factory()->create(['title' => '非公開章作品', 'status' => 'published']);
        WorkStorySection::query()->create(['work_id' => $work->id, 'section_type' => 'chapter', 'title' => '秘密の非公開章', 'status' => 'private']);
        $this->get(route('public.works.index', ['keyword' => '秘密の非公開章']))->assertOk()->assertDontSee('非公開章作品');
    }
}
