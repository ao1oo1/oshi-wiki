<?php

namespace Tests\Feature\Public;

use Tests\TestCase;

class PublicStoryCharacterNotesHiddenTest extends TestCase
{
    public function test_only_story_character_notes_are_hidden(): void
    {
        $view = file_get_contents(
            resource_path('views/public/works/show.blade.php')
        );

        $this->assertStringContainsString(
            '<main class="oshi-container space-y-8">',
            $view
        );

        $this->assertStringContainsString(
            'id="work-characters"',
            $view
        );

        $this->assertStringContainsString(
            'id="work-story-sections"',
            $view
        );

        $this->assertStringContainsString(
            'id="work-character-relationships"',
            $view
        );

        $this->assertStringContainsString(
            '章・編ごとの物語詳細',
            $view
        );

        $this->assertStringContainsString(
            '登場キャラクター',
            $view
        );

        $this->assertStringNotContainsString(
            '$character->pivot->notes',
            $view
        );

        $this->assertStringContainsString(
            '$character->pivot->character_state',
            $view
        );

        $this->assertStringContainsString(
            '$character->pivot->first_appearance',
            $view
        );

        $this->assertStringContainsString(
            '$event->notes',
            $view
        );

        $this->assertStringContainsString(
            '$section->notes',
            $view
        );
    }
}
