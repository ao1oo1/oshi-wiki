<?php

namespace Tests\Feature\Writer;

use App\Models\Role;
use App\Models\SavedPrompt;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkStorySection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavedPromptStoryEventAppearingCharactersTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_characters_are_added_to_prompt_context(): void
    {
        $role = Role::query()->firstOrCreate(
            ['name' => User::ROLE_WRITER],
            ['label' => '一般執筆ユーザー']
        );

        $user = User::factory()->create([
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        $work = Work::factory()->create([
            'status' => 'published',
        ]);

        $section = WorkStorySection::query()->create([
            'work_id' => $work->id,
            'section_type' => 'chapter',
            'title' => '第1章',
            'status' => 'published',
        ]);

        $section->events()->create([
            'event_number' => 1,
            'title' => '出来事',
            'summary' => '出来事の本文',
            'appearing_characters' =>
                '監督生・グリム・エース・デュース・クロウリー',
            'sort_order' => 1,
        ]);

        $prompt = app(
            \App\Services\SavedPromptService::class
        )->createForUser($user, [
            'title' => '登場人物プロンプト',
            'category' => 'scene',
            'purpose' => 'テスト',
            'work_ref' => 'work:' . $work->id,
            'selected_story_event_ranges' => [
                $section->id . ':1:1',
            ],
            'selected_character_refs' => [],
            'writing_style' => 'dream_novel',
            'genre' => 'daily_life',
            'status' => 'active',
        ]);

        $this->assertInstanceOf(SavedPrompt::class, $prompt);
        $this->assertStringContainsString(
            '登場キャラクター：監督生・グリム・エース・デュース・クロウリー',
            $prompt->prompt_body
        );
    }
}
