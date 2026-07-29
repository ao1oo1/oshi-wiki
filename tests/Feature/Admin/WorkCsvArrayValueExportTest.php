<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkCsvArrayValueExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_work_export_serializes_media_types_as_json(): void
    {
        Work::factory()->create([
            'title' => '公開CSV配列テスト',
            'status' => 'published',
            'media_types' => [
                'manga',
                'anime',
            ],
        ]);

        $response = $this->actingAs($this->superAdmin())
            ->get(route('admin.works.csv-export', [
                'status' => 'published',
            ]));

        $response->assertOk();

        $csv = $response->getContent();

        $this->assertStringContainsString(
            'media_types',
            $csv
        );

        $lines = preg_split(
            '/\r\n|\r|\n/',
            trim($csv)
        );

        $this->assertIsArray($lines);
        $this->assertGreaterThanOrEqual(2, count($lines));

        $headers = str_getcsv(
            ltrim($lines[0], "\xEF\xBB\xBF"),
            ',',
            '"',
            ''
        );
        $row = str_getcsv(
            $lines[1],
            ',',
            '"',
            ''
        );

        $mediaTypesIndex = array_search(
            'media_types',
            $headers,
            true
        );

        $this->assertNotFalse($mediaTypesIndex);
        $this->assertSame(
            '["manga","anime"]',
            $row[$mediaTypesIndex]
        );
    }

    public function test_export_does_not_raise_array_to_string_warning(): void
    {
        Work::factory()->create([
            'status' => 'published',
            'media_types' => [
                'game',
                'app',
            ],
        ]);

        $previous = set_error_handler(
            static function (
                int $severity,
                string $message
            ): never {
                throw new \ErrorException(
                    $message,
                    0,
                    $severity
                );
            }
        );

        try {
            $response = $this->actingAs($this->superAdmin())
                ->get(route('admin.works.csv-export', [
                    'status' => 'published',
                ]));

            $response->assertOk();
        } finally {
            restore_error_handler();
        }

        $this->assertNotNull($previous);
    }

    private function superAdmin(): User
    {
        return User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);
    }
}
