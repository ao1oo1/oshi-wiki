<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Work;
use App\Models\WorkStorySection;
use App\Services\WorkStorySectionCsvService;
use App\Services\WorkStorySectionEventCsvService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WorkStorySectionEventAppearingCharactersTest extends TestCase
{
    use RefreshDatabase;

    public function test_schema_and_form_support_free_character_text(): void
    {
        $this->assertTrue(
            Schema::hasColumn(
                'work_story_section_events',
                'appearing_characters'
            )
        );

        $work = Work::factory()->create();

        $this->actingAs($this->superAdmin())
            ->get(route(
                'admin.works.story-sections.create',
                $work
            ))
            ->assertOk()
            ->assertSee('登場キャラクター')
            ->assertSee(
                '監督生・グリム・エース・デュース・クロウリー'
            )
            ->assertSee(
                'キャラクターデータとの連携は行わない自由入力欄です。'
            );
    }

    public function test_form_saves_and_detail_displays_characters(): void
    {
        $work = Work::factory()->create();

        $this->actingAs($this->superAdmin())
            ->post(route(
                'admin.works.story-sections.store',
                $work
            ), [
                'section_type' => 'chapter',
                'title' => '登場人物テスト章',
                'spoiler_level' => 'none',
                'status' => 'draft',
                'events' => [[
                    'title' => '食堂の騒動',
                    'summary' => 'シャンデリアが壊れる。',
                    'appearing_characters' =>
                        '監督生・グリム・エース・デュース・クロウリー',
                    'sort_order' => 1,
                ]],
            ])
            ->assertRedirect();

        $section = WorkStorySection::query()
            ->where('title', '登場人物テスト章')
            ->firstOrFail();

        $this->assertDatabaseHas(
            'work_story_section_events',
            [
                'work_story_section_id' => $section->id,
                'appearing_characters' =>
                    '監督生・グリム・エース・デュース・クロウリー',
            ]
        );

        $this->actingAs($this->superAdmin())
            ->get(route(
                'admin.works.story-sections.show',
                [$work, $section]
            ))
            ->assertOk()
            ->assertSee(
                '監督生・グリム・エース・デュース・クロウリー'
            );
    }

    public function test_individual_csv_round_trip_supports_column(): void
    {
        $work = Work::factory()->create();
        $section = WorkStorySection::query()->create([
            'work_id' => $work->id,
            'section_type' => 'chapter',
            'title' => '個別CSV章',
            'status' => 'draft',
        ]);

        $csv = implode("\n", [
            'story_event_id,event_number,title,timing,summary,appearing_characters,location,outcome,spoiler_level,notes,sort_order',
            ',1,CSV出来事,冒頭,本文,監督生・グリム,学園,結果,minor,備考,1',
        ]);

        $this->actingAs($this->superAdmin())
            ->post(route(
                'admin.works.story-sections.events.csv.store',
                [$work, $section]
            ), [
                'csv_file' =>
                    UploadedFile::fake()->createWithContent(
                        'events.csv',
                        $csv
                    ),
            ])
            ->assertSessionHas('success');

        $event = $section->events()->firstOrFail();

        $this->assertSame(
            '監督生・グリム',
            $event->appearing_characters
        );

        $export = app(
            WorkStorySectionEventCsvService::class
        )->export($section);

        $this->assertStringContainsString(
            'appearing_characters',
            $export
        );
        $this->assertStringContainsString(
            '監督生・グリム',
            $export
        );
    }

    public function test_work_event_csv_supports_column(): void
    {
        $work = Work::factory()->create();
        $section = WorkStorySection::query()->create([
            'work_id' => $work->id,
            'section_type' => 'chapter',
            'title' => '作品CSV章',
            'status' => 'draft',
        ]);

        $section->events()->create([
            'title' => '出来事',
            'appearing_characters' => 'エース・デュース',
            'sort_order' => 1,
        ]);

        $csv = app(
            WorkStorySectionCsvService::class
        )->exportEvents($work);

        $this->assertStringContainsString(
            'appearing_characters',
            $csv
        );
        $this->assertStringContainsString(
            'エース・デュース',
            $csv
        );
    }

    public function test_old_csv_without_column_remains_compatible(): void
    {
        $work = Work::factory()->create();
        $section = WorkStorySection::query()->create([
            'work_id' => $work->id,
            'section_type' => 'chapter',
            'title' => '旧CSV章',
            'status' => 'draft',
        ]);

        $csv = implode("\n", [
            'story_event_id,title,summary,sort_order',
            ',旧形式出来事,旧形式本文,1',
        ]);

        $this->actingAs($this->superAdmin())
            ->post(route(
                'admin.works.story-sections.events.csv.store',
                [$work, $section]
            ), [
                'csv_file' =>
                    UploadedFile::fake()->createWithContent(
                        'legacy.csv',
                        $csv
                    ),
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas(
            'work_story_section_events',
            [
                'work_story_section_id' => $section->id,
                'title' => '旧形式出来事',
                'appearing_characters' => null,
            ]
        );
    }

    private function superAdmin(): User
    {
        return User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);
    }
}
