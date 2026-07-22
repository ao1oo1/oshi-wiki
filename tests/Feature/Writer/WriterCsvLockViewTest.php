<?php

namespace Tests\Feature\Writer;

use Tests\TestCase;

class WriterCsvLockViewTest extends TestCase
{
    public function test_csv_view_contains_lock_overlay_design(): void
    {
        $view = file_get_contents(
            resource_path('views/writer/csv/index.blade.php')
        );

        $this->assertStringContainsString(
            'CSVインポート/エクスポートはPlus限定です',
            $view
        );

        $this->assertStringContainsString(
            'CSV機能はOshi-Wiki Plus限定です',
            $view
        );

        $this->assertStringContainsString(
            'bg-[#2D3748]/45',
            $view
        );

        $this->assertStringContainsString(
            '<rect x="5" y="11" width="14" height="10" rx="2">',
            $view
        );
    }
}
