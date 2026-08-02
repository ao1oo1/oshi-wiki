<?php

namespace Tests\Feature\Public;

use Tests\TestCase;

class PublicStoryEventSpacingTest extends TestCase
{
    public function test_story_event_text_does_not_preserve_template_indentation(): void
    {
        $view = file_get_contents(
            resource_path('views/public/works/show.blade.php')
        );

        $this->assertStringContainsString(
            '<div class="mt-3 whitespace-pre-line leading-8">{{ trim($event->summary) }}</div>',
            $view
        );

        $this->assertStringContainsString(
            '<div class="mt-1 whitespace-pre-line leading-7">{{ trim($event->outcome) }}</div>',
            $view
        );

        $this->assertStringContainsString(
            '<div class="mt-3 rounded-xl bg-[#F7FAFC] p-3">',
            $view
        );

        $this->assertStringNotContainsString(
            '<div class="mt-3 whitespace-pre-wrap leading-8">'
                . PHP_EOL
                . '                                                            {{ $event->summary }}',
            $view
        );

        $this->assertStringNotContainsString(
            '<div class="mt-2 whitespace-pre-wrap leading-8">'
                . PHP_EOL
                . '                                                                {{ $event->outcome }}',
            $view
        );
    }
}
