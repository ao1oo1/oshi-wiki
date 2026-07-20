<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MonetizationFoundationSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_monetization_foundation_tables_and_work_columns_exist(): void
    {
        $this->assertTrue(Schema::hasColumns('works', [
            'media_types', 'monetization_enabled',
            'monetization_inheritance', 'isbn', 'official_store_url',
        ]));

        $this->assertTrue(Schema::hasTable('monetization_services'));
        $this->assertTrue(Schema::hasTable('affiliate_programs'));
        $this->assertTrue(Schema::hasTable('work_monetization_links'));
    }
}
