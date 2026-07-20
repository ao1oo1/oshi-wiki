<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class WorkCsvMonetizationCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_old_csv_updates_work_without_new_monetization_columns(): void
    {
        $user = $this->superAdmin();

        $work = Work::factory()->create([
            'title' => '更新前',
            'monetization_enabled' => false,
            'monetization_inheritance' => 'self_then_parent',
        ]);

        $file = UploadedFile::fake()->createWithContent(
            'works.csv',
            implode("\n", [
                'work_id,title,status',
                "{$work->id},更新後,draft",
            ])
        );

        $response = $this->actingAs($user)->post(
            route('admin.works.csv-import.store'),
            [
                'csv_file' => $file,
                'default_status' => 'draft',
            ]
        );

        $response->assertRedirect(
            route('admin.works.csv-import.create')
        );

        $this->assertDatabaseHas('works', [
            'id' => $work->id,
            'title' => '更新後',
            'monetization_enabled' => false,
            'monetization_inheritance' => 'self_then_parent',
        ]);
    }

    public function test_csv_imports_monetization_fields_and_media_types_json(): void
    {
        $user = $this->superAdmin();

        $work = Work::factory()->create([
            'title' => '収益化更新前',
        ]);

        $path = tempnam(sys_get_temp_dir(), 'work-monetization-csv-');
        $handle = fopen($path, 'wb');

        fputcsv(
            $handle,
            [
                'work_id',
                'title',
                'status',
                'media_types',
                'monetization_enabled',
                'monetization_inheritance',
                'isbn',
                'official_store_url',
            ],
            ',',
            '"',
            ''
        );

        fputcsv(
            $handle,
            [
                $work->id,
                '収益化更新後',
                'draft',
                '["manga","anime"]',
                '1',
                'self_then_parent',
                '9781234567890',
                'https://store.example.com/work',
            ],
            ',',
            '"',
            ''
        );

        fclose($handle);

        $response = $this->actingAs($user)->post(
            route('admin.works.csv-import.store'),
            [
                'csv_file' => new UploadedFile(
                    $path,
                    'works.csv',
                    'text/csv',
                    null,
                    true
                ),
                'default_status' => 'draft',
            ]
        );

        $response->assertRedirect(
            route('admin.works.csv-import.create')
        );

        $work->refresh();

        $this->assertSame('収益化更新後', $work->title);
        $this->assertSame(['manga', 'anime'], $work->media_types);
        $this->assertTrue($work->monetization_enabled);
        $this->assertSame(
            'self_then_parent',
            $work->monetization_inheritance
        );
        $this->assertSame('9781234567890', $work->isbn);
        $this->assertSame(
            'https://store.example.com/work',
            $work->official_store_url
        );
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $user->forceFill([
            'is_super_admin' => true,
        ])->save();

        return $user->refresh();
    }
}
