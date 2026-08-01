<?php

namespace Tests\Feature\Admin;

use App\Models\Character;
use App\Models\SeoSetting;
use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoSettingsAndSearchKeywordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_save_seo_settings(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->patch(route('admin.analytics.seo.update'), [
                'site_title' => 'Oshi-Wiki SEO',
                'site_description' => '作品とキャラクターのデータベース',
                'site_keywords' => 'アニメ,漫画,ゲーム',
                'google_site_verification' => 'verification-code',
                'default_og_image_url' =>
                    'https://oshi-wiki.com/og.jpg',
                'append_site_name_to_titles' => '1',
            ])
            ->assertRedirect(
                route('admin.analytics.index', ['tab' => 'seo'])
            );

        $this->assertDatabaseHas('seo_settings', [
            'site_title' => 'Oshi-Wiki SEO',
            'google_site_verification' => 'verification-code',
        ]);
    }

    public function test_non_super_admin_cannot_save_seo_settings(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => false,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->patch(route('admin.analytics.seo.update'), [
                'site_title' => 'Blocked',
            ])
            ->assertForbidden();
    }

    public function test_work_search_keyword_finds_published_work(): void
    {
        $work = Work::factory()->create([
            'title' => '僕のヒーローアカデミア',
            'search_keywords' => "ヒロアカ\nMHA",
            'status' => 'published',
            'parent_work_id' => null,
        ]);

        $this->get(route('public.works.index', [
            'keyword' => 'MHA',
        ]))
            ->assertOk()
            ->assertSee($work->title);
    }

    public function test_character_search_keyword_finds_linked_work(): void
    {
        $work = Work::factory()->create([
            'title' => '僕のヒーローアカデミア',
            'status' => 'published',
            'parent_work_id' => null,
        ]);

        $character = Character::factory()->create([
            'work_id' => $work->id,
            'name' => '緑谷出久',
            'search_keywords' => 'デク, Deku',
            'status' => 'published',
        ]);

        $character->linkedWorks()->syncWithoutDetaching([
            $work->id => [
                'is_primary' => true,
                'sort_order' => 0,
            ],
        ]);

        $this->get(route('public.works.index', [
            'keyword' => 'Deku',
        ]))
            ->assertOk()
            ->assertSee($work->title);
    }

    public function test_admin_forms_have_search_keyword_fields(): void
    {
        $this->assertStringContainsString(
            'name="search_keywords"',
            file_get_contents(
                resource_path('views/admin/works/_form.blade.php')
            )
        );

        $this->assertStringContainsString(
            'name="search_keywords"',
            file_get_contents(
                resource_path('views/admin/characters/_form.blade.php')
            )
        );
    }

    public function test_public_meta_uses_saved_seo_settings(): void
    {
        SeoSetting::query()->create([
            'site_title' => 'Oshi-Wiki SEO',
            'site_description' => '共通SEO説明です。',
            'site_keywords' => '作品,キャラクター',
            'google_site_verification' => 'verification-code',
        ]);

        \App\Support\SeoSettings::forget();

        $this->get(route('public.home'))
            ->assertOk()
            ->assertSee('name="keywords"', false)
            ->assertSee('verification-code');
    }
    public function test_saving_seo_keywords_refreshes_cached_settings(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);

        \App\Support\SeoSettings::forget();

        $cachedDefaults = \App\Support\SeoSettings::get();

        $this->assertSame(
            'アニメ,漫画,ゲーム,キャラクター,人物相関図,'
                .'ストーリー,世界観,創作支援',
            $cachedDefaults->site_keywords
        );

        $newKeywords = '推し活,キャラクター設定,創作資料';

        $this->actingAs($user)
            ->patch(route('admin.analytics.seo.update'), [
                'site_title' => 'Oshi-Wiki',
                'site_description' => '創作支援データベース',
                'site_keywords' => $newKeywords,
                'google_site_verification' => null,
                'default_og_image_url' => null,
            ])
            ->assertRedirect(
                route('admin.analytics.index', ['tab' => 'seo'])
            );

        $this->assertDatabaseHas('seo_settings', [
            'site_keywords' => $newKeywords,
        ]);

        $this->assertDatabaseCount('seo_settings', 1);

        $this->assertSame(
            $newKeywords,
            \App\Support\SeoSettings::get()->site_keywords
        );

        $this->actingAs($user)
            ->get(route('admin.analytics.index', ['tab' => 'seo']))
            ->assertOk()
            ->assertSee($newKeywords);
    }

    public function test_saving_updates_existing_row_and_removes_duplicates(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);

        SeoSetting::query()->create([
            'site_title' => '古い設定1',
            'site_keywords' => '古いワード1',
        ]);

        SeoSetting::query()->create([
            'site_title' => '古い設定2',
            'site_keywords' => '古いワード2',
        ]);

        \App\Support\SeoSettings::forget();

        $this->assertSame(
            '古いワード1',
            \App\Support\SeoSettings::get()->site_keywords
        );

        $newKeywords = 'アニメ作品,漫画作品,ゲーム作品';

        $this->actingAs($user)
            ->patch(route('admin.analytics.seo.update'), [
                'site_title' => 'Oshi-Wiki',
                'site_description' => '創作支援データベース',
                'site_keywords' => $newKeywords,
                'google_site_verification' => null,
                'default_og_image_url' => null,
            ])
            ->assertRedirect(
                route('admin.analytics.index', ['tab' => 'seo'])
            );

        $this->assertDatabaseCount('seo_settings', 1);

        $this->assertDatabaseHas('seo_settings', [
            'site_title' => 'Oshi-Wiki',
            'site_keywords' => $newKeywords,
        ]);

        $this->assertSame(
            $newKeywords,
            \App\Support\SeoSettings::get()->site_keywords
        );
    }

    public function test_admin_seo_form_reads_database_when_public_cache_is_stale(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);

        \App\Models\SeoSetting::query()->create([
            'site_title' => 'Oshi-Wiki',
            'site_description' => 'DB最新説明',
            'site_keywords' => 'DB最新ワード,消えないSEO',
        ]);

        \Illuminate\Support\Facades\Cache::put(
            \App\Support\SeoSettings::CACHE_KEY,
            [
                'site_title' => '古いタイトル',
                'site_description' => '古い説明',
                'site_keywords' => '古いキャッシュ',
                'google_site_verification' => null,
                'default_og_image_url' => null,
            ],
            now()->addHour()
        );

        $response = $this->actingAs($user)
            ->get(route('admin.analytics.index', ['tab' => 'seo']));

        $response
            ->assertOk()
            ->assertSee('DB最新ワード,消えないSEO');

        $html = $response->getContent();

        $this->assertStringContainsString(
            '>DB最新ワード,消えないSEO</textarea>',
            $html
        );

        $this->assertStringNotContainsString(
            '>古いキャッシュ</textarea>',
            $html
        );
    }

    public function test_saving_seo_settings_rebuilds_cache_as_plain_array(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);

        $keywords = '保存後キャッシュ,共通SEOワード';

        $this->actingAs($user)
            ->patch(route('admin.analytics.seo.update'), [
                'site_title' => 'Oshi-Wiki',
                'site_description' => '保存確認',
                'site_keywords' => $keywords,
                'google_site_verification' => null,
                'default_og_image_url' => null,
            ])
            ->assertRedirect(route('admin.analytics.index', ['tab' => 'seo']));

        $cached = \Illuminate\Support\Facades\Cache::get(
            \App\Support\SeoSettings::CACHE_KEY
        );

        $this->assertIsArray($cached);
        $this->assertSame($keywords, $cached['site_keywords'] ?? null);
        $this->assertSame(
            $keywords,
            \App\Support\SeoSettings::get()->site_keywords
        );
    }

}
