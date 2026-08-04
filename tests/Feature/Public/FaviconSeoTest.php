<?php

namespace Tests\Feature\Public;

use Tests\TestCase;

class FaviconSeoTest extends TestCase
{
    public function test_home_declares_png_and_ico_favicons(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee(
                'href="' . asset('favicon_48.png') . '"',
                false
            )
            ->assertSee(
                'sizes="48x48"',
                false
            )
            ->assertSee(
                'href="' . asset('favicon_96.png') . '"',
                false
            )
            ->assertSee(
                'sizes="96x96"',
                false
            )
            ->assertSee(
                'href="' . asset('favicon_180.png') . '"',
                false
            )
            ->assertSee(
                'sizes="180x180"',
                false
            )
            ->assertSee(
                'href="' . asset('favicon.ico') . '"',
                false
            );
    }

    public function test_favicon_files_exist(): void
    {
        $this->assertFileExists(
            public_path('favicon_48.png')
        );

        $this->assertFileExists(
            public_path('favicon_96.png')
        );

        $this->assertFileExists(
            public_path('favicon_180.png')
        );

        $this->assertFileExists(
            public_path('favicon.ico')
        );
    }
}
