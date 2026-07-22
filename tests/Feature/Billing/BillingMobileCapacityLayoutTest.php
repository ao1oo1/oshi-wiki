<?php

namespace Tests\Feature\Billing;

use Tests\TestCase;

class BillingMobileCapacityLayoutTest extends TestCase
{
    public function test_plus_capacity_rows_use_non_wrapping_three_column_grid(): void
    {
        $source = file_get_contents(
            resource_path('views/writer/billing/index.blade.php')
        );

        $this->assertIsString($source);
        $this->assertSame(
            4,
            substr_count(
                $source,
                'grid-cols-[minmax(0,1fr)_auto_auto]'
            )
        );
        $this->assertGreaterThanOrEqual(
            8,
            substr_count($source, 'whitespace-nowrap')
        );
        $this->assertStringContainsString(
            'オリジナルキャラクター',
            $source
        );
        $this->assertStringContainsString('5倍', $source);
        $this->assertStringContainsString('10倍', $source);
        $this->assertStringContainsString('20倍', $source);
    }
}
