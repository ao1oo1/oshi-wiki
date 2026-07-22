<?php

namespace Tests\Feature\Writer;

use App\Models\Role;
use App\Models\SavedPrompt;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkStorySection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavedPromptStoryEventRangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_shows_twenty_event_ranges_in_accordion(): void
    {
        $user = $this->writer();
        [$work, $section] = $this->sectionWithEvents(45);

        $this->actingAs($user)
            ->get(route('writer.prompts.create'))
            ->assertOk()
            ->assertSee('参照する章・編と物語詳細')
            ->assertSee('1～20を挿入')
            ->assertSee('21～40を挿入')
            ->assertSee('41～45を挿入')
            ->assertSee(
                'story-section-range-item',
                false
            );
    }

    public function test_selected_range_is_saved_and_only_range_is_inserted(): void
    {
        $user = $this->writer();
        [$work, $section] = $this->sectionWithEvents(45);

        $response = $this->actingAs($user)->post(
            route('writer.prompts.store'),
            $this->payload(
                $work,
                [$section->id . ':21:40']
            )
        );

        $prompt = SavedPrompt::query()->firstOrFail();

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
            21,
            (int) $savedRange['start']
        );
        $this->assertSame(
            40,
            (int) $savedRange['end']
        );

        $this->assertNull(
            $prompt->work_story_section_id
        );

        $this->assertStringContainsString(
            '参照範囲：物語詳細21～40',
            $prompt->prompt_body
        );
        $this->assertStringNotContainsString(
            '出来事21',
            $prompt->prompt_body
        );
        $this->assertStringContainsString(
            '詳細：詳細21',
            $prompt->prompt_body
        );
        $this->assertStringNotContainsString(
            '出来事40',
            $prompt->prompt_body
        );
        $this->assertStringContainsString(
            '詳細：詳細40',
            $prompt->prompt_body
        );
        $this->assertStringNotContainsString(
            '詳細：詳細20',
            $prompt->prompt_body
        );
        $this->assertStringNotContainsString(
            '詳細：詳細41',
            $prompt->prompt_body
        );
    }

    public function test_invalid_or_other_work_range_is_rejected(): void
    {
        $user = $this->writer();
        [$work] = $this->sectionWithEvents(20);
        [$otherWork, $otherSection] =
            $this->sectionWithEvents(20);

        $this->actingAs($user)
            ->post(
                route('writer.prompts.store'),
                $this->payload(
                    $work,
                    [$otherSection->id . ':1:20']
                )
            )
            ->assertSessionHasErrors(
                'selected_story_event_ranges'
            );

        $this->actingAs($user)
            ->post(
                route('writer.prompts.store'),
                $this->payload(
                    $otherWork,
                    [$otherSection->id . ':2:20']
                )
            )
            ->assertSessionHasErrors(
                'selected_story_event_ranges'
            );
    }

    public function test_original_work_clears_ranges(): void
    {
        $user = $this->writer();
        [$work, $section] = $this->sectionWithEvents(20);

        $payload = $this->payload(
            $work,
            [$section->id . ':1:20']
        );
        $payload['work_ref'] = 'original';

        $this->actingAs($user)
            ->post(route('writer.prompts.store'), $payload)
            ->assertRedirect();

        $prompt = SavedPrompt::query()->firstOrFail();

        $this->assertSame(
            [],
            $prompt->selected_story_event_ranges
        );
        $this->assertNull($prompt->work_story_section_id);
    }

    public function test_legacy_whole_section_selection_still_works(): void
    {
        $user = $this->writer();
        [$work, $section] = $this->sectionWithEvents(2);

        $payload = $this->payload($work, []);
        $payload['work_story_section_id'] = $section->id;

        $prompt = app(
            \App\Services\SavedPromptService::class
        )->createForUser($user, $payload);

        $this->assertSame(
            $section->id,
            $prompt->work_story_section_id
        );
        $this->assertStringNotContainsString(
            '出来事1',
            $prompt->prompt_body
        );
        $this->assertStringContainsString(
            '詳細：詳細1',
            $prompt->prompt_body
        );
        $this->assertStringNotContainsString(
            '出来事2',
            $prompt->prompt_body
        );
        $this->assertStringContainsString(
            '詳細：詳細2',
            $prompt->prompt_body
        );
    }

    private function sectionWithEvents(
        int $count
    ): array {
        $work = Work::factory()->create([
            'status' => 'published',
        ]);

        $section = WorkStorySection::query()->create([
            'work_id' => $work->id,
            'section_type' => 'chapter',
            'title' => '範囲選択章',
            'status' => 'published',
        ]);

        foreach (range(1, $count) as $number) {
            $section->events()->create([
                'title' => '出来事' . $number,
                'summary' => '詳細' . $number,
                'sort_order' => $number,
            ]);
        }

        return [$work, $section];
    }

    private function payload(
        Work $work,
        array $ranges
    ): array {
        return [
            'title' => '物語詳細範囲テスト',
            'category' => 'scene',
            'purpose' => 'テスト',
            'work_ref' => 'work:' . $work->id,
            'selected_story_event_ranges' => $ranges,
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
                'description' => '小説執筆補助機能',
            ]
        );

        return User::factory()->create([
            'status' => 'active',
            'role_id' => $role->id,
            'is_super_admin' => false,
        ])->refresh();
    }
}
