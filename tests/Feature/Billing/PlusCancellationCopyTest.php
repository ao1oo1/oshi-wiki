<?php

namespace Tests\Feature\Billing;

use Tests\TestCase;

class PlusCancellationCopyTest extends TestCase
{
    public function test_billing_page_contains_requested_cancellation_copy(): void
    {
        $view = file_get_contents(
            resource_path('views/writer/billing/index.blade.php')
        );

        $this->assertStringContainsString(
            '有料プランをキャンセルした場合、期間終了次第データは削除されます。',
            $view
        );

        $this->assertStringContainsString(
            '期間までにデータを別で保存することをお勧めします。',
            $view
        );

        $this->assertStringContainsString(
            '閲覧・編集・削除・CSVインポート・エクスポートも使用できなくなります。',
            $view
        );
    }
}
