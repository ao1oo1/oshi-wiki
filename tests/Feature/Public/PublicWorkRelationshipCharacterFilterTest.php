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

}
