<?php

namespace Tests\Feature\Admin;

use App\Models\Character;
use App\Models\CharacterRelationship;
use App\Models\User;
use App\Models\Work;
use App\Services\Admin\CharacterRelationshipDuplicateMergeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CharacterRelationshipDuplicateMergeTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_open_duplicate_page(): void
    {
        [
            $work,
            $from,
            $to,
            $keep,
            $duplicate,
        ] = $this->duplicateRelationships();

        $this->actingAs($this->superAdmin())
            ->get(route(
                'admin.character-relationships'
                . '.duplicates.index'
            ))
            ->assertOk()
            ->assertSee('関係性重複チェック')
            ->assertSee($work->title)
            ->assertSee($from->name)
            ->assertSee($to->name)
            ->assertSee((string) $keep->id)
            ->assertSee((string) $duplicate->id)
            ->assertSee('残す')
            ->assertSee('削除');
    }

    public function test_non_super_admin_cannot_use_duplicate_page(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => false,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route(
                'admin.character-relationships'
                . '.duplicates.index'
            ))
            ->assertForbidden();
    }

    public function test_merge_keeps_lowest_id_and_soft_deletes_others(): void
    {
        [
            $work,
            $from,
            $to,
            $keep,
            $duplicate,
        ] = $this->duplicateRelationships();

        $group = app(
            CharacterRelationshipDuplicateMergeService::class
        )->groups()->first();

        $this->actingAs($this->superAdmin())
            ->post(
                route(
                    'admin.character-relationships'
                    . '.duplicates.merge'
                ),
                [
                    'merge_all' => false,
                    'duplicate_groups' => [
                        $group['token'],
                    ],
                ]
            )
            ->assertRedirect(route(
                'admin.character-relationships'
                . '.duplicates.index'
            ))
            ->assertSessionHas('success');

        $this->assertDatabaseHas(
            'character_relationships',
            [
                'id' => $keep->id,
                'deleted_at' => null,
            ]
        );

        $this->assertSoftDeleted(
            'character_relationships',
            [
                'id' => $duplicate->id,
            ]
        );
    }

    public function test_only_selected_group_is_merged(): void
    {
        [
            ,
            ,
            ,
            $keepA,
            $duplicateA,
        ] = $this->duplicateRelationships(
            'Aから',
            'Aへ'
        );

        [
            ,
            ,
            ,
            $keepB,
            $duplicateB,
        ] = $this->duplicateRelationships(
            'Bから',
            'Bへ'
        );

        $groups = app(
            CharacterRelationshipDuplicateMergeService::class
        )->groups();

        $groupA = $groups->first(
            fn (array $group): bool =>
                (int) $group['from_character_id']
                    === (int) $keepA->from_character_id
                && (int) $group['to_character_id']
                    === (int) $keepA->to_character_id
        );

        $this->actingAs($this->superAdmin())
            ->post(
                route(
                    'admin.character-relationships'
                    . '.duplicates.merge'
                ),
                [
                    'duplicate_groups' => [
                        $groupA['token'],
                    ],
                ]
            )
            ->assertRedirect();

        $this->assertSoftDeleted(
            'character_relationships',
            ['id' => $duplicateA->id]
        );

        $this->assertDatabaseHas(
            'character_relationships',
            [
                'id' => $duplicateB->id,
                'deleted_at' => null,
            ]
        );
    }

    public function test_merge_all_processes_every_duplicate_group(): void
    {
        [
            ,
            ,
            ,
            $keepA,
            $duplicateA,
        ] = $this->duplicateRelationships(
            '全件Aから',
            '全件Aへ'
        );

        [
            ,
            ,
            ,
            $keepB,
            $duplicateB,
        ] = $this->duplicateRelationships(
            '全件Bから',
            '全件Bへ'
        );

        $this->actingAs($this->superAdmin())
            ->post(
                route(
                    'admin.character-relationships'
                    . '.duplicates.merge'
                ),
                [
                    'merge_all' => true,
                ]
            )
            ->assertRedirect(route(
                'admin.character-relationships'
                . '.duplicates.index'
            ))
            ->assertSessionHas('success');

        $this->assertDatabaseHas(
            'character_relationships',
            [
                'id' => $keepA->id,
                'deleted_at' => null,
            ]
        );

        $this->assertDatabaseHas(
            'character_relationships',
            [
                'id' => $keepB->id,
                'deleted_at' => null,
            ]
        );

        $this->assertSoftDeleted(
            'character_relationships',
            ['id' => $duplicateA->id]
        );

        $this->assertSoftDeleted(
            'character_relationships',
            ['id' => $duplicateB->id]
        );
    }

    public function test_different_direction_is_not_duplicate(): void
    {
        $work = Work::factory()->create();

        $from = Character::factory()->create([
            'work_id' => $work->id,
        ]);
        $to = Character::factory()->create([
            'work_id' => $work->id,
        ]);

        CharacterRelationship::query()->create([
            'work_id' => $work->id,
            'from_character_id' => $from->id,
            'to_character_id' => $to->id,
            'called_name' => '順方向',
            'relationship' => '関係',
            'status' => 'published',
        ]);

        CharacterRelationship::query()->create([
            'work_id' => $work->id,
            'from_character_id' => $to->id,
            'to_character_id' => $from->id,
            'called_name' => '逆方向',
            'relationship' => '関係',
            'status' => 'published',
        ]);

        $groups = app(
            CharacterRelationshipDuplicateMergeService::class
        )->groups();

        $this->assertCount(0, $groups);
    }

    private function duplicateRelationships(
        string $fromName = '重複元',
        string $toName = '重複先'
    ): array {
        $work = Work::factory()->create();

        $from = Character::factory()->create([
            'work_id' => $work->id,
            'name' => $fromName,
        ]);

        $to = Character::factory()->create([
            'work_id' => $work->id,
            'name' => $toName,
        ]);

        $keep = CharacterRelationship::query()->create([
            'work_id' => $work->id,
            'from_character_id' => $from->id,
            'to_character_id' => $to->id,
            'called_name' => '先に登録',
            'relationship' => '友人',
            'impression' => '先に登録された内容',
            'notes' => '保持対象',
            'status' => 'published',
        ]);

        $duplicate =
            CharacterRelationship::query()->create([
                'work_id' => $work->id,
                'from_character_id' => $from->id,
                'to_character_id' => $to->id,
                'called_name' => '後から登録',
                'relationship' => '知人',
                'impression' => '後から登録された内容',
                'notes' => '削除対象',
                'status' => 'draft',
            ]);

        return [
            $work,
            $from,
            $to,
            $keep,
            $duplicate,
        ];
    }

    private function superAdmin(): User
    {
        return User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);
    }
}
