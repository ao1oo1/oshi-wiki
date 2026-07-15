<?php

namespace Tests\Feature\Admin;

use App\Models\Character;
use App\Models\User;
use App\Models\Work;
use App\Services\CharacterWorkLinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CharacterWorkMultipleListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_character_filter_finds_additional_linked_work(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);

        $primary = Work::factory()->create([
            'title' => '基本作品',
        ]);

        $chapter = Work::factory()->create([
            'title' => '第1章',
        ]);

        $character = Character::factory()->create([
            'work_id' => $primary->id,
            'name' => '複数作品キャラクター',
        ]);

        app(CharacterWorkLinkService::class)
            ->add($character, $chapter->id);

        $response = $this->actingAs($user)->get(
            route('admin.characters.index', [
                'work_id' => $chapter->id,
            ])
        );

        $response
            ->assertOk()
            ->assertSee('複数作品キャラクター')
            ->assertSee('基本作品')
            ->assertSee('第1章');
    }

    public function test_character_list_shows_other_work_count(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);

        $primary = Work::factory()->create(['title' => '基本作品']);
        $chapterOne = Work::factory()->create(['title' => '第1章']);
        $chapterTwo = Work::factory()->create(['title' => '第2章']);

        $character = Character::factory()->create([
            'work_id' => $primary->id,
            'name' => '対象キャラクター',
        ]);

        app(CharacterWorkLinkService::class)->sync(
            $character,
            [$primary->id, $chapterOne->id, $chapterTwo->id],
            $primary->id
        );

        $response = $this->actingAs($user)
            ->get(route('admin.characters.index'));

        $response
            ->assertOk()
            ->assertSee('基本作品')
            ->assertSee('ほか2作品')
            ->assertSee('第1章')
            ->assertSee('第2章');
    }

    public function test_work_detail_contains_additionally_linked_character(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);

        $primary = Work::factory()->create();
        $chapter = Work::factory()->create();

        $character = Character::factory()->create([
            'work_id' => $primary->id,
            'name' => '章にも登場するキャラクター',
        ]);

        app(CharacterWorkLinkService::class)
            ->add($character, $chapter->id);

        $response = $this->actingAs($user)
            ->get(route('admin.works.show', $chapter));

        $response
            ->assertOk()
            ->assertSee('章にも登場するキャラクター');
    }
}
