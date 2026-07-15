<?php

namespace Tests\Feature\Character;

use App\Models\Character;
use App\Models\User;
use App\Models\Work;
use App\Services\CharacterCsvImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CharacterCsvMultipleWorksTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_accepts_primary_work_id_and_work_ids(): void
    {
        $primary = Work::factory()->create();
        $chapter = Work::factory()->create();

        $path = tempnam(sys_get_temp_dir(), 'character-multi-work-');

        file_put_contents(
            $path,
            implode("\n", [
                'character_id,primary_work_id,work_ids,character_name,status',
                ',' . $primary->id . ',"' . $primary->id . ',' . $chapter->id . '",複数作品キャラ,draft',
            ])
        );

        $result = app(CharacterCsvImportService::class)
            ->import($path, null, 'draft');

        @unlink($path);

        $this->assertSame(1, $result['imported']);
        $this->assertSame([], $result['errors']);

        $character = Character::query()
            ->where('name', '複数作品キャラ')
            ->firstOrFail();

        $this->assertSame($primary->id, $character->work_id);
        $this->assertTrue($character->isLinkedToWork($primary->id));
        $this->assertTrue($character->isLinkedToWork($chapter->id));
    }

    public function test_legacy_work_id_csv_still_imports(): void
    {
        $work = Work::factory()->create();

        $path = tempnam(sys_get_temp_dir(), 'character-legacy-work-');

        file_put_contents(
            $path,
            implode("\n", [
                'character_id,work_id,character_name,status',
                ',' . $work->id . ',旧形式キャラ,draft',
            ])
        );

        $result = app(CharacterCsvImportService::class)
            ->import($path, null, 'draft');

        @unlink($path);

        $this->assertSame(1, $result['imported']);

        $character = Character::query()
            ->where('name', '旧形式キャラ')
            ->firstOrFail();

        $this->assertSame($work->id, $character->work_id);
        $this->assertTrue($character->isLinkedToWork($work->id));
    }

    public function test_export_contains_multiple_work_columns_and_values(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);

        $primary = Work::factory()->create(['title' => '基本作品']);
        $chapter = Work::factory()->create(['title' => '第1章']);

        $character = Character::factory()->create([
            'work_id' => $primary->id,
            'name' => 'CSV対象キャラ',
        ]);

        $character->linkedWorks()->syncWithoutDetaching([
            $chapter->id => [
                'is_primary' => false,
                'sort_order' => 0,
            ],
        ]);

        $response = $this->actingAs($user)->get(
            route('admin.characters.csv-export', [
                'character_id' => $character->id,
            ])
        );

        $response->assertOk();

        $csv = $response->getContent();

        $this->assertStringContainsString('primary_work_id', $csv);
        $this->assertStringContainsString('work_ids', $csv);
        $this->assertStringContainsString('primary_work_title', $csv);
        $this->assertStringContainsString('work_titles', $csv);
        $this->assertStringContainsString('基本作品', $csv);
        $this->assertStringContainsString('第1章', $csv);
    }

    public function test_export_work_filter_includes_additional_link(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);

        $primary = Work::factory()->create();
        $chapter = Work::factory()->create();

        $character = Character::factory()->create([
            'work_id' => $primary->id,
            'name' => '追加作品CSV対象',
        ]);

        $character->linkedWorks()->syncWithoutDetaching([
            $chapter->id => [
                'is_primary' => false,
                'sort_order' => 0,
            ],
        ]);

        $response = $this->actingAs($user)->get(
            route('admin.characters.csv-export', [
                'work_id' => $chapter->id,
            ])
        );

        $response
            ->assertOk()
            ->assertSee('追加作品CSV対象');
    }
}
