<?php

namespace Tests\Feature\Admin;

use App\Models\Character;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkStorySection;
use App\Services\WorkStorySectionCsvService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkStorySectionCharacterNameResolutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_character_csv_resolves_registered_character_by_name_when_id_is_blank(): void
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

        $character = Character::factory()->create([
            'name' => '登録済みキャラクター',
        ]);

        $character->linkedWorks()->attach($work->id);

        $path = $this->makeCharacterCsv([
            '',
            $section->id,
            $work->id,
            $section->title,
            '',
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
        ]);

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

    public function test_character_csv_falls_back_to_name_when_id_does_not_exist(): void
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

        $character = Character::factory()->create([
            'name' => '名前検索対象',
        ]);

        $character->linkedWorks()->attach($work->id);

        $path = $this->makeCharacterCsv([
            '',
            $section->id,
            $work->id,
            $section->title,
            '999999',
            $character->name,
            'main',
            '',
            '',
            '',
            '',
            '',
            '',
            '0',
            '',
            '1',
        ]);

        $result = app(WorkStorySectionCsvService::class)
            ->import('characters', $path, $work);

        unlink($path);

        $this->assertSame([], $result['errors']);
        $this->assertSame(1, $result['created']);
    }

    public function test_unregistered_character_returns_clear_name_error(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $work = Work::factory()->create();

        $section = WorkStorySection::query()->create([
            'work_id' => $work->id,
            'section_type' => 'chapter',
            'title' => '第3章',
            'sort_order' => 3,
            'status' => 'draft',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $path = $this->makeCharacterCsv([
            '',
            $section->id,
            $work->id,
            $section->title,
            '',
            '桜木 清右衛門',
            'main',
            '',
            '',
            '',
            '',
            '',
            '',
            '0',
            '',
            '1',
        ]);

        $result = app(WorkStorySectionCsvService::class)
            ->import('characters', $path, $work);

        unlink($path);

        $this->assertCount(1, $result['errors']);

        $this->assertStringContainsString(
            '2行目：',
            $result['errors'][0]
        );

        $this->assertStringContainsString(
            'キャラクター「桜木 清右衛門」',
            $result['errors'][0]
        );

        $this->assertStringContainsString(
            "作品ID {$work->id} に登録されていません。",
            $result['errors'][0]
        );

        $this->assertStringNotContainsString(
            'No query results for model',
            $result['errors'][0]
        );
    }

    private function makeCharacterCsv(array $row): string
    {
        $path = tempnam(
            sys_get_temp_dir(),
            'story-character-name-'
        );

        $handle = fopen($path, 'wb');

        fputcsv(
            $handle,
            WorkStorySectionCsvService::CHARACTER_HEADERS,
            ',',
            '"',
            ''
        );

        fputcsv(
            $handle,
            $row,
            ',',
            '"',
            ''
        );

        fclose($handle);

        return $path;
    }
}
