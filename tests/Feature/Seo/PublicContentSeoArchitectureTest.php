<?php

namespace Tests\Feature\Seo;

use App\Models\Character;
use App\Models\Work;
use App\Services\PublicSeoContentBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicContentSeoArchitectureTest extends TestCase
{
    use RefreshDatabase;

    public function test_work_seo_contains_search_intent(): void
    {
        $work = Work::factory()->create([
            'title' => '薬屋のひとりごと',
            'original_media' => '漫画',
            'status' => 'published',
        ]);

        $builder = app(PublicSeoContentBuilder::class);

        $this->assertStringContainsString(
            '薬屋のひとりごと',
            $builder->workTitle($work)
        );

        $this->assertStringContainsString(
            '登場人物',
            $builder->workTitle($work)
        );

        $this->assertStringContainsString(
            'キャラクター',
            $builder->workDescription($work)
        );
    }

    public function test_character_seo_contains_work_name(): void
    {
        $work = Work::factory()->create([
            'title' => 'ツイステ',
            'status' => 'published',
        ]);

        $character = Character::factory()->create([
            'name' => 'リドル・ローズハート',
            'status' => 'published',
        ]);

        $character->linkedWorks()->attach($work, ['is_primary' => true]);

        $builder = app(PublicSeoContentBuilder::class);

        $this->assertStringContainsString(
            'リドル・ローズハート',
            $builder->characterTitle($character)
        );

        $this->assertStringContainsString(
            'ツイステ',
            $builder->characterTitle($character)
        );

        $this->assertStringContainsString(
            '口調',
            $builder->characterTitle($character)
        );

        $this->assertLessThanOrEqual(
            70,
            mb_strlen(
                $builder->characterTitle($character)
            )
        );
    }

    public function test_public_detail_html_uses_dynamic_seo(): void
    {
        $work = Work::factory()->create([
            'title' => '原神',
            'original_media' => 'ゲーム',
            'status' => 'published',
        ]);

        $response = $this->get(
            route('public.works.show', $work)
        );

        $response
            ->assertOk()
            ->assertSee(
                '<title>原神｜キャラクター一覧・人物関係・'
                . 'ストーリー・世界観</title>',
                false
            )
            ->assertSee(
                'name="description"',
                false
            );
    }

    public function test_backfill_and_sitemap_commands(): void
    {
        Work::factory()->create([
            'title' => 'SEO作品',
            'status' => 'published',
        ]);

        $this->artisan('seo:backfill')
            ->assertSuccessful();

        $this->artisan('sitemap:generate')
            ->assertSuccessful();

        $this->assertFileExists(
            public_path('sitemap.xml')
        );

        $this->assertStringContainsString(
            'SEO作品',
            Work::query()->firstOrFail()->seo_title
        );
    }

    public function test_internal_link_partials_are_registered(): void
    {
        $workView = file_get_contents(
            resource_path(
                'views/public/works/show.blade.php'
            )
        );

        $characterView = file_get_contents(
            resource_path(
                'views/public/characters/show.blade.php'
            )
        );

        $this->assertStringContainsString(
            'work-seo-internal-links',
            $workView
        );

        $this->assertStringContainsString(
            'character-seo-internal-links',
            $characterView
        );
    }
}
