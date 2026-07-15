<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WorkCsvWorldbuildingRoundTripTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_contains_v4_columns_and_relation_json(): void
    {
        $user = $this->createSuperAdmin();
        $work = Work::factory()->create();

        $response = $this->actingAs($user)->get(
            route('admin.works.csv-export', ['work_id' => $work->id])
        );

        $response->assertOk();

        $csv = $response->getContent();
        $this->assertStringContainsString('canon_events_json', $csv);
        $this->assertStringContainsString('term_usages_json', $csv);

        foreach ($this->worldbuildingColumns() as $column) {
            $this->assertStringContainsString($column, $csv);
        }
    }

    public function test_sample_csv_has_utf8_bom_and_json_columns(): void
    {
        $user = $this->createSuperAdmin();

        $response = $this->actingAs($user)->get(
            route('admin.works.csv-import.sample')
        );

        $response->assertOk();

        $content = $response->getContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('canon_events_json', $content);
        $this->assertStringContainsString('term_usages_json', $content);
    }

    public function test_import_updates_v4_text_columns_without_removing_title(): void
    {
        $user = $this->createSuperAdmin();
        $work = Work::factory()->create(['title' => '更新前']);

        $columns = $this->worldbuildingColumns();
        $targetColumn = $columns[0] ?? null;

        $headers = ['work_id', 'title', 'status', 'canon_events_json', 'term_usages_json'];

        if ($targetColumn) {
            $headers[] = $targetColumn;
        }

        $row = [
            $work->id,
            '更新後',
            'draft',
            '[]',
            '[]',
        ];

        if ($targetColumn) {
            $row[] = 'CSVから更新した世界設定';
        }

        $path = tempnam(sys_get_temp_dir(), 'work-csv-');
        $handle = fopen($path, 'wb');
        fputcsv($handle, $headers, ',', '"', '');
        fputcsv($handle, $row, ',', '"', '');
        fclose($handle);

        $response = $this->actingAs($user)->post(
            route('admin.works.csv-import.store'),
            [
                'default_status' => 'draft',
                'csv_file' => new \Illuminate\Http\UploadedFile(
                    $path,
                    'works.csv',
                    'text/csv',
                    null,
                    true
                ),
            ]
        );

        $response->assertRedirect(route('admin.works.csv-import.create'));

        $work->refresh();
        $this->assertSame('更新後', $work->title);

        if ($targetColumn) {
            $this->assertSame('CSVから更新した世界設定', $work->{$targetColumn});
        }
    }


    private function createSuperAdmin(): User
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $user->forceFill([
            'is_super_admin' => true,
        ])->save();

        return $user->refresh();
    }

    private function worldbuildingColumns(): array
    {
        return array_values(array_diff(
            Schema::getColumnListing('works'),
            [
                'id', 'title', 'title_kana', 'slug', 'genre',
                'original_media', 'official_url', 'guideline_url',
                'description', 'status', 'review_status',
                'created_by', 'updated_by', 'published_at',
                'created_at', 'updated_at', 'deleted_at',
                'helpful_count', 'contributor_application_id',
            ]
        ));
    }
}
