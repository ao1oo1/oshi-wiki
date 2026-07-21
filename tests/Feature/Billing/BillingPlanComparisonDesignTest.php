<?php

namespace Tests\Feature\Billing;

use Tests\TestCase;

class BillingPlanComparisonDesignTest extends TestCase
{
    public function test_billing_page_contains_clear_plan_comparison(): void
    {
        $source = file_get_contents(
            resource_path('views/writer/billing/index.blade.php')
        );

        $this->assertIsString($source);

        foreach ([
            '無料プラン',
            'Oshi-Wiki Plus',
            '★ おすすめ ★',
            '最大20倍の登録容量',
            '月額480円でPlusを始める',
            'いつでも解約可能・解約金なし',
            'Plusがおすすめの方',
            '5倍',
            '10倍',
            '20倍',
        ] as $text) {
            $this->assertStringContainsString($text, $source);
        }
    }

    public function test_billing_page_uses_free_and_plus_plan_limits(): void
    {
        $source = file_get_contents(
            resource_path('views/writer/billing/index.blade.php')
        );

        $this->assertIsString($source);
        $this->assertStringContainsString(
            "\$freePlan['limits']['original_characters']",
            $source
        );
        $this->assertStringContainsString(
            "\$freePlan['limits']['relationships']",
            $source
        );
        $this->assertStringContainsString(
            "\$plusPlan['limits']['original_characters']",
            $source
        );
        $this->assertStringContainsString(
            "\$plusPlan['limits']['stories']",
            $source
        );
    }
}
