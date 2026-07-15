<?php

namespace Tests\Feature\Admin;

use App\Models\Character;
use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CharacterWorkMultipleSelectionUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_form_shows_primary_and_multiple_work_fields(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);

        Work::factory()->count(3)->create();

        $response = $this->actingAs($user)
            ->get(route('admin.characters.create'));

        $response
            ->assertOk()
            ->assertSee('主作品')
            ->assertSee('追加で紐付ける作品')
            ->assertSee('name="linked_work_ids[]"', false)
            ->assertSee('id="linked-work-search"', false);
    }

    public function test_store_links_character_to_multiple_works(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);

        $primary = Work::factory()->create();
        $chapter = Work::factory()->create();

        $response = $this->actingAs($user)->post(
            route('admin.characters.store'),
            [
                'work_id' => $primary->id,
                'linked_work_ids' => [$primary->id, $chapter->id],
                'name' => 'テストキャラクター',
                'status' => 'draft',
            ]
        );

        $response->assertRedirect(route('admin.characters.index'));

        $character = Character::query()
            ->where('name', 'テストキャラクター')
            ->firstOrFail();

        $this->assertSame($primary->id, $character->work_id);
        $this->assertTrue($character->isLinkedToWork($primary->id));
        $this->assertTrue($character->isLinkedToWork($chapter->id));
        $this->assertCount(2, $character->linkedWorks);
    }

    public function test_update_can_change_multiple_work_links(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);

        $primary = Work::factory()->create();
        $chapterOne = Work::factory()->create();
        $chapterTwo = Work::factory()->create();

        $character = Character::factory()->create([
            'work_id' => $primary->id,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->put(
            route('admin.characters.update', $character),
            [
                'work_id' => $chapterOne->id,
                'linked_work_ids' => [$chapterOne->id, $chapterTwo->id],
                'name' => $character->name,
                'status' => 'draft',
            ]
        );

        $response->assertRedirect(
            route('admin.characters.show', $character)
        );

        $character->refresh();

        $this->assertSame($chapterOne->id, $character->work_id);
        $this->assertFalse($character->isLinkedToWork($primary->id));
        $this->assertTrue($character->isLinkedToWork($chapterOne->id));
        $this->assertTrue($character->isLinkedToWork($chapterTwo->id));
    }
}
