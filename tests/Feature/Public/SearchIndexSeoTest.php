<?php

namespace Tests\Feature\Public;

use App\Models\Character;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchIndexSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_is_available_as_xml(): void
    {
        $response = $this->get('/sitemap.xml');

        $response
            ->assertOk()
            ->assertHeader(
                'Content-Type',
                'application/xml; charset=UTF-8'
            )
            ->assertSee(
                route('public.home'),
                false
            )
            ->assertSee(
                route('public.works.index'),
                false
            );
    }

    public function test_sitemap_contains_only_published_content(): void
    {
        $publishedWork = Work::factory()->create([
            'status' => 'published',
        ]);

        $draftWork = Work::factory()->create([
            'status' => 'draft',
        ]);

        $publishedCharacter = Character::factory()->create([
            'status' => 'published',
            'work_id' => $publishedWork->id,
        ]);

        $draftCharacter = Character::factory()->create([
            'status' => 'draft',
            'work_id' => $publishedWork->id,
        ]);

        $response = $this->get('/sitemap.xml');

        $response
            ->assertSee(
                route(
                    'public.works.show',
                    $publishedWork
                ),
                false
            )
            ->assertDontSee(
                route(
                    'public.works.show',
                    $draftWork
                ),
                false
            )
            ->assertSee(
                route(
                    'public.characters.show',
                    $publishedCharacter
                ),
                false
            )
            ->assertDontSee(
                route(
                    'public.characters.show',
                    $draftCharacter
                ),
                false
            );
    }

    public function test_robots_points_to_sitemap_and_blocks_private_areas(): void
    {
        $contents = file_get_contents(
            public_path('robots.txt')
        );

        $this->assertStringContainsString(
            'Sitemap: https://oshi-wiki.com/sitemap.xml',
            $contents
        );
        $this->assertStringContainsString(
            'Disallow: /admin/',
            $contents
        );
        $this->assertStringContainsString(
            'Disallow: /writer/',
            $contents
        );
    }

    public function test_public_templates_include_shared_seo_meta(): void
    {
        $files = [
            'public/works/index.blade.php',
            'public/works/show.blade.php',
            'public/characters/show.blade.php',
            'public/tags/index.blade.php',
            'public/about/show.blade.php',
            'public/writing-tool.blade.php',
            'public/legal/_layout_start.blade.php',
        ];

        foreach ($files as $file) {
            $contents = file_get_contents(
                resource_path('views/' . $file)
            );

            $this->assertSame(
                1,
                substr_count(
                    $contents,
                    'partials.seo-meta'
                ),
                $file
            );
        }
    }

    public function test_shared_seo_meta_has_canonical_description_and_robots(): void
    {
        $contents = file_get_contents(
            resource_path(
                'views/partials/seo-meta.blade.php'
            )
        );

        $this->assertStringContainsString(
            'name="description"',
            $contents
        );
        $this->assertStringContainsString(
            'rel="canonical"',
            $contents
        );
        $this->assertStringContainsString(
            'name="robots"',
            $contents
        );
        $this->assertStringContainsString(
            'noindex, follow',
            $contents
        );
    }
}
