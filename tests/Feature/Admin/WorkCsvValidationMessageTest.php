<?php

namespace Tests\Feature\Admin;

use App\Services\WorkCsvImportService;
use ReflectionMethod;
use Tests\TestCase;

class WorkCsvValidationMessageTest extends TestCase
{
    public function test_media_types_accept_japanese_aliases(): void
    {
        $service = app(WorkCsvImportService::class);

        $method = new ReflectionMethod(
            WorkCsvImportService::class,
            'normalizeMediaTypes'
        );
        $method->setAccessible(true);

        $this->assertSame(
            [
                'anime',
                'manga',
                'game',
                'novel',
                'goods',
                'app',
                'other',
            ],
            $method->invoke(
                $service,
                'アニメ、漫画、ゲーム、小説、グッズ、アプリ、その他'
            )
        );
    }

    public function test_media_types_accept_json_export_format(): void
    {
        $service = app(WorkCsvImportService::class);

        $method = new ReflectionMethod(
            WorkCsvImportService::class,
            'normalizeMediaTypes'
        );
        $method->setAccessible(true);

        $this->assertSame(
            ['anime', 'manga'],
            $method->invoke(
                $service,
                '["anime","manga"]'
            )
        );
    }

    public function test_status_accepts_japanese_aliases(): void
    {
        $service = app(WorkCsvImportService::class);

        $method = new ReflectionMethod(
            WorkCsvImportService::class,
            'normalizeStatus'
        );
        $method->setAccessible(true);

        $this->assertSame(
            'published',
            $method->invoke($service, '公開')
        );

        $this->assertSame(
            'draft',
            $method->invoke($service, '下書き')
        );

        $this->assertSame(
            'private',
            $method->invoke($service, '非公開')
        );
    }

    public function test_validation_messages_are_specific_japanese_messages(): void
    {
        $service = app(WorkCsvImportService::class);

        $method = new ReflectionMethod(
            WorkCsvImportService::class,
            'validationMessages'
        );
        $method->setAccessible(true);

        $messages = $method->invoke($service);

        $this->assertArrayHasKey(
            'media_types.*.in',
            $messages
        );
        $this->assertStringContainsString(
            '媒体種別',
            $messages['media_types.*.in']
        );
        $this->assertStringContainsString(
            ':input',
            $messages['media_types.*.in']
        );

        $this->assertArrayHasKey(
            'monetization_inheritance.in',
            $messages
        );
        $this->assertArrayHasKey(
            'status.in',
            $messages
        );
    }

    public function test_service_no_longer_uses_empty_validation_messages(): void
    {
        $source = file_get_contents(
            app_path('Services/WorkCsvImportService.php')
        );

        $this->assertStringContainsString(
            '$this->validationMessages()',
            $source
        );

        $this->assertStringContainsString(
            '$this->validationAttributes()',
            $source
        );

        $this->assertStringNotContainsString(
            "\$this->rules(),\n                    [],",
            $source
        );
    }
}
