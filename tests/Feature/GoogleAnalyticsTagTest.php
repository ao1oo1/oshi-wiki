<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\App;
use Tests\TestCase;

class GoogleAnalyticsTagTest extends TestCase
{
    public function test_google_analytics_partial_contains_measurement_id(): void
    {
        $contents = file_get_contents(
            resource_path('views/partials/google-analytics.blade.php')
        );

        $this->assertStringContainsString(
            'G-ESQQTQB1QH',
            $contents
        );
        $this->assertStringContainsString(
            'googletagmanager.com/gtag/js',
            $contents
        );
        $this->assertStringContainsString(
            "gtag('config'",
            $contents
        );
    }

    public function test_google_analytics_is_not_rendered_outside_production(): void
    {
        App::detectEnvironment(
            fn (): string => 'testing'
        );

        $html = view('partials.google-analytics')->render();

        $this->assertStringNotContainsString(
            'googletagmanager.com/gtag/js',
            $html
        );
        $this->assertStringNotContainsString(
            "gtag('config'",
            $html
        );
    }

    public function test_each_real_head_template_includes_google_analytics_once(): void
    {
        $headFiles = collect(
            $this->recursiveBladeFiles(
                resource_path('views')
            )
        )->filter(
            function (string $file): bool {
                return preg_match(
                    '/^\s*<head(?:\s[^>]*)?>/im',
                    file_get_contents($file)
                ) === 1;
            }
        );

        $this->assertNotEmpty($headFiles);

        foreach ($headFiles as $file) {
            $contents = file_get_contents($file);

            $this->assertSame(
                1,
                substr_count(
                    $contents,
                    'partials.google-analytics'
                ),
                $file
            );
        }
    }

    public function test_google_tag_body_exists_only_in_shared_partial(): void
    {
        $tagFiles = collect(
            $this->recursiveBladeFiles(
                resource_path('views')
            )
        )->filter(
            function (string $file): bool {
                return str_contains(
                    file_get_contents($file),
                    'googletagmanager.com/gtag/js'
                );
            }
        )->values();

        $this->assertCount(1, $tagFiles);

        $this->assertSame(
            resource_path('views/partials/google-analytics.blade.php'),
            $tagFiles->first()
        );
    }

    private function recursiveBladeFiles(string $directory): array
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $directory,
                \FilesystemIterator::SKIP_DOTS
            )
        );

        $files = [];

        foreach ($iterator as $file) {
            if (
                $file->isFile()
                && str_ends_with(
                    $file->getFilename(),
                    '.blade.php'
                )
            ) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }
}
