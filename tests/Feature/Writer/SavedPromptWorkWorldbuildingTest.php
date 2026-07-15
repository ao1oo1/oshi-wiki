<?php

namespace Tests\Feature\Writer;

use App\Models\Role;
use App\Models\SavedPrompt;
use App\Models\User;
use App\Models\Work;
use App\Services\SavedPromptService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavedPromptWorkWorldbuildingTest extends TestCase
{
    use RefreshDatabase;

    public function test_selected_work_categories_are_added_to_prompt(): void
    {
        $user = User::factory()->create();
        $work = Work::factory()->create([
            'title' => '作品設定テスト',
            'status' => 'published',
            'timeline_setting' => '原作第10話以降',
            'building_layout' => '寮は二人部屋が基本。',
            'school_dorm_rules' => '門限は22時。',
        ]);

        $work->canonEvents()->create([
            'sort_order' => 0,
            'timing' => '春',
            'event_name' => '入学式',
            'event_status' => '起きている',
            'notes' => '主人公が初登場する。',
        ]);

        $work->termUsages()->create([
            'sort_order' => 0,
            'term' => '魔法石',
            'meaning' => '魔力を蓄える石',
            'usage_example' => '「魔法石を忘れるな」',
        ]);

        $prompt = app(SavedPromptService::class)->previewForUser($user, [
            'work_ref' => 'work:' . $work->id,
            'selected_character_refs' => [],
            'include_relationship_timeline' => false,
            'include_work_worldbuilding' => true,
            'selected_work_worldbuilding_categories' => [
                'story_design',
                'buildings',
                'canon_events',
                'term_usages',
            ],
            'writing_style' => 'web_novel',
            'genre' => 'fantasy',
        ]);

        $this->assertStringContainsString('【作品設定】', $prompt);
        $this->assertStringContainsString('原作第10話以降', $prompt);
        $this->assertStringContainsString('寮は二人部屋が基本。', $prompt);
        $this->assertStringContainsString('入学式', $prompt);
        $this->assertStringContainsString('魔法石', $prompt);
        $this->assertStringNotContainsString('門限は22時。', $prompt);
    }

    public function test_worldbuilding_is_removed_for_original_work(): void
    {
        $user = User::factory()->create();

        $savedPrompt = app(SavedPromptService::class)->createForUser($user, [
            'title' => 'オリジナル作品',
            'work_ref' => 'original',
            'selected_character_refs' => [],
            'include_relationship_timeline' => false,
            'include_work_worldbuilding' => true,
            'selected_work_worldbuilding_categories' => ['buildings'],
            'writing_style' => 'web_novel',
            'genre' => 'fantasy',
        ]);

        $this->assertSame(SavedPrompt::WORK_SOURCE_ORIGINAL, $savedPrompt->work_source);
        $this->assertFalse($savedPrompt->include_work_worldbuilding);
        $this->assertSame([], $savedPrompt->selected_work_worldbuilding_categories);
        $this->assertStringNotContainsString('【作品設定】', $savedPrompt->prompt_body);
    }

    public function test_prompt_form_shows_category_selection_ui(): void
    {
        $writerRole = Role::query()->firstOrCreate(
            ['name' => 'writer'],
            ['label' => '一般執筆ユーザー']
        );

        $user = User::factory()->create([
            'status' => 'active',
            'role_id' => $writerRole->id,
        ]);

        $response = $this->actingAs($user)->get(
            route('writer.prompts.create')
        );

        $response
            ->assertOk()
            ->assertSee('作品設定をプロンプトに反映する')
            ->assertSee('原作の重要イベント年表')
            ->assertSee('用語の使用例');
    }
}
