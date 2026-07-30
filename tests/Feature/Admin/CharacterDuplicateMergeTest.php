<?php

namespace Tests\Feature\Admin;

use App\Models\Character;
use App\Models\CharacterRelationship;
use App\Models\OriginalCharacterRelationship;
use App\Models\SavedPrompt;
use App\Models\Tag;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkStorySection;
use App\Services\Admin\CharacterDuplicateMergeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CharacterDuplicateMergeTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_open_duplicate_page(): void
    {
        [$work, $keep, $duplicate] =
            $this->duplicateCharacters();

        $this->actingAs($this->superAdmin())
            ->get(route(
                'admin.characters.duplicates.index'
            ))
            ->assertOk()
            ->assertSee('キャラクター重複チェック')
            ->assertSee($work->title)
            ->assertSee($keep->name)
            ->assertSee((string) $keep->id)
            ->assertSee((string) $duplicate->id)
            ->assertSee('残す')
            ->assertSee('統合後削除');
    }

    public function test_staff_cannot_use_duplicate_page(): void
    {
        $staff = User::factory()->create([
            'is_super_admin' => false,
            'status' => 'active',
        ]);

        $this->actingAs($staff)
            ->get(route(
                'admin.characters.duplicates.index'
            ))
            ->assertForbidden();
    }

    public function test_merge_keeps_lowest_id_and_moves_references(): void
    {
        [$work, $keep, $duplicate] =
            $this->duplicateCharacters();

        $otherWork = Work::factory()->create();
        $tag = Tag::factory()->create([
            'type' => 'character',
        ]);

        $duplicate->tags()->attach($tag->id);

        $duplicate->linkedWorks()->attach(
            $otherWork->id,
            [
                'is_primary' => false,
                'sort_order' => 5,
                'notes' => '追加作品',
            ]
        );

        $section = WorkStorySection::query()->create([
            'work_id' => $work->id,
            'section_type' => 'chapter',
            'section_number' => 1,
            'title' => '第1章',
            'spoiler_level' => 'none',
            'sort_order' => 1,
            'status' => 'published',
        ]);

        $section->characters()->attach(
            $duplicate->id,
            [
                'appearance_type' => 'appears',
                'age_at_section' => '16歳',
                'sort_order' => 4,
            ]
        );

        $otherCharacter = Character::factory()->create([
            'work_id' => $work->id,
        ]);

        $relationship = CharacterRelationship::query()->create([
            'work_id' => $work->id,
            'from_character_id' => $duplicate->id,
            'to_character_id' => $otherCharacter->id,
            'relationship' => '友人',
            'status' => 'published',
        ]);

        $writer = User::factory()->create([
            'status' => 'active',
        ]);

        $writerRelationship =
            OriginalCharacterRelationship::query()->create([
                'user_id' => $writer->id,
                'from_character_source' => 'v1',
                'to_character_source' => 'v1',
                'from_character_id' => $duplicate->id,
                'to_character_id' => $otherCharacter->id,
                'called_name' => '友人',
                'status' => 'active',
            ]);

        $prompt = SavedPrompt::query()->create([
            'user_id' => $writer->id,
            'title' => '重複統合プロンプト',
            'category' => 'other',
            'prompt_body' => '本文',
            'status' => 'active',
            'selected_character_refs' => [
                'v1:' . $duplicate->id,
                'v1:' . $keep->id,
            ],
        ]);

        DB::table('helpful_votes')->insert([
            'target_type' => Character::class,
            'target_id' => $duplicate->id,
            'session_id' => 'duplicate-test',
            'ip_address' => '127.0.0.1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $group = app(
            CharacterDuplicateMergeService::class
        )->groups()->first();

        $response = $this->actingAs(
            $this->superAdmin()
        )->post(
            route(
                'admin.characters.duplicates.merge'
            ),
            [
                'duplicate_groups' => [
                    $group['token'],
                ],
            ]
        );

        $response
            ->assertRedirect(route(
                'admin.characters.duplicates.index'
            ))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('characters', [
            'id' => $keep->id,
            'deleted_at' => null,
        ]);

        $this->assertSoftDeleted('characters', [
            'id' => $duplicate->id,
        ]);

        $this->assertDatabaseHas('character_tag', [
            'character_id' => $keep->id,
            'tag_id' => $tag->id,
        ]);

        $this->assertDatabaseHas('character_work', [
            'character_id' => $keep->id,
            'work_id' => $otherWork->id,
        ]);

        $this->assertDatabaseHas(
            'character_work_story_section',
            [
                'character_id' => $keep->id,
                'work_story_section_id' => $section->id,
                'age_at_section' => '16歳',
            ]
        );

        $this->assertSame(
            $keep->id,
            $relationship->fresh()->from_character_id
        );

        $this->assertSame(
            $keep->id,
            $writerRelationship
                ->fresh()
                ->from_character_id
        );

        $this->assertSame(
            ['v1:' . $keep->id],
            $prompt->fresh()->selected_character_refs
        );

        $this->assertDatabaseHas('helpful_votes', [
            'target_type' => Character::class,
            'target_id' => $keep->id,
            'session_id' => 'duplicate-test',
        ]);
    }

    public function test_only_selected_group_is_merged(): void
    {
        [$workA, $keepA, $duplicateA] =
            $this->duplicateCharacters('同名A');

        [$workB, $keepB, $duplicateB] =
            $this->duplicateCharacters('同名B');

        $groups = app(
            CharacterDuplicateMergeService::class
        )->groups();

        $groupA = $groups->first(
            fn (array $group): bool =>
                $group['name'] === '同名A'
        );

        $this->actingAs($this->superAdmin())
            ->post(
                route(
                    'admin.characters.duplicates.merge'
                ),
                [
                    'duplicate_groups' => [
                        $groupA['token'],
                    ],
                ]
            )
            ->assertRedirect();

        $this->assertSoftDeleted('characters', [
            'id' => $duplicateA->id,
        ]);

        $this->assertDatabaseHas('characters', [
            'id' => $duplicateB->id,
            'deleted_at' => null,
        ]);
    }

    private function duplicateCharacters(
        string $name = '重複キャラクター'
    ): array {
        $work = Work::factory()->create();

        $keep = Character::factory()->create([
            'work_id' => $work->id,
            'name' => $name,
        ]);

        $duplicate = Character::factory()->create([
            'work_id' => $work->id,
            'name' => $name,
        ]);

        return [$work, $keep, $duplicate];
    }

    private function superAdmin(): User
    {
        return User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);
    }
}
