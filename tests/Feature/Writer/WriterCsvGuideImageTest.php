<?php

namespace Tests\Feature\Writer;

use Tests\TestCase;

class WriterCsvGuideImageTest extends TestCase
{
    public function test_writer_csv_guide_contains_import_example_image(): void
    {
        $view = file_get_contents(
            resource_path('views/writer/csv/guide.blade.php')
        );

        $this->assertStringContainsString(
            "asset('images/writer/csv-guide-import-example.png')",
            $view
        );

        $this->assertStringContainsString(
            'CSVサンプルの入力例',
            $view
        );

        $this->assertStringContainsString(
            '1行目の列名は変更せず、2行目以降へキャラクター情報を入力してください。',
            $view
        );
    }

    public function test_import_section_keeps_example_after_steps(): void
    {
        $view = file_get_contents(
            resource_path('views/writer/csv/guide.blade.php')
        );

        $stepsPosition = strpos($view, '5. ファイルを選び、新規登録する');
        $imagePosition = strpos($view, 'csv-guide-import-example.png');

        $this->assertNotFalse($stepsPosition);
        $this->assertNotFalse($imagePosition);
        $this->assertGreaterThan($stepsPosition, $imagePosition);
    }

    public function test_public_example_image_exists(): void
    {
        $this->assertFileExists(
            public_path('images/writer/csv-guide-import-example.png')
        );
    }
}
