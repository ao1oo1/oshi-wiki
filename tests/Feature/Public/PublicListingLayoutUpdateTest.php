<?php

namespace Tests\Feature\Public;

use Tests\TestCase;

class PublicListingLayoutUpdateTest extends TestCase
{
    public function test_home_limits_work_cards_to_nine(): void
    {
        $view = file_get_contents(
            resource_path(
                'views/public/works/index.blade.php'
            )
        );

        $this->assertStringContainsString(
            '($isHome ?? false) ? $works->take(9) : $works',
            $view
        );
    }

    public function test_works_index_hides_home_search_media_and_tags(): void
    {
        $view = file_get_contents(
            resource_path(
                'views/public/works/index.blade.php'
            )
        );

        $this->assertGreaterThanOrEqual(
            4,
            substr_count(
                $view,
                '@if ($isHome ?? false)'
            )
        );

        $this->assertStringContainsString(
            '媒体から探す',
            $view
        );

        $this->assertStringContainsString(
            'タグから探す',
            $view
        );
    }

    public function test_tags_index_has_no_pagination(): void
    {
        $view = file_get_contents(
            resource_path(
                'views/public/tags/index.blade.php'
            )
        );

        $query = file_get_contents(
            base_path('app/Http/Controllers/Public/TagController.php')
        );

        $this->assertStringNotContainsString(
            '$tags->links()',
            $view
        );

        $this->assertStringNotContainsString(
            '->paginate(',
            $query
        );
    }
}
