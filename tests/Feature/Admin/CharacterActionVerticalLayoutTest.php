<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class CharacterActionVerticalLayoutTest extends TestCase
{
    public function test_character_actions_are_vertical_and_content_sized(): void
    {
        $view = file_get_contents(
            resource_path('views/admin/characters/index.blade.php')
        );

        $this->assertStringContainsString(
            'mx-auto inline-flex w-fit flex-col items-stretch justify-center gap-2 whitespace-nowrap',
            $view
        );

        $this->assertStringContainsString(
            'min-w-[88px] justify-center px-4 py-2',
            $view
        );

        $this->assertStringContainsString(
            'class="w-full"',
            $view
        );

        $this->assertStringContainsString(
            'px-3 py-3 align-middle admin-index-action-cell',
            $view
        );
    }

    public function test_character_table_uses_reduced_width_for_vertical_actions(): void
    {
        $view = file_get_contents(
            resource_path('views/admin/characters/index.blade.php')
        );

        $this->assertStringContainsString(
            'min-w-[1050px] table-fixed',
            $view
        );

        $this->assertStringContainsString(
            "? 'w-[17%]' : 'w-[15%]'",
            $view
        );
    }
}
