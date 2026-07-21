<?php

namespace Tests\Feature\Writer;

use Tests\TestCase;

class WriterNavigationBillingPlanTest extends TestCase
{
    public function test_writer_user_card_contains_plan_summary_and_link(): void
    {
        $source = file_get_contents(
            resource_path(
                'views/writer/original_characters/_layout_start.blade.php'
            )
        );

        $this->assertIsString($source);
        $this->assertStringContainsString(
            'writer-plan-summary',
            $source
        );
        $this->assertStringContainsString(
            '現在のプラン',
            $source
        );
        $this->assertStringContainsString(
            '無料プラン',
            $source
        );
        $this->assertStringContainsString(
            'Oshi-Wiki Plus',
            $source
        );
        $this->assertStringContainsString(
            "route('writer.billing.index')",
            $source
        );
        $this->assertStringContainsString(
            'プラン管理',
            $source
        );
    }

    public function test_writer_user_card_uses_paid_access_status(): void
    {
        $source = file_get_contents(
            resource_path(
                'views/writer/original_characters/_layout_start.blade.php'
            )
        );

        $this->assertIsString($source);
        $this->assertStringContainsString(
            '$writerBillingProfile?->hasPaidAccess()',
            $source
        );
        $this->assertStringContainsString(
            '$writerPlanName',
            $source
        );
    }
}
