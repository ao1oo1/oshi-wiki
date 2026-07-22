<?php

namespace Tests\Feature\Writer;

use Tests\TestCase;

class WriterCsvGuideImageLayoutTest extends TestCase
{
    public function test_csv_guide_image_is_responsive_and_contained(): void
    {
        $view = file_get_contents(
            resource_path('views/writer/csv/guide.blade.php')
        );

        $this->assertStringContainsString('max-h-[240px]', $view);
        $this->assertStringContainsString('sm:max-h-[320px]', $view);
        $this->assertStringContainsString('lg:max-h-[380px]', $view);
        $this->assertStringContainsString('max-w-full', $view);
        $this->assertStringContainsString('lg:max-w-[760px]', $view);
        $this->assertStringContainsString('object-contain', $view);
        $this->assertStringContainsString('loading="lazy"', $view);
    }

    public function test_csv_guide_image_asset_exists_and_is_valid_png(): void
    {
        $path = public_path(
            'images/writer/csv-guide-import-example.png'
        );

        $this->assertFileExists($path);
        $this->assertGreaterThan(1000, filesize($path));

        $imageInfo = getimagesize($path);

        $this->assertIsArray($imageInfo);
        $this->assertSame('image/png', $imageInfo['mime']);
        $this->assertSame(1566, $imageInfo[0]);
        $this->assertSame(534, $imageInfo[1]);
    }
}
