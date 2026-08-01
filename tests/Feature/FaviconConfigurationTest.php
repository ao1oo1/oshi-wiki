<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class FaviconConfigurationTest extends TestCase
{
    public function test_favicon_file_is_present_and_large_enough(): void
    {
        $path = public_path('favicon.ico');

        $this->assertFileExists($path);
        $this->assertGreaterThan(
            1000,
            filesize($path)
        );
    }

    public function test_favicon_partial_uses_stable_root_url(): void
    {
        $partial = file_get_contents(
            resource_path('views/partials/favicon.blade.php')
        );

        $this->assertStringContainsString(
            "asset('favicon.ico')",
            $partial
        );

        $this->assertStringContainsString(
            'rel="icon"',
            $partial
        );

        $this->assertStringContainsString(
            'rel="shortcut icon"',
            $partial
        );
    }

    public function test_every_real_head_template_includes_favicon_once(): void
    {
        $templates = collect(
            File::allFiles(resource_path('views'))
        )->filter(
            fn ($file) =>
                str_ends_with($file->getFilename(), '.blade.php')
                && preg_match(
                    '/<head(?:\s[^>]*)?>/i',
                    $file->getContents()
                )
        );

        $this->assertNotEmpty($templates);

        foreach ($templates as $template) {
            $source = $template->getContents();

            $this->assertSame(
                1,
                substr_count(
                    $source,
                    "@include('partials.favicon')"
                ),
                $template->getRelativePathname()
            );
        }
    }
}
