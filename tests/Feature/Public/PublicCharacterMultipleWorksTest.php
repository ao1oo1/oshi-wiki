<?php

namespace Tests\Feature\Public;

use App\Models\Character;
use App\Models\Work;
use App\Services\CharacterWorkLinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCharacterMultipleWorksTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_character_page_shows_primary_and_additional_works(): void
    {
        $primaryWork = Work::factory()->create([
            'title' => '公開基本作品',
            'status' => 'published',
        ]);

        $chapterWork = Work::factory()->create([
            'title' => '公開追加章',
            'status' => 'published',
        ]);

        $character = Character::factory()->create([
            'work_id' => $primaryWork->id,
            'name' => '複数作品公開キャラ',
            'status' => 'published',
        ]);

        app(CharacterWorkLinkService::class)->add(
            $character,
            $chapterWork->id
        );

        $this->get(route('public.characters.show', $character))
            ->assertOk()
            ->assertSee('公開基本作品')
            ->assertSee('公開追加章')
            ->assertSee('主作品')
            ->assertSee('追加作品');
    }

    public function test_character_is_public_when_only_additional_linked_work_is_published(): void
    {
        $privatePrimary = Work::factory()->create([
            'status' => 'private',
        ]);

        $publishedAdditional = Work::factory()->create([
            'title' => '唯一の公開作品',
            'status' => 'published',
        ]);

        $character = Character::factory()->create([
            'work_id' => $privatePrimary->id,
            'name' => '追加作品のみ公開キャラ',
            'status' => 'published',
        ]);

        app(CharacterWorkLinkService::class)->add(
            $character,
            $publishedAdditional->id
        );

        $this->get(route('public.characters.show', $character))
            ->assertOk()
            ->assertSee('唯一の公開作品');
    }

    public function test_character_is_hidden_when_no_linked_work_is_published(): void
    {
        $privateWork = Work::factory()->create([
            'status' => 'private',
        ]);

        $character = Character::factory()->create([
            'work_id' => $privateWork->id,
            'status' => 'published',
        ]);

        $this->get(route('public.characters.show', $character))
            ->assertNotFound();
    }
}
