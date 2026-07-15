<?php

namespace Tests\Feature\Admin;

use App\Models\Character;
use App\Models\CharacterRelationship;
use App\Models\User;
use App\Models\Work;
use App\Services\CharacterWorkLinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CharacterRelationshipMultipleWorksTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_form_includes_additionally_linked_characters(): void
    {
        $user = $this->superAdmin();

        $primaryA = Work::factory()->create();
        $primaryB = Work::factory()->create();
        $sharedWork = Work::factory()->create();

        $from = Character::factory()->create([
            'work_id' => $primaryA->id,
            'name' => '追加作品キャラA',
        ]);

        $to = Character::factory()->create([
            'work_id' => $primaryB->id,
            'name' => '追加作品キャラB',
        ]);

        app(CharacterWorkLinkService::class)->add($from, $sharedWork->id);
        app(CharacterWorkLinkService::class)->add($to, $sharedWork->id);

        $this->actingAs($user)
            ->get(route('admin.character-relationships.create', [
                'work_id' => $sharedWork->id,
            ]))
            ->assertOk()
            ->assertSee('追加作品キャラA')
            ->assertSee('追加作品キャラB');
    }

    public function test_relationship_can_be_created_for_shared_additional_work(): void
    {
        $user = $this->superAdmin();

        $primaryA = Work::factory()->create();
        $primaryB = Work::factory()->create();
        $sharedWork = Work::factory()->create();

        $from = Character::factory()->create([
            'work_id' => $primaryA->id,
        ]);

        $to = Character::factory()->create([
            'work_id' => $primaryB->id,
        ]);

        app(CharacterWorkLinkService::class)->add($from, $sharedWork->id);
        app(CharacterWorkLinkService::class)->add($to, $sharedWork->id);

        $this->actingAs($user)
            ->post(route('admin.character-relationships.store'), [
                'work_id' => $sharedWork->id,
                'from_character_id' => $from->id,
                'to_character_id' => $to->id,
                'relationship' => '共通作品での関係',
                'status' => 'draft',
            ])
            ->assertRedirect(route('admin.character-relationships.index'));

        $this->assertDatabaseHas('character_relationships', [
            'work_id' => $sharedWork->id,
            'from_character_id' => $from->id,
            'to_character_id' => $to->id,
            'relationship' => '共通作品での関係',
        ]);
    }

    public function test_relationship_is_rejected_when_one_character_is_not_linked(): void
    {
        $user = $this->superAdmin();

        $workA = Work::factory()->create();
        $workB = Work::factory()->create();

        $from = Character::factory()->create([
            'work_id' => $workA->id,
        ]);

        $to = Character::factory()->create([
            'work_id' => $workB->id,
        ]);

        $this->actingAs($user)
            ->from(route('admin.character-relationships.create', [
                'work_id' => $workA->id,
            ]))
            ->post(route('admin.character-relationships.store'), [
                'work_id' => $workA->id,
                'from_character_id' => $from->id,
                'to_character_id' => $to->id,
                'relationship' => '無効',
                'status' => 'draft',
            ])
            ->assertSessionHasErrors('to_character_id');

        $this->assertDatabaseCount('character_relationships', 0);
    }

    public function test_csv_import_accepts_shared_additional_work(): void
    {
        $user = $this->superAdmin();

        $primaryA = Work::factory()->create();
        $primaryB = Work::factory()->create();
        $sharedWork = Work::factory()->create();

        $from = Character::factory()->create([
            'work_id' => $primaryA->id,
        ]);

        $to = Character::factory()->create([
            'work_id' => $primaryB->id,
        ]);

        app(CharacterWorkLinkService::class)->add($from, $sharedWork->id);
        app(CharacterWorkLinkService::class)->add($to, $sharedWork->id);

        $csv = implode("\n", [
            'relationship_id,work_id,from_character_id,to_character_id,called_name,relationship,impression,notes,status',
            ",{$sharedWork->id},{$from->id},{$to->id},呼称,共通作品関係,,,draft",
        ]);

        $this->actingAs($user)
            ->post(route('admin.character-relationships.csv-import.store'), [
                'csv_file' => UploadedFile::fake()
                    ->createWithContent('relationships.csv', $csv),
                'default_status' => 'draft',
            ])
            ->assertRedirect(
                route('admin.character-relationships.csv-import.create')
            );

        $this->assertDatabaseHas('character_relationships', [
            'work_id' => $sharedWork->id,
            'from_character_id' => $from->id,
            'to_character_id' => $to->id,
            'relationship' => '共通作品関係',
        ]);
    }

    public function test_edit_form_keeps_additionally_linked_characters(): void
    {
        $user = $this->superAdmin();

        $primaryA = Work::factory()->create();
        $primaryB = Work::factory()->create();
        $sharedWork = Work::factory()->create();

        $from = Character::factory()->create([
            'work_id' => $primaryA->id,
            'name' => '編集対象A',
        ]);

        $to = Character::factory()->create([
            'work_id' => $primaryB->id,
            'name' => '編集対象B',
        ]);

        app(CharacterWorkLinkService::class)->add($from, $sharedWork->id);
        app(CharacterWorkLinkService::class)->add($to, $sharedWork->id);

        $relationship = CharacterRelationship::query()->create([
            'work_id' => $sharedWork->id,
            'from_character_id' => $from->id,
            'to_character_id' => $to->id,
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route(
                'admin.character-relationships.edit',
                $relationship
            ))
            ->assertOk()
            ->assertSee('編集対象A')
            ->assertSee('編集対象B');
    }

    private function superAdmin(): User
    {
        return User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);
    }
}
