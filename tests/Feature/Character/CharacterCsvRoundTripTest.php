<?php

namespace Tests\Feature\Character;

use App\Models\Character;
use App\Models\Tag;
use App\Models\Work;
use App\Services\CharacterCsvImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CharacterCsvRoundTripTest extends TestCase
{
    use RefreshDatabase;

    public function test_imports_new_fields_japanese_enums_and_tags(): void
    {
        $work = Work::factory()->create();
        $tag = Tag::query()->create([
            'name' => '主人公',
            'slug' => 'main-character',
            'type' => 'character',
            'status' => 'published',
        ]);

        $csv = implode("\n", [
            'work_id,name,real_name,school_grade_class,basic_tone,source_type,source_reliability,source_checked_at,spoiler_level,tag_names,status',
            "{$work->id},テスト人物,本名,1年A組,丁寧,公式,高,2026-07-14,軽度,主人公,published",
        ]);

        $path = tempnam(sys_get_temp_dir(), 'character-csv-');
        file_put_contents($path, "\xEF\xBB\xBF" . $csv);

        $result = app(CharacterCsvImportService::class)->import(
            $path,
            null,
            'draft'
        );

        unlink($path);

        $this->assertSame(1, $result['created']);
        $this->assertSame([], $result['errors']);

        $character = Character::query()->firstOrFail();

        $this->assertSame('本名', $character->real_name);
        $this->assertSame('1年A組', $character->school_grade_class);
        $this->assertSame('丁寧', $character->basic_tone);
        $this->assertSame('official', $character->source_type);
        $this->assertSame('high', $character->source_reliability);
        $this->assertSame('minor', $character->spoiler_level);
        $this->assertTrue($character->tags()->whereKey($tag->id)->exists());
    }

    public function test_updates_existing_character_and_accepts_legacy_headers(): void
    {
        $work = Work::factory()->create();

        $character = Character::factory()->create([
            'work_id' => $work->id,
            'name' => '更新前',
            'status' => 'draft',
        ]);

        $csv = implode("\n", [
            'character_id,work_id,character_name,grade_class,tone,tone_examples,status',
            "{$character->id},{$work->id},更新後,2年B組,砕けた口調,「了解」,published",
        ]);

        $path = tempnam(sys_get_temp_dir(), 'character-csv-');
        file_put_contents($path, $csv);

        $result = app(CharacterCsvImportService::class)->import(
            $path,
            null,
            'draft'
        );

        unlink($path);

        $this->assertSame(1, $result['updated']);
        $this->assertSame(0, $result['created']);

        $character->refresh();

        $this->assertSame('更新後', $character->name);
        $this->assertSame('2年B組', $character->school_grade_class);
        $this->assertSame('砕けた口調', $character->basic_tone);
        $this->assertSame('「了解」', $character->short_quote_examples);
        $this->assertSame('published', $character->status);
    }

    public function test_sample_csv_has_valid_utf8_bom_and_new_headers(): void
    {
        $controller = app(
            \App\Http\Controllers\Admin\CharacterCsvImportController::class
        );

        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('sampleCsv');
        $csv = $method->invoke($controller);

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);

        $csv = substr($csv, 3);
        $handle = fopen('php://temp', 'r+b');
        fwrite($handle, $csv);
        rewind($handle);

        $headers = fgetcsv($handle, null, ',', '"', '');

        $this->assertSame('character_id', $headers[0]);
        $this->assertContains('source_type', $headers);
        $this->assertContains('spoiler_level', $headers);
        $this->assertContains('tag_ids', $headers);
        $this->assertContains('tag_names', $headers);
    }

    public function test_export_can_limit_csv_to_one_character(): void
    {
        $work = Work::factory()->create();

        $target = Character::factory()->create([
            'work_id' => $work->id,
            'name' => '個別出力対象',
        ]);

        Character::factory()->create([
            'work_id' => $work->id,
            'name' => '出力対象外',
        ]);

        $controller = app(
            \App\Http\Controllers\Admin\CharacterCsvExportController::class
        );

        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('buildCsv');

        $request = \Illuminate\Http\Request::create(
            '/admin/characters/export/csv',
            'GET',
            ['character_id' => $target->id]
        );

        $csv = $method->invoke($controller, $request);
        $csv = preg_replace('/^\xEF\xBB\xBF/', '', $csv) ?? $csv;

        $handle = fopen('php://temp', 'r+b');
        fwrite($handle, $csv);
        rewind($handle);

        $headers = fgetcsv($handle, null, ',', '"', '');
        $row = fgetcsv($handle, null, ',', '"', '');
        $extraRow = fgetcsv($handle, null, ',', '"', '');

        $data = array_combine($headers, $row);

        $this->assertSame((string) $target->id, $data['character_id']);
        $this->assertSame('個別出力対象', $data['character_name']);
        $this->assertFalse($extraRow);
    }

    public function test_character_detail_view_shows_id_and_individual_export_link(): void
    {
        $work = Work::factory()->create();

        $character = Character::factory()->create([
            'work_id' => $work->id,
            'name' => '詳細表示対象',
        ]);

        $character->load([
            'work',
            'tags',
            'outgoingRelationships.toCharacter',
            'incomingRelationships.fromCharacter',
        ]);

        $view = $this->view(
            'admin.characters.show',
            ['character' => $character]
        );

        $view
            ->assertSee('キャラクターID：' . $character->id)
            ->assertSee(
                route(
                    'admin.characters.csv-export',
                    ['character_id' => $character->id]
                ),
                false
            )
            ->assertSee('このキャラクターをCSVエクスポート');
    }

}
