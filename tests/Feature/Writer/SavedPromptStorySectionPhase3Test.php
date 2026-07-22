<?php

namespace Tests\Feature\Writer;

use App\Models\Character;
use App\Models\Role;
use App\Models\SavedPrompt;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkStorySection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavedPromptStorySectionPhase3Test extends TestCase
{
    use RefreshDatabase;

    public function test_prompt_form_shows_sections_for_published_works(): void
    {
        $user = $this->writer();
        $work = Work::factory()->create([
            'title' => '章選択作品',
            'status' => 'published',
        ]);

        WorkStorySection::query()->create([
            'work_id' => $work->id,
            'section_type' => 'chapter',
            'title' => '公開第1章',
            'status' => 'published',
        ]);

        WorkStorySection::query()->create([
            'work_id' => $work->id,
            'section_type' => 'chapter',
            'title' => '非公開第2章',
            'status' => 'private',
        ]);

        $this->actingAs($user)
            ->get(route('writer.prompts.create'))
            ->assertOk()
            ->assertSee('参照する章・編')
            ->assertSee('公開第1章')
            ->assertDontSee('非公開第2章');
    }

    public function test_selected_section_is_saved_and_added_to_prompt(): void
    {
        $user = $this->writer();
        $work = Work::factory()->create([
            'title' => 'プロンプト作品',
            'status' => 'published',
        ]);

        $character = Character::factory()->create([
            'work_id' => $work->id,
            'name' => '章時点キャラ',
            'status' => 'published',
        ]);

        $work->linkedCharacters()->sync([
            $character->id => [
                'is_primary' => true,
                'sort_order' => 0,
            ],
        ]);

        $section = WorkStorySection::query()->create([
            'work_id' => $work->id,
            'section_type' => 'chapter',
            'title' => '第1章 始まり',
            'synopsis' => 'この章の概要です。',
            'cumulative_settings' => 'この章までの設定です。',
            'status' => 'published',
        ]);

        $section->events()->create([
            'title' => '重要な出来事',
            'summary' => '物語詳細本文',
            'sort_order' => 1,
        ]);

        $section->characters()->attach($character->id, [
            'appearance_type' => 'main',
            'age_at_section' => '16歳',
            'school_grade_at_section' => '1年',
            'affiliation_at_section' => '特別寮',
            'character_state' => 'まだ寮長ではない。',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($user)->post(
            route('writer.prompts.store'),
            $this->payload($work, $section)
        );

        $prompt = SavedPrompt::query()->firstOrFail();

        $response->assertRedirect(
            route('writer.prompts.show', $prompt)
        );

        $this->assertSame(
            $section->id,
            $prompt->work_story_section_id
        );

        $this->assertStringContainsString(
            '【参照する章・編】',
            $prompt->prompt_body
        );
        $this->assertStringContainsString(
            '第1章 始まり',
            $prompt->prompt_body
        );
        $this->assertStringContainsString(
            'この章までの設定です。',
            $prompt->prompt_body
        );
        $this->assertStringNotContainsString(
            '重要な出来事',
            $prompt->prompt_body
        );
        $this->assertStringContainsString(
            '詳細：',
            $prompt->prompt_body
        );
        $this->assertStringContainsString(
            '年齢：16歳',
            $prompt->prompt_body
        );
        $this->assertStringContainsString(
            '所属：特別寮',
            $prompt->prompt_body
        );
        $this->assertStringContainsString(
            'まだ寮長ではない。',
            $prompt->prompt_body
        );
    }

    public function test_section_from_other_work_is_rejected(): void
    {
        $user = $this->writer();

        $work = Work::factory()->create([
            'status' => 'published',
        ]);
        $otherWork = Work::factory()->create([
            'status' => 'published',
        ]);

        $section = WorkStorySection::query()->create([
            'work_id' => $otherWork->id,
            'section_type' => 'chapter',
            'title' => '別作品の章',
            'status' => 'published',
        ]);

        $this->actingAs($user)
            ->post(
                route('writer.prompts.store'),
                $this->payload($work, $section)
            )
            ->assertSessionHasErrors(
                'work_story_section_id'
            );

        $this->assertDatabaseCount('saved_prompts', 0);
    }

    public function test_private_section_is_rejected(): void
    {
        $user = $this->writer();
        $work = Work::factory()->create([
            'status' => 'published',
        ]);

        $section = WorkStorySection::query()->create([
            'work_id' => $work->id,
            'section_type' => 'chapter',
            'title' => '非公開章',
            'status' => 'private',
        ]);

        $this->actingAs($user)
            ->post(
                route('writer.prompts.store'),
                $this->payload($work, $section)
            )
            ->assertSessionHasErrors(
                'work_story_section_id'
            );
    }

    public function test_original_work_removes_section_selection(): void
    {
        $user = $this->writer();
        $work = Work::factory()->create([
            'status' => 'published',
        ]);

        $section = WorkStorySection::query()->create([
            'work_id' => $work->id,
            'section_type' => 'chapter',
            'title' => '公開章',
            'status' => 'published',
        ]);

        $payload = $this->payload($work, $section);
        $payload['work_ref'] = 'original';

        $this->actingAs($user)
            ->post(route('writer.prompts.store'), $payload)
            ->assertRedirect();

        $prompt = SavedPrompt::query()->firstOrFail();

        $this->assertNull($prompt->work_id);
        $this->assertNull(
            $prompt->work_story_section_id
        );
        $this->assertStringNotContainsString(
            '【参照する章・編】',
            $prompt->prompt_body
        );
    }

    private function payload(
        Work $work,
        WorkStorySection $section
    ): array {
        return [
            'title' => '章連携テスト',
            'category' => 'scene',
            'purpose' => 'テスト',
            'work_ref' => 'work:' . $work->id,
            'work_story_section_id' => $section->id,
            'selected_character_refs' => [],
            'writing_style' => 'dream_novel',
            'genre' => 'daily_life',
            'status' => 'active',
        ];
    }

    private function writer(): User
    {
        $role = Role::query()->firstOrCreate(
            ['name' => User::ROLE_WRITER],
            [
                'label' => '一般執筆ユーザー',
                'description' =>
                    '小説執筆補助機能を利用するユーザー',
            ]
        );

        return User::factory()->create([
            'status' => 'active',
            'role_id' => $role->id,
            'is_super_admin' => false,
        ])->refresh();
    }
}
