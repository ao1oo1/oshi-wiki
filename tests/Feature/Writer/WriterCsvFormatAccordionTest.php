<?php

namespace Tests\Feature\Writer;

use App\Services\WriterCsvService;
use Tests\TestCase;

class WriterCsvFormatAccordionTest extends TestCase
{
    public function test_writer_csv_index_includes_format_accordion_after_import_form(): void
    {
        $view = file_get_contents(
            resource_path('views/writer/csv/index.blade.php')
        );

        $buttonPosition = strpos($view, 'CSVから新規登録');
        $accordionPosition = strpos(
            $view,
            "@include('writer.csv._format_accordion'"
        );

        $this->assertNotFalse($buttonPosition);
        $this->assertNotFalse($accordionPosition);
        $this->assertGreaterThan($buttonPosition, $accordionPosition);
    }

    public function test_format_accordion_uses_actual_service_headers(): void
    {
        $view = file_get_contents(
            resource_path('views/writer/csv/_format_accordion.blade.php')
        );

        $this->assertStringContainsString(
            'WriterCsvService::class',
            $view
        );
        $this->assertStringContainsString(
            '->headers($type)',
            $view
        );
        $this->assertStringContainsString(
            '<details',
            $view
        );
        $this->assertStringContainsString(
            'CSVの形式',
            $view
        );
        $this->assertStringContainsString(
            '列名',
            $view
        );
        $this->assertStringContainsString(
            '説明',
            $view
        );
    }

    public function test_every_writer_csv_type_has_headers(): void
    {
        $service = app(WriterCsvService::class);

        foreach (WriterCsvService::TYPES as $type => $label) {
            $headers = $service->headers($type);

            $this->assertNotEmpty(
                $headers,
                sprintf('%sのCSV列が空です。', $label)
            );
        }
    }
}
