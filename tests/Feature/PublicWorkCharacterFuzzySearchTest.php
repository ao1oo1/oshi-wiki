<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicWorkCharacterFuzzySearchTest extends TestCase
{
    public function test_public_work_view_includes_character_search(): void
    {
        $view = file_get_contents(
            base_path('resources/views/public/works/show.blade.php')
        );

        $partial = file_get_contents(
            resource_path(
                'views/works/partials/'
                . 'character-fuzzy-search.blade.php'
            )
        );

        $this->assertStringContainsString(
            "works.partials.character-fuzzy-search",
            $view
        );

        $this->assertStringContainsString(
            'data-work-character-search-input',
            $partial
        );

        $this->assertStringContainsString(
            'normalize(\'NFKC\')',
            $partial
        );

        $this->assertStringContainsString(
            'toHiragana',
            $partial
        );

        $this->assertStringContainsString(
            'levenshtein',
            $partial
        );

        $this->assertStringContainsString(
            'isSubsequence',
            $partial
        );

        $this->assertStringContainsString(
            '該当するキャラクターが見つかりませんでした。',
            $partial
        );
    }
}
