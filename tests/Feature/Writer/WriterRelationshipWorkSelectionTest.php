<?php

namespace Tests\Feature\Writer;

use App\Models\Character;
use App\Models\OriginalCharacter;
use App\Models\Role;
use App\Models\User;
use App\Models\Work;
use App\Services\CharacterWorkLinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WriterRelationshipWorkSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_has_work_and_character_selectors(): void
    {
        $user = $this->writerUser();

        $work = Work::factory()->create([
            'title' => '選択対象作品',
            'status' => 'published',
        ]);

        Character::factory()->create([
            'work_id' => $work->id,
            'name' => '作品登録キャラクター',
            'status' => 'published',
        ]);

        OriginalCharacter::query()->create([
            'user_id' => $user->id,
            'name' => '自作キャラクター',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route(
                'writer.original-character-relationships.create'
            ))
            ->assertOk()
            ->assertSee('name="from_work_ref"', false)
            ->assertSee('name="to_work_ref"', false)
            ->assertSee('オリジナルキャラクター')
            ->assertSee('選択対象作品')
            ->assertSee('作品登録キャラクター')
            ->assertSee('自作キャラクター')
            ->assertSee('data-work-refs="original"', false)
            ->assertSee(
                'data-work-refs="work:' . $work->id . '"',
                false
            );
    }

    public function test_additional_work_is_in_character_work_refs(): void
    {
        $user = $this->writerUser();

        $primary = Work::factory()->create([
            'status' => 'published',
        ]);

        $chapter = Work::factory()->create([
            'title' => '追加作品',
            'status' => 'published',
        ]);

        $character = Character::factory()->create([
            'work_id' => $primary->id,
            'name' => '追加作品キャラクター',
            'status' => 'published',
        ]);

        app(CharacterWorkLinkService::class)->add(
            $character,
            $chapter->id
        );

        $response = $this->actingAs($user)->get(
            route('writer.original-character-relationships.create')
        );

        $response
            ->assertOk()
            ->assertSee('追加作品')
            ->assertSee('追加作品キャラクター')
            ->assertSee('work:' . $chapter->id, false);
    }

    public function test_store_accepts_original_and_work_character(): void
    {
        $user = $this->writerUser();

        $original = OriginalCharacter::query()->create([
            'user_id' => $user->id,
            'name' => '関係元オリジナル',
            'status' => 'active',
        ]);

        $work = Work::factory()->create([
            'status' => 'published',
        ]);

        $character = Character::factory()->create([
            'work_id' => $work->id,
            'status' => 'published',
        ]);

        $this->actingAs($user)
            ->post(
                route(
                    'writer.original-character-relationships.store'
                ),
                [
                    'from_work_ref' => 'original',
                    'from_character_ref' =>
                        'original:' . $original->id,
                    'to_work_ref' => 'work:' . $work->id,
                    'to_character_ref' =>
                        'v1:' . $character->id,
                    'relationship_type' => '友人',
                    'status' => 'active',
                ]
            )
            ->assertRedirect();

        $this->assertDatabaseHas(
            'original_character_relationships',
            [
                'user_id' => $user->id,
                'from_original_character_id' => $original->id,
                'to_character_id' => $character->id,
                'relationship_type' => '友人',
            ]
        );
    }

    public function test_store_rejects_character_from_other_work(): void
    {
        $user = $this->writerUser();

        $selectedWork = Work::factory()->create([
            'status' => 'published',
        ]);

        $otherWork = Work::factory()->create([
            'status' => 'published',
        ]);

        $from = Character::factory()->create([
            'work_id' => $selectedWork->id,
            'status' => 'published',
        ]);

        $to = Character::factory()->create([
            'work_id' => $otherWork->id,
            'status' => 'published',
        ]);

        $this->actingAs($user)
            ->post(
                route(
                    'writer.original-character-relationships.store'
                ),
                [
                    'from_work_ref' =>
                        'work:' . $selectedWork->id,
                    'from_character_ref' => 'v1:' . $from->id,
                    'to_work_ref' =>
                        'work:' . $selectedWork->id,
                    'to_character_ref' => 'v1:' . $to->id,
                    'status' => 'active',
                ]
            )
            ->assertSessionHasErrors('to_character_ref');

        $this->assertDatabaseCount(
            'original_character_relationships',
            0
        );
    }

    private function writerUser(): User
    {
        $role = Role::query()->firstOrCreate(
            ['name' => 'writer'],
            ['label' => '一般執筆ユーザー']
        );

        return User::factory()->create([
            'role_id' => $role->id,
            'status' => 'active',
        ]);
    }
}
