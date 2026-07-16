<?php

namespace Tests\Feature\Writer;

use App\Models\Character;
use App\Models\Role;
use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WriterWorkHierarchyPhase2BTest extends TestCase
{
    use RefreshDatabase;

    public function test_prompt_form_shows_hierarchical_work_title(): void
    {
        $user = $this->writer();

        $parent = Work::factory()->create([
            'title' => 'Writer親作品',
            'status' => 'published',
        ]);

        $child = Work::factory()->create([
            'title' => 'Writer子作品',
            'parent_work_id' => $parent->id,
            'status' => 'published',
        ]);

        $character = Character::factory()->create([
            'work_id' => $child->id,
            'status' => 'published',
        ]);

        $child->linkedCharacters()->sync([
            $character->id => [
                'is_primary' => true,
                'sort_order' => 0,
            ],
        ]);

        $this->actingAs($user)
            ->get(route('writer.prompts.create'))
            ->assertOk()
            ->assertSee('Writer親作品')
            ->assertSee('Writer子作品')
            ->assertSee('＞');
    }

    public function test_relationship_form_shows_hierarchical_work_title(): void
    {
        $user = $this->writer();

        $parent = Work::factory()->create([
            'title' => '関係性親作品',
            'status' => 'published',
        ]);

        $child = Work::factory()->create([
            'title' => '関係性子作品',
            'parent_work_id' => $parent->id,
            'status' => 'published',
        ]);

        $character = Character::factory()->create([
            'work_id' => $child->id,
            'status' => 'published',
        ]);

        $child->linkedCharacters()->sync([
            $character->id => [
                'is_primary' => true,
                'sort_order' => 0,
            ],
        ]);

        $this->actingAs($user)
            ->get(
                route(
                    'writer.original-character-relationships.create'
                )
            )
            ->assertOk()
            ->assertSee('関係性親作品')
            ->assertSee('関係性子作品')
            ->assertSee('＞');
    }

    public function test_child_of_draft_parent_is_not_selectable(): void
    {
        $user = $this->writer();

        $parent = Work::factory()->create([
            'status' => 'draft',
        ]);

        $child = Work::factory()->create([
            'title' => '選択不可子作品',
            'parent_work_id' => $parent->id,
            'status' => 'published',
        ]);

        $character = Character::factory()->create([
            'work_id' => $child->id,
            'status' => 'published',
        ]);

        $child->linkedCharacters()->sync([
            $character->id => [
                'is_primary' => true,
                'sort_order' => 0,
            ],
        ]);

        $this->actingAs($user)
            ->get(route('writer.prompts.create'))
            ->assertOk()
            ->assertDontSee('選択不可子作品');
    }

    private function writer(): User
    {
        $writerRole = Role::query()->firstOrCreate(
            ['name' => 'writer'],
            ['label' => '一般執筆ユーザー']
        );

        return User::factory()->create([
            'status' => 'active',
            'role_id' => $writerRole->id,
        ]);
    }
}
