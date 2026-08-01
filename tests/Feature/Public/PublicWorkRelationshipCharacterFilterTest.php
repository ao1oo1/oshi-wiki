<?php

namespace Tests\Feature\Public;

use Tests\TestCase;

class PublicWorkRelationshipCharacterFilterTest
    extends TestCase
{
    public function test_generated_work_description_is_removed(): void
    {
        $view = file_get_contents(
            resource_path(
                'views/public/works/show.blade.php'
            )
        );

        $this->assertStringNotContainsString(
            '作品情報ページです',
            $view
        );
    }

    public function test_relationship_character_filter_is_present(): void
    {
        $view = file_get_contents(
            resource_path(
                'views/public/works/show.blade.php'
            )
        );

        $this->assertStringContainsString(
            'data-public-work-relationship-filter',
            $view
        );

        $this->assertStringContainsString(
            'data-public-work-relationship-character-select',
            $view
        );

        $this->assertStringContainsString(
            'data-public-work-relationship-row',
            $view
        );

        $this->assertStringContainsString(
            "->unique('id')",
            $view
        );

        $this->assertStringContainsString(
            "->sortBy('id')",
            $view
        );
    }

    public function test_relationship_filter_javascript_exists(): void
    {
        $javascript = file_get_contents(
            resource_path('js/app.js')
        );

        $this->assertStringContainsString(
            'PUBLIC_WORK_RELATIONSHIP_CHARACTER_FILTER_START',
            $javascript
        );

        $this->assertStringContainsString(
            'dataset.characterIds',
            $javascript
        );
    }
    public function test_top_generated_intro_before_shortcuts_is_removed(): void
    {
        $view = file_get_contents(
            resource_path(
                'views/public/works/show.blade.php'
            )
        );

        $shortcutPosition = strpos(
            $view,
            'public-work-shortcuts'
        );

        $this->assertNotFalse($shortcutPosition);

        $beforeShortcuts = substr(
            $view,
            0,
            $shortcutPosition
        );

        $this->assertStringNotContainsString(
            "\$entitySeo['summary']",
            $beforeShortcuts
        );

        $this->assertStringNotContainsString(
            'aria-label="ページ概要"',
            $beforeShortcuts
        );
    }

    public function test_mobile_relationship_filter_forces_hidden_rows_off_screen(): void
    {
        $javascript = file_get_contents(
            resource_path('js/app.js')
        );

        $css = file_get_contents(
            resource_path('css/app.css')
        );

        $this->assertStringContainsString(
            "row.style.setProperty(",
            $javascript
        );

        $this->assertStringContainsString(
            "'display',",
            $javascript
        );

        $this->assertStringContainsString(
            "'none',",
            $javascript
        );

        $this->assertStringContainsString(
            "'important'",
            $javascript
        );

        $this->assertStringContainsString(
            '[data-public-work-relationship-row][hidden]',
            $css
        );

        $this->assertStringContainsString(
            'display: none !important;',
            $css
        );
    }

}
