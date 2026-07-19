<?php

namespace Tests\Feature\Public;

use Tests\TestCase;

class PublicWorkStorySectionAccordionGridTest extends TestCase
{
    private function viewContents(): string
    {
        return file_get_contents(
            resource_path(
                'views/public/works/show.blade.php'
            )
        );
    }

    public function test_public_story_sections_use_two_column_desktop_grid(): void
    {
        $contents = $this->viewContents();

        $this->assertStringContainsString(
            'grid grid-cols-1 gap-4 lg:grid-cols-2 lg:items-start',
            $contents
        );
    }

    public function test_top_level_story_sections_are_closed_by_default(): void
    {
        $contents = $this->viewContents();

        $start = strpos(
            $contents,
            '@foreach ($work->publishedStorySections as $section)'
        );

        $detailsPosition = strpos(
            $contents,
            '<details class="min-w-0',
            $start
        );

        $summaryPosition = strpos(
            $contents,
            '<summary class="cursor-pointer">',
            $detailsPosition
        );

        $this->assertNotFalse($start);
        $this->assertNotFalse($detailsPosition);
        $this->assertNotFalse($summaryPosition);

        $openingDetails = substr(
            $contents,
            $detailsPosition,
            $summaryPosition - $detailsPosition
        );

        $this->assertStringNotContainsString(
            ' open',
            $openingDetails
        );

        $this->assertStringNotContainsString(
            '@if (! $sectionIsMajorSpoiler)',
            $openingDetails
        );
    }

    public function test_chapter_title_font_is_smaller_on_desktop(): void
    {
        $contents = $this->viewContents();

        $this->assertStringContainsString(
            'text-base font-bold text-[#2D3748] lg:text-[0.95rem]',
            $contents
        );
    }

    public function test_section_order_remains_collection_order(): void
    {
        $contents = $this->viewContents();

        $gridPosition = strpos(
            $contents,
            'grid grid-cols-1 gap-4 lg:grid-cols-2 lg:items-start'
        );

        $foreachPosition = strpos(
            $contents,
            '@foreach ($work->publishedStorySections as $section)',
            $gridPosition
        );

        $detailsPosition = strpos(
            $contents,
            '<details class="min-w-0',
            $foreachPosition
        );

        $this->assertNotFalse($gridPosition);
        $this->assertNotFalse($foreachPosition);
        $this->assertNotFalse($detailsPosition);

        $this->assertLessThan(
            $foreachPosition,
            $gridPosition
        );

        $this->assertLessThan(
            $detailsPosition,
            $foreachPosition
        );
    }
}
