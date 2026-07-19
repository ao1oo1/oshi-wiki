<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class AdminListResultCountLayoutTest extends TestCase
{
    public function test_shared_count_partial_has_requested_design(): void
    {
        $contents = file_get_contents(
            resource_path(
                'views/admin/partials/'
                . 'list-result-count.blade.php'
            )
        );

        foreach ([
            'justify-end',
            'text-right',
            '検索結果',
            '全体',
        ] as $required) {
            $this->assertStringContainsString(
                $required,
                $contents
            );
        }

        foreach ([
            'bg-white',
            'shadow',
            'border',
            'rounded',
        ] as $forbidden) {
            $this->assertStringNotContainsString(
                $forbidden,
                $contents
            );
        }
    }

    public function test_all_four_list_pages_use_shared_partial(): void
    {
        $expected = [
            'characters/index.blade.php' => 1,
            'works/index.blade.php' => 1,
            'tags/index.blade.php' => 2,
            'character_relationships/index.blade.php' => 1,
        ];

        foreach ($expected as $path => $count) {
            $contents = file_get_contents(
                resource_path('views/admin/' . $path)
            );

            $this->assertSame(
                $count,
                substr_count(
                    $contents,
                    'admin.partials.list-result-count'
                ),
                $path
            );

            $this->assertStringNotContainsString(
                'data-admin-result-count',
                $contents,
                $path
            );
        }
    }

    public function test_each_count_is_before_its_table(): void
    {
        $paths = [
            'characters/index.blade.php',
            'works/index.blade.php',
            'character_relationships/index.blade.php',
        ];

        foreach ($paths as $path) {
            $contents = file_get_contents(
                resource_path('views/admin/' . $path)
            );

            $include = strpos(
                $contents,
                'admin.partials.list-result-count'
            );

            $table = strpos(
                $contents,
                '<table',
                $include
            );

            $this->assertNotFalse($include, $path);
            $this->assertNotFalse($table, $path);
            $this->assertLessThan(
                $table,
                $include,
                $path
            );
        }
    }

    public function test_character_count_is_outside_table_shell(): void
    {
        $contents = file_get_contents(
            resource_path(
                'views/admin/characters/index.blade.php'
            )
        );

        $includePosition = strpos(
            $contents,
            'admin.partials.list-result-count'
        );

        $shellPosition = strpos(
            $contents,
            'staff-mobile-table-shell'
        );

        $this->assertNotFalse($includePosition);
        $this->assertNotFalse($shellPosition);

        $this->assertLessThan(
            $shellPosition,
            $includePosition
        );
    }

    public function test_tag_counts_are_before_both_tables(): void
    {
        $contents = file_get_contents(
            resource_path(
                'views/admin/tags/index.blade.php'
            )
        );

        $firstInclude = strpos(
            $contents,
            'admin.partials.list-result-count'
        );

        $firstTable = strpos(
            $contents,
            '<table',
            $firstInclude
        );

        $secondInclude = strpos(
            $contents,
            'admin.partials.list-result-count',
            $firstInclude + 1
        );

        $secondTable = strpos(
            $contents,
            '<table',
            $secondInclude
        );

        $this->assertNotFalse($firstInclude);
        $this->assertNotFalse($firstTable);
        $this->assertNotFalse($secondInclude);
        $this->assertNotFalse($secondTable);

        $this->assertLessThan(
            $firstTable,
            $firstInclude
        );

        $this->assertLessThan(
            $secondTable,
            $secondInclude
        );
    }
}
