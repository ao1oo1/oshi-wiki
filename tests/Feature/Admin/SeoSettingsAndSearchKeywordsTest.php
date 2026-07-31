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
}
