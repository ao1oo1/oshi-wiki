<?php

namespace Tests\Feature\Public;

use Tests\TestCase;

class PublicPageJumpBelowHeaderTest extends TestCase
{
    public function test_public_views_put_top_jump_after_header(): void
    {
        $paths = [
            'about/show.blade.php',
            'characters/show.blade.php',
            'contact/create.blade.php',
            'contributor/apply.blade.php',
            'staff/show.blade.php',
            'tags/index.blade.php',
            'works/index.blade.php',
            'works/show.blade.php',
            'writing-tool.blade.php',
        ];

        foreach ($paths as $relativePath) {
            $contents = file_get_contents(
                resource_path(
                    'views/public/' . $relativePath
                )
            );

            $headerPosition = strpos(
                $contents,
                "@include('public.partials.header')"
            );

            $jumpPosition = strpos(
                $contents,
                'id="page-top"'
            );

            $this->assertNotFalse(
                $headerPosition,
                $relativePath
            );
            $this->assertNotFalse(
                $jumpPosition,
                $relativePath
            );
            $this->assertGreaterThan(
                $headerPosition,
                $jumpPosition,
                $relativePath
            );
        }
    }

    public function test_public_work_index_renders_header_before_jump(): void
    {
        $response = $this->get(
            route('public.works.index')
        );

        $response->assertOk();

        $html = $response->getContent();

        $headerPosition = strpos($html, '<header');
        $jumpPosition = strpos(
            $html,
            'id="page-top"'
        );

        $this->assertNotFalse($headerPosition);
        $this->assertNotFalse($jumpPosition);
        $this->assertGreaterThan(
            $headerPosition,
            $jumpPosition
        );
    }
}
