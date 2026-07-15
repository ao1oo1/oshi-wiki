<?php

namespace Tests\Feature\Public;

use App\Models\Character;
use App\Models\Work;
use App\Services\CharacterWorkLinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWorkMultipleWorksTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_work_detail_shows_additionally_linked_character(): void
    {
        $primaryWork = Work::factory()->create([
            'status' => 'published',
        ]);

        $chapterWork = Work::factory()->create([
            'status' => 'published',
        ]);

        $character = Character::factory()->create([
            'work_id' => $primaryWork->id,
            'name' => '公開追加作品キャラ',
            'status' => 'published',
        ]);

        app(CharacterWorkLinkService::class)->add(
            $character,
            $chapterWork->id
        );

        $this->get(route('public.works.show', $chapterWork))
            ->assertOk()
            ->assertSee('公開追加作品キャラ');
    }

    public function test_public_search_finds_work_by_additionally_linked_character(): void
    {
        $primaryWork = Work::factory()->create([
            'status' => 'published',
        ]);

        $chapterWork = Work::factory()->create([
            'title' => '検索対象章',
            'status' => 'published',
        ]);

        $character = Character::factory()->create([
            'work_id' => $primaryWork->id,
            'name' => '追加紐付け検索人物',
            'status' => 'published',
        ]);

        app(CharacterWorkLinkService::class)->add(
            $character,
            $chapterWork->id
        );

        $this->get(route('public.works.index', [
            'keyword' => '追加紐付け検索人物',
        ]))
            ->assertOk()
            ->assertSee('検索対象章');
    }

    public function test_public_home_loads_additional_character_without_duplicates(): void
    {
        $primaryWork = Work::factory()->create([
            'status' => 'published',
        ]);

        $chapterWork = Work::factory()->create([
            'title' => 'ホーム表示章',
            'status' => 'published',
        ]);

        $character = Character::factory()->create([
            'work_id' => $primaryWork->id,
            'name' => 'ホーム追加人物',
            'status' => 'published',
        ]);

        app(CharacterWorkLinkService::class)->add(
            $character,
            $chapterWork->id
        );

        $this->get(route('public.home'))
            ->assertOk()
            ->assertSee('ホーム表示章')
            ->assertSee('ホーム追加人物');
    }
}
