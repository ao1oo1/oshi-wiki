<?php

namespace Tests\Feature\Billing;

use Tests\TestCase;

class PlusCancellationCopyTest extends TestCase
{
    public function test_billing_page_contains_retention_copy(): void
    {
        $view = file_get_contents(
            resource_path('views/writer/billing/index.blade.php')
        );

        $this->assertStringContainsString(
            '創作データは3か月間保管されます。',
            $view
        );

        $this->assertStringContainsString(
            '閲覧とCSVエクスポート',
            $view
        );

        $this->assertStringContainsString(
            '自動的に削除され、復元できません。',
            $view
        );

        $this->assertStringContainsString(
            '3か月以内にPlusへ再加入した場合',
            $view
        );
    }
}
