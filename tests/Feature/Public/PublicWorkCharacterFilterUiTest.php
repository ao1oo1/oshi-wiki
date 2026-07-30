<?php

namespace Tests\Feature\Public;

use Tests\TestCase;

class PublicWorkCharacterFilterUiTest extends TestCase
{
    public function test_work_detail_has_shortcut_links(): void
    {
        $contents = $this->viewContents();

        $this->assertStringContainsString(
            'href="#work-characters"',
            $contents
        );
        $this->assertStringContainsString(
            'href="#work-story-sections"',
            $contents
        );
        $this->assertStringContainsString(
            'href="#work-character-relationships"',
            $contents
        );
    }

    public function test_character_cards_hide_first_person_and_summary(): void
    {
        $contents = $this->viewContents();

        $this->assertStringNotContainsString(
            '一人称：{{ $character->first_person }}',
            $contents
        );
        $this->assertStringNotContainsString(
            '$character->personality',
            $this->characterSection($contents)
        );
    }

    public function test_character_filter_fields_and_card_data_exist(): void
    {
        $contents = $this->viewContents();

        $this->assertStringContainsString(
            'data-character-filter="affiliation"',
            $contents
        );
        $this->assertStringContainsString(
            'data-character-filter="school"',
            $contents
        );
        $this->assertStringContainsString(
            'data-character-filter="occupation"',
            $contents
        );
        $this->assertStringContainsString(
            'data-affiliation="{{ $character->affiliation }}"',
            $contents
        );
        $this->assertStringContainsString(
            'data-school="{{ $character->school_grade_class }}"',
            $contents
        );
        $this->assertStringContainsString(
            'data-occupation="{{ $character->occupation_position }}"',
            $contents
        );
    }

    public function test_detail_button_uses_bottom_action_wrapper(): void
    {
        $contents = $this->viewContents();

        $this->assertStringContainsString(
            'public-work-character-card__action',
            $contents
        );
        $this->assertStringContainsString(
            'public-work-character-card__detail',
            $contents
        );
    }

    private function viewContents(): string
    {
        return file_get_contents(
            resource_path('views/public/works/show.blade.php')
        );
    }

    private function characterSection(string $contents): string
    {
        $start = strpos($contents, 'id="work-characters"');
        $end = strpos($contents, 'id="work-story-sections"');

        $this->assertNotFalse($start);
        $this->assertNotFalse($end);

        return substr($contents, $start, $end - $start);
    }
    public function test_character_cards_hide_spoiler_badge(): void
    {
        $contents = $this->characterSection(
            $this->viewContents()
        );

        $this->assertStringNotContainsString(
            'ネタバレ：',
            $contents
        );
        $this->assertStringNotContainsString(
            'SPOILER_LEVELS',
            $contents
        );
    }

}
