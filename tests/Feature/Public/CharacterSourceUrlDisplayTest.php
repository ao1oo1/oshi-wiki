<?php

namespace Tests\Feature\Public;

use Tests\TestCase;

class CharacterSourceUrlDisplayTest extends TestCase
{
    public function test_character_source_urls_are_split_by_newlines_and_semicolons(): void
    {
        $view = file_get_contents(
            resource_path('views/public/characters/show.blade.php')
        );

        $this->assertStringContainsString(
            "'/(?:\\R|[;；])+/u'",
            $view
        );

        $this->assertStringContainsString(
            'href="{{ $url }}"',
            $view
        );

        $this->assertStringContainsString(
            'class="block break-all text-blue-700 underline"',
            $view
        );
    }

    public function test_split_pattern_keeps_each_url_separate(): void
    {
        $value = 'https://example.com/a; https://example.com/b'
            . PHP_EOL
            . 'https://example.com/c';

        $urls = collect(
            preg_split('/(?:\R|[;；])+/u', $value)
        )
            ->map(fn ($url) => trim($url))
            ->filter()
            ->values();

        $this->assertSame([
            'https://example.com/a',
            'https://example.com/b',
            'https://example.com/c',
        ], $urls->all());
    }
}
