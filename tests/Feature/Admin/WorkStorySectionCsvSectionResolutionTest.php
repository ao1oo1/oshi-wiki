<?php

namespace Tests\Feature\Admin;

use App\Models\Character;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkStorySection;
use App\Services\WorkStorySectionCsvService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkStorySectionCsvSectionResolutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_character_csv_resolves_section_by_title_when_id_is_blank(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $work = Work::factory()->create();

        $section = WorkStorySection::query()->create([
            'work_id' => $work->id,
            'section_type' => 'chapter',
            'title' => '第1章',
            'sort_order' => 1,
            'status' => 'draft',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $character = Character::factory()->create();

        $character->linkedWorks()->attach($work->id);

        $csv = implode("\n", [
            implode(',', WorkStorySectionCsvService::CHARACTER_HEADERS),
            implode(',', [
                '',
                '',
                $work->id,
                '第1章',
                $character->id,
                $character->name,
                'main',
                '',
                '',
                '',
                '',
                '',
                '',
                '1',
                '',
                '1',
            ]),
        ]);

        $path = tempnam(sys_get_temp_dir(), 'story-character-');
        file_put_contents($path, $csv);

        $result = app(WorkStorySectionCsvService::class)
            ->import('characters', $path, $work);

        unlink($path);

        $this->assertSame([], $result['errors']);
        $this->assertSame(1, $result['created']);

        $this->assertDatabaseHas(
            'character_work_story_section',
            [
                'work_story_section_id' => $section->id,
                'character_id' => $character->id,
            ]
        );
    }

    public function test_event_csv_resolves_section_by_title_when_id_is_invalid(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $work = Work::factory()->create();

        $section = WorkStorySection::query()->create([
            'work_id' => $work->id,
            'section_type' => 'chapter',
            'title' => '第2章',
            'sort_order' => 2,
            'status' => 'draft',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $csv = implode("\n", [
            implode(',', WorkStorySectionCsvService::EVENT_HEADERS),
            implode(',', [
                '',
                '999999',
                $work->id,
                '第2章',
                '1',
                '出来事',
                '',
                '詳細',
                '',
                '',
                '',
                'none',
                '',
                '1',
            ]),
        ]);

        $path = tempnam(sys_get_temp_dir(), 'story-event-');
        file_put_contents($path, $csv);

        $result = app(WorkStorySectionCsvService::class)
            ->import('events', $path, $work);

        unlink($path);

        $this->assertSame([], $result['errors']);
        $this->assertSame(1, $result['created']);

        $this->assertDatabaseHas(
            'work_story_section_events',
            [
                'work_story_section_id' => $section->id,
                'title' => '出来事',
            ]
        );
    }
}
