<?php

namespace Tests\Feature\Public;

use Tests\TestCase;

class PublicWorkRelationshipTableLayoutTest extends TestCase
{
    public function test_relationship_table_has_dedicated_layout_classes(): void
    {
        $contents = file_get_contents(
            resource_path('views/public/works/show.blade.php')
        );

        $this->assertStringContainsString(
            'public-work-relationship-table-wrap',
            $contents
        );
        $this->assertStringContainsString(
            'public-work-relationship-table',
            $contents
        );
        $this->assertSame(
            2,
            substr_count(
                $contents,
                'public-work-relationship-table__impression'
            )
        );
    }

    public function test_relationship_table_css_wraps_long_text(): void
    {
        $contents = file_get_contents(
            resource_path('css/app.css')
        );

        $this->assertStringContainsString(
            '.public-work-relationship-table__impression',
            $contents
        );
        $this->assertStringContainsString(
            'overflow-wrap: anywhere !important;',
            $contents
        );
        $this->assertStringContainsString(
            'word-break: break-word !important;',
            $contents
        );
        $this->assertStringContainsString(
            'table-layout: fixed;',
            $contents
        );
    }
}
