<?php

namespace Tests\Feature\Public;

use App\Models\Character;
use App\Models\Tag;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EntityAllFieldsSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_character_page_uses_profile_fields_for_seo(): void
    {
        $work = Work::query()->create([
            'title' => 'ツイステッドワンダーランド',
            'slug' => 'twisted-wonderland-character-seo',
            'status' => 'published',
        ]);

        $tag = Tag::query()->create([
            'name' => 'ハーツラビュル寮',
            'slug' => 'heartslabyul',
            'type' => '所属',
            'status' => 'published',
        ]);

        $character = Character::query()->create([
            'work_id' => $work->id,
            'name' => 'エース・トラッポラ',
            'name_kana' => 'えーす・とらっぽら',
            'name_english' => 'Ace Trappola',
            'search_keywords' => 'エース 誕生日,エース 寮',
            'birthday' => '9月23日',
            'height' => '172cm',
            'affiliation' => 'ハーツラビュル寮',
            'school_grade_class' => '1年A組',
            'first_person' => 'オレ',
            'status' => 'published',
        ]);

        $character->tags()->attach($tag);

        $this->get(route('public.characters.show', $character))
            ->assertOk()
            ->assertSee(
                'エース・トラッポラ・誕生日・プロフィール',
                false
            )
            ->assertSee('エース 誕生日', false)
            ->assertSee('9月23日', false)
            ->assertSee('ハーツラビュル寮', false)
            ->assertSee('application/ld+json', false)
            ->assertSee('"birthDate":"9月23日"', false)
            ->assertSee('aria-label="ページ概要"', false);
    }

    public function test_work_page_uses_all_registered_fields_for_seo(): void
    {
        $work = Work::query()->create([
            'title' => 'ツイステッドワンダーランド',
            'slug' => 'twisted-wonderland-work-seo',
            'title_kana' => 'ついすてっどわんだーらんど',
            'search_keywords' =>
                'オクタヴィネル スペル,Octavinelle,寮 英語表記',
            'genre' => '学園ファンタジー',
            'school_dorm_rules' =>
                'オクタヴィネル寮の英語表記はOctavinelle。',
            'organizations_memberships' =>
                'ナイトレイブンカレッジの七寮。',
            'status' => 'published',
        ]);

        $this->get(route('public.works.show', $work))
            ->assertOk()
            ->assertSee(
                'ツイステッドワンダーランド｜'
                . 'キャラクター・世界観・用語・設定',
                false
            )
            ->assertSee('オクタヴィネル スペル', false)
            ->assertSee('Octavinelle', false)
            ->assertSee('学校・寮・規則', false)
            ->assertSee('application/ld+json', false)
            ->assertSee('"@type":"CreativeWork"', false);
    }

    public function test_unregistered_information_is_not_invented(): void
    {
        $work = Work::query()->create([
            'title' => '情報未登録作品',
            'slug' => 'unregistered-information-work',
            'status' => 'published',
        ]);

        $character = Character::query()->create([
            'work_id' => $work->id,
            'name' => '情報未登録キャラクター',
            'status' => 'published',
        ]);

        $this->get(route('public.characters.show', $character))
            ->assertOk()
            ->assertDontSee('誕生日は未設定', false)
            ->assertDontSee('所属は未設定', false)
            ->assertDontSee('"birthDate"', false);
    }
}
