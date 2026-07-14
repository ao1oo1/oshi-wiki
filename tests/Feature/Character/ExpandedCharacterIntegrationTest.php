<?php

namespace Tests\Feature\Character;

use App\Models\Character;
use App\Models\Work;
use App\Services\PromptCharacterContextBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpandedCharacterIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_work_search_finds_expanded_character_fields(): void
    {
        $work = Work::factory()->create([
            'title' => '検索対象作品',
            'status' => 'published',
        ]);

        Character::factory()->create([
            'work_id' => $work->id,
            'name' => '検索対象人物',
            'real_name' => '秘密の本名',
            'species' => '妖精族',
            'abilities' => '星の魔法',
            'status' => 'published',
        ]);

        $this->get(route('public.works.index', ['keyword' => '星の魔法']))
            ->assertOk()
            ->assertSee('検索対象作品');
    }

    public function test_public_work_detail_displays_expanded_character_summary(): void
    {
        $work = Work::factory()->create([
            'title' => '公開作品',
            'status' => 'published',
        ]);

        Character::factory()->create([
            'work_id' => $work->id,
            'name' => '公開人物',
            'gender' => '女性',
            'species' => '人間',
            'school_grade_class' => '2年B組',
            'occupation_position' => '寮長',
            'story_activities' => '第2章で活躍する。',
            'spoiler_level' => 'minor',
            'status' => 'published',
        ]);

        $this->get(route('public.works.show', $work))
            ->assertOk()
            ->assertSee('公開人物')
            ->assertSee('性別：女性')
            ->assertSee('種族：人間')
            ->assertSee('学校・学年・クラス：2年B組')
            ->assertSee('寮長')
            ->assertSee('ネタバレ：');
    }

    public function test_writer_prompt_uses_new_v1_character_fields(): void
    {
        $user = \App\Models\User::factory()->create();
        $work = Work::factory()->create([
            'title' => 'プロンプト作品',
            'status' => 'published',
        ]);

        $character = Character::factory()->create([
            'work_id' => $work->id,
            'name' => 'プロンプト人物',
            'school_grade_class' => '3年C組',
            'basic_tone' => '落ち着いた口調',
            'short_quote_examples' => '「承知しました」',
            'status' => 'published',
        ]);

        $context = app(PromptCharacterContextBuilder::class)->build(
            $user,
            ['v1:' . $character->id]
        );

        $this->assertStringContainsString('3年C組', $context['characters']);
        $this->assertStringContainsString('「承知しました」', $context['characters']);
    }
}
