<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Work;
use App\Models\WorkStorySection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class WorkStorySectionIndividualEventCsvTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_open_individual_csv_ui(): void
    {
        [$work, $section] = $this->workAndSection();

        $this->actingAs($this->superAdmin())
            ->get(route(
                'admin.works.story-sections.events.csv.create',
                [$work, $section]
            ))
            ->assertOk()
            ->assertSee('CSVファイルを取り込む')
            ->assertSee('サンプルCSVをダウンロード')
            ->assertSee('現在の物語詳細CSVを出力');
    }

    public function test_section_detail_has_individual_csv_actions(): void
    {
        [$work, $section] = $this->workAndSection();

        $this->actingAs($this->superAdmin())
            ->get(route(
                'admin.works.story-sections.show',
                [$work, $section]
            ))
            ->assertOk()
            ->assertSee('CSVで追加・更新')
            ->assertSee('この章をCSV出力');
    }

    public function test_sample_csv_has_bom_and_simple_headers(): void
    {
        $response = $this->actingAs(
            $this->superAdmin()
        )->get(route(
            'admin.story-section-events.csv.sample'
        ));

        $response->assertOk();

        $csv = $response->getContent();

        $this->assertStringStartsWith(
            "\xEF\xBB\xBF",
            $csv
        );
        $this->assertStringContainsString(
            'story_event_id,event_number,title',
            $csv
        );
        $this->assertStringNotContainsString(
            'story_section_id',
            $csv
        );
        $this->assertStringNotContainsString(
            'work_id',
            $csv
        );
    }

    public function test_csv_creates_and_updates_events_in_section(): void
    {
        [$work, $section] = $this->workAndSection();

        $createCsv = implode("\n", [
            'story_event_id,event_number,title,timing,summary,location,outcome,spoiler_level,notes,sort_order',
            ',1,CSV新規出来事,章冒頭,新規本文,学園,到着した,minor,補足,1',
        ]);

        $this->actingAs($this->superAdmin())
            ->post(route(
                'admin.works.story-sections.events.csv.store',
                [$work, $section]
            ), [
                'csv_file' =>
                    UploadedFile::fake()
                        ->createWithContent(
                            'events.csv',
                            $createCsv
                        ),
            ])
            ->assertRedirect(route(
                'admin.works.story-sections.events.csv.create',
                [$work, $section]
            ))
            ->assertSessionHas('success');

        $event = $section->events()
            ->where('title', 'CSV新規出来事')
            ->firstOrFail();

        $updateCsv = implode("\n", [
            'story_event_id,event_number,title,timing,summary,location,outcome,spoiler_level,notes,sort_order',
            "{$event->id},2,CSV更新出来事,中盤,更新本文,寮,解決した,major,更新補足,5",
        ]);

        $this->actingAs($this->superAdmin())
            ->post(route(
                'admin.works.story-sections.events.csv.store',
                [$work, $section]
            ), [
                'csv_file' =>
                    UploadedFile::fake()
                        ->createWithContent(
                            'events-update.csv',
                            $updateCsv
                        ),
            ])
            ->assertSessionHas('success');

        $event->refresh();

        $this->assertSame(
            'CSV更新出来事',
            $event->title
        );
        $this->assertSame('major', $event->spoiler_level);
        $this->assertSame(5, $event->sort_order);
    }

    public function test_event_id_from_other_section_is_rejected(): void
    {
        [$work, $section] = $this->workAndSection();

        $otherSection = WorkStorySection::query()->create([
            'work_id' => $work->id,
            'section_type' => 'chapter',
            'title' => '別章',
            'status' => 'draft',
        ]);

        $otherEvent = $otherSection->events()->create([
            'title' => '別章の出来事',
            'sort_order' => 1,
        ]);

        $csv = implode("\n", [
            'story_event_id,title',
            "{$otherEvent->id},不正更新",
        ]);

        $this->actingAs($this->superAdmin())
            ->post(route(
                'admin.works.story-sections.events.csv.store',
                [$work, $section]
            ), [
                'csv_file' =>
                    UploadedFile::fake()
                        ->createWithContent(
                            'invalid.csv',
                            $csv
                        ),
            ])
            ->assertSessionHas('csv_errors');

        $this->assertSame(
            '別章の出来事',
            $otherEvent->refresh()->title
        );
    }

    public function test_import_rejects_more_than_five_hundred_events(): void
    {
        [$work, $section] = $this->workAndSection();

        for ($i = 1; $i <= 500; $i++) {
            $section->events()->create([
                'title' => "既存{$i}",
                'sort_order' => $i,
            ]);
        }

        $csv = implode("\n", [
            'story_event_id,title',
            ',501件目',
        ]);

        $this->actingAs($this->superAdmin())
            ->post(route(
                'admin.works.story-sections.events.csv.store',
                [$work, $section]
            ), [
                'csv_file' =>
                    UploadedFile::fake()
                        ->createWithContent(
                            'over-limit.csv',
                            $csv
                        ),
            ])
            ->assertSessionHasErrors('csv_file');

        $this->assertSame(
            500,
            $section->events()->count()
        );
    }

    public function test_non_super_admin_cannot_use_csv_routes(): void
    {
        [$work, $section] = $this->workAndSection();

        $user = User::factory()->create([
            'is_super_admin' => false,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route(
                'admin.works.story-sections.events.csv.create',
                [$work, $section]
            ))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route(
                'admin.works.story-sections.events.csv.store',
                [$work, $section]
            ), [
                'csv_file' =>
                    UploadedFile::fake()
                        ->createWithContent(
                            'events.csv',
                            "title\n不正登録"
                        ),
            ])
            ->assertForbidden();
    }

    public function test_section_from_other_work_returns_not_found(): void
    {
        [$work] = $this->workAndSection();

        $otherWork = Work::factory()->create();

        $otherSection = WorkStorySection::query()->create([
            'work_id' => $otherWork->id,
            'section_type' => 'chapter',
            'title' => '別作品章',
            'status' => 'draft',
        ]);

        $this->actingAs($this->superAdmin())
            ->get(route(
                'admin.works.story-sections.events.csv.create',
                [$work, $otherSection]
            ))
            ->assertNotFound();
    }

    private function workAndSection(): array
    {
        $work = Work::factory()->create([
            'title' => '個別CSV作品',
        ]);

        $section = WorkStorySection::query()->create([
            'work_id' => $work->id,
            'section_type' => 'chapter',
            'title' => '個別CSV第1章',
            'status' => 'draft',
        ]);

        return [$work, $section];
    }

    private function superAdmin(): User
    {
        return User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);
    }
}
