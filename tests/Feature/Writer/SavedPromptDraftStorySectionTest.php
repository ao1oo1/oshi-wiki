<?php

namespace Tests\Feature\Writer;

use App\Models\Role;
use App\Models\SavedPrompt;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkStorySection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavedPromptDraftStorySectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_form_shows_draft_and_published_sections(): void
    {
        $user = $this->writer();
        $work = $this->publishedWork();

        $published = $this->section(
            $work,
            '公開章',
            'published',
            1
        );
        $draft = $this->section(
            $work,
            '下書き章',
            'draft',
            2
        );
        $private = $this->section(
            $work,
            '非公開章',
            'private',
            3
        );

        $published->events()->create([
            'title' => '公開章の出来事',
            'sort_order' => 1,
        ]);
        $draft->events()->create([
            'title' => '下書き章の出来事',
            'sort_order' => 1,
        ]);
        $private->events()->create([
            'title' => '非公開章の出来事',
            'sort_order' => 1,
        ]);

        $prompt = SavedPrompt::query()->create([
            'user_id' => $user->id,
            'title' => '章表示確認',
            'work_source' => SavedPrompt::WORK_SOURCE_V1,
            'work_id' => $work->id,
            'status' => 'draft',
            'prompt_body' => '編集画面表示確認',
        ]);

        $response = $this->actingAs($user)
            ->get(route('writer.prompts.edit', $prompt));

        $response
            ->assertOk()
            ->assertSee('公開章')
            ->assertSee('下書き章')
            ->assertDontSee('非公開章');
    }

    public function test_draft_section_range_can_be_saved(): void
    {
        $user = $this->writer();
        $work = $this->publishedWork();

        $section = $this->section(
            $work,
            '下書き章',
            'draft',
            1
        );

        foreach (range(1, 25) as $number) {
            $section->events()->create([
                'title' => '出来事' . $number,
                'summary' => '内容' . $number,
                'sort_order' => $number,
            ]);
        }

        $response = $this->actingAs($user)
            ->post(
                route('writer.prompts.store'),
                [
                    'title' => '下書き章選択',
                    'category' => 'scene',
                    'purpose' => '下書き章の参照確認',
                    'work_ref' => 'work:' . $work->id,
                    'selected_story_event_ranges' => [
                        $section->id . ':1:20',
                    ],
                    'selected_character_refs' => [],
                    'writing_style' => 'dream_novel',
                    'genre' => 'daily_life',
                    'status' => 'active',
                ]
            );

        $prompt = SavedPrompt::query()
            ->where('user_id', $user->id)
            ->where('title', '下書き章選択')
            ->firstOrFail();

        $response->assertRedirect(
            route('writer.prompts.show', $prompt)
        );

        $this->assertCount(
            1,
            $prompt->selected_story_event_ranges
        );

        $savedRange =
            $prompt->selected_story_event_ranges[0];

        $this->assertSame(
            $section->id,
            (int) $savedRange['section_id']
        );
        $this->assertSame(
            1,
            (int) $savedRange['start']
        );
        $this->assertSame(
            20,
            (int) $savedRange['end']
        );
    }

    public function test_private_section_range_is_rejected(): void
    {
        $user = $this->writer();
        $work = $this->publishedWork();

        $section = $this->section(
            $work,
            '非公開章',
            'private',
            1
        );

        $section->events()->create([
            'title' => '非公開の出来事',
            'sort_order' => 1,
        ]);

        $this->actingAs($user)
            ->post(
                route('writer.prompts.store'),
                [
                    'title' => '非公開章選択',
                    'category' => 'scene',
                    'purpose' => '非公開章の拒否確認',
                    'work_ref' => 'work:' . $work->id,
                    'selected_story_event_ranges' => [
                        $section->id . ':1:1',
                    ],
                    'selected_character_refs' => [],
                    'writing_style' => 'dream_novel',
                    'genre' => 'daily_life',
                    'status' => 'active',
                ]
            )
            ->assertSessionHasErrors(
                'selected_story_event_ranges'
            );
    }

    public function test_soft_deleted_section_is_not_shown(): void
    {
        $user = $this->writer();
        $work = $this->publishedWork();

        $section = $this->section(
            $work,
            '削除済み章',
            'draft',
            1
        );

        $section->delete();

        $prompt = SavedPrompt::query()->create([
            'user_id' => $user->id,
            'title' => '削除済み非表示確認',
            'work_source' => SavedPrompt::WORK_SOURCE_V1,
            'work_id' => $work->id,
            'status' => 'draft',
            'prompt_body' => '削除済み章の表示確認',
        ]);

        $this->actingAs($user)
            ->get(route('writer.prompts.edit', $prompt))
            ->assertOk()
            ->assertDontSee('削除済み章');
    }

    private function writer(): User
    {
        $role = Role::query()->firstOrCreate(
            ['name' => User::ROLE_WRITER],
            [
                'label' => '一般執筆ユーザー',
                'description' => '小説執筆補助機能',
            ]
        );

        return User::factory()->create([
            'status' => 'active',
            'role_id' => $role->id,
            'is_super_admin' => false,
        ])->refresh();
    }

    private function publishedWork(): Work
    {
        return Work::factory()->create([
            'status' => 'published',
        ]);
    }

    private function section(
        Work $work,
        string $title,
        string $status,
        int $sortOrder
    ): WorkStorySection {
        return WorkStorySection::query()->create([
            'work_id' => $work->id,
            'section_type' => 'chapter',
            'title' => $title,
            'status' => $status,
            'spoiler_level' => 'none',
            'sort_order' => $sortOrder,
        ]);
    }
}
