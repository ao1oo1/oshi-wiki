<?php

namespace Tests\Feature\Admin;

use App\Models\Character;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkStorySection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class WorkStorySectionPhase4ATest extends TestCase
{
    use RefreshDatabase;

    public function test_three_csv_samples_have_bom_and_headers(): void
    {
        $user = $this->superAdmin();

        foreach ([
            'sections' => 'story_section_id',
            'events' => 'story_event_id',
            'characters' =>
                'story_section_character_id',
        ] as $type => $header) {
            $response = $this->actingAs($user)->get(
                route(
                    'admin.works.story-sections.csv.sample',
                    $type
                )
            );

            $response->assertOk();
            $csv = $response->getContent();

            $this->assertStringStartsWith(
                "\xEF\xBB\xBF",
                $csv
            );
            $this->assertStringContainsString(
                $header,
                $csv
            );
        }
    }

    public function test_section_csv_can_create_and_update(): void
    {
        $user = $this->superAdmin();
        $work = Work::factory()->create();

        $csv = implode("\n", [
            'story_section_id,work_id,title,section_type,status,sort_order',
            ",{$work->id},CSV第1章,chapter,draft,1",
        ]);

        $this->actingAs($user)
            ->post(
                route(
                    'admin.works.story-sections.csv.import',
                    $work
                ),
                [
                    'type' => 'sections',
                    'csv_file' =>
                        UploadedFile::fake()
                            ->createWithContent(
                                'sections.csv',
                                $csv
                            ),
                    'default_status' => 'draft',
                ]
            )
            ->assertRedirect();

        $section = WorkStorySection::query()
            ->where('title', 'CSV第1章')
            ->firstOrFail();

        $update = implode("\n", [
            'story_section_id,work_id,title,section_type,status,sort_order',
            "{$section->id},{$work->id},CSV更新章,chapter,published,2",
        ]);

        $this->actingAs($user)
            ->post(
                route(
                    'admin.works.story-sections.csv.import',
                    $work
                ),
                [
                    'type' => 'sections',
                    'csv_file' =>
                        UploadedFile::fake()
                            ->createWithContent(
                                'sections-update.csv',
                                $update
                            ),
                    'default_status' => 'draft',
                ]
            );

        $section->refresh();

        $this->assertSame('CSV更新章', $section->title);
        $this->assertSame(
            'published',
            $section->status
        );
    }

    public function test_event_and_character_csv_import(): void
    {
        $user = $this->superAdmin();
        $work = Work::factory()->create();
        $section = WorkStorySection::query()->create([
            'work_id' => $work->id,
            'section_type' => 'chapter',
            'title' => '対象章',
            'status' => 'draft',
        ]);

        $character = Character::factory()->create([
            'work_id' => $work->id,
            'name' => 'CSVキャラ',
        ]);

        $work->linkedCharacters()->sync([
            $character->id => [
                'is_primary' => true,
                'sort_order' => 0,
            ],
        ]);

        $eventCsv = implode("\n", [
            'story_event_id,story_section_id,title,summary,sort_order',
            ",{$section->id},CSV出来事,出来事本文,1",
        ]);

        $this->actingAs($user)->post(
            route(
                'admin.works.story-sections.csv.import',
                $work
            ),
            [
                'type' => 'events',
                'csv_file' =>
                    UploadedFile::fake()
                        ->createWithContent(
                            'events.csv',
                            $eventCsv
                        ),
            ]
        );

        $characterCsv = implode("\n", [
            'story_section_character_id,story_section_id,character_id,age_at_section,school_grade_at_section,sort_order',
            ",{$section->id},{$character->id},16歳,1年,1",
        ]);

        $this->actingAs($user)->post(
            route(
                'admin.works.story-sections.csv.import',
                $work
            ),
            [
                'type' => 'characters',
                'csv_file' =>
                    UploadedFile::fake()
                        ->createWithContent(
                            'characters.csv',
                            $characterCsv
                        ),
            ]
        );

        $this->assertDatabaseHas(
            'work_story_section_events',
            [
                'work_story_section_id' => $section->id,
                'title' => 'CSV出来事',
            ]
        );

        $this->assertDatabaseHas(
            'character_work_story_section',
            [
                'work_story_section_id' => $section->id,
                'character_id' => $character->id,
                'age_at_section' => '16歳',
            ]
        );
    }

    public function test_text_import_creates_section_events_and_characters(): void
    {
        $user = $this->superAdmin();
        $work = Work::factory()->create();

        $character = Character::factory()->create([
            'work_id' => $work->id,
            'name' => 'テキストキャラ',
        ]);

        $work->linkedCharacters()->sync([
            $character->id => [
                'is_primary' => true,
                'sort_order' => 0,
            ],
        ]);

        $text = <<<TEXT
■ テキスト第1章
種別：chapter
概要：章の概要

物語詳細：
1. 最初の出来事
詳細：出来事本文

登場キャラクター：
・テキストキャラ
年齢：16歳
学年：1年
TEXT;

        $this->actingAs($user)
            ->post(
                route(
                    'admin.works.story-sections.text-import.store',
                    $work
                ),
                [
                    'raw_text' => $text,
                    'status' => 'draft',
                ]
            )
            ->assertRedirect();

        $section = WorkStorySection::query()
            ->where('title', 'テキスト第1章')
            ->firstOrFail();

        $this->assertDatabaseHas(
            'work_story_section_events',
            [
                'work_story_section_id' => $section->id,
                'title' => '最初の出来事',
            ]
        );

        $this->assertDatabaseHas(
            'character_work_story_section',
            [
                'work_story_section_id' => $section->id,
                'character_id' => $character->id,
            ]
        );
    }

    public function test_bulk_action_changes_only_sections_in_work(): void
    {
        $user = $this->superAdmin();
        $work = Work::factory()->create();
        $otherWork = Work::factory()->create();

        $section = WorkStorySection::query()->create([
            'work_id' => $work->id,
            'section_type' => 'chapter',
            'title' => '対象章',
            'status' => 'draft',
        ]);

        $other = WorkStorySection::query()->create([
            'work_id' => $otherWork->id,
            'section_type' => 'chapter',
            'title' => '別作品章',
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->post(
                route(
                    'admin.works.story-sections.bulk-action',
                    $work
                ),
                [
                    'section_ids' => [$section->id],
                    'bulk_action' => 'publish',
                ]
            )
            ->assertRedirect();

        $this->assertSame(
            'published',
            $section->refresh()->status
        );
        $this->assertSame(
            'draft',
            $other->refresh()->status
        );
    }

    public function test_index_contains_phase4a_actions(): void
    {
        $user = $this->superAdmin();
        $work = Work::factory()->create();

        $this->actingAs($user)
            ->get(
                route(
                    'admin.works.story-sections.index',
                    $work
                )
            )
            ->assertOk()
            ->assertSee('テキスト取り込み')
            ->assertSee('CSV取り込み・出力');
    }

    private function superAdmin(): User
    {
        return User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);
    }
}
