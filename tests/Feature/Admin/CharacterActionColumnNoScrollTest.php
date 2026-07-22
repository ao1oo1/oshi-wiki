<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class CharacterActionColumnNoScrollTest extends TestCase
{
    public function test_character_table_has_all_column_widths_including_id(): void
    {
        $view = file_get_contents(
            resource_path('views/admin/characters/index.blade.php')
        );

        $this->assertStringContainsString(
            'admin-character-table w-full table-fixed',
            $view
        );

        $this->assertStringNotContainsString(
            'min-w-[1050px]',
            $view
        );

        $this->assertStringContainsString(
            "'w-[4%]' : 'w-[5%]'",
            $view
        );

        $this->assertStringContainsString(
            '<col class="w-[3%]">',
            $view
        );

        $this->assertStringContainsString(
            "'w-[19%]' : 'w-[12%]'",
            $view
        );
    }

    public function test_checkbox_and_id_columns_use_compact_padding(): void
    {
        $view = file_get_contents(
            resource_path('views/admin/characters/index.blade.php')
        );

        $this->assertStringContainsString(
            'px-1 py-4 text-center font-bold',
            $view
        );

        $this->assertStringContainsString(
            'px-1 py-4 text-center align-middle',
            $view
        );

        $this->assertStringContainsString(
            'px-2 py-3 text-center text-xs',
            $view
        );
    }

    public function test_desktop_css_removes_table_minimum_width(): void
    {
        $css = file_get_contents(
            resource_path('css/app.css')
        );

        $this->assertStringContainsString(
            '.admin-character-table',
            $css
        );

        $this->assertStringContainsString(
            'min-width: 0 !important;',
            $css
        );

        $this->assertStringContainsString(
            'table-layout: fixed !important;',
            $css
        );
    }
}
