<?php

namespace Tests\Feature\Admin;

use App\Models\SavedPrompt;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkStorySection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkStorySectionPhase4BTest extends TestCase
{
    use RefreshDatabase;

    public function test_trash_page_lists_story_sections(): void
    {
        $work = Work::factory()->create([
            'title' => 'ゴミ箱作品',
        ]);

        $section = $this->section(
            $work,
            '削除済み第1章'
        );
        $section->delete();

        $this->actingAs($this->superAdmin())
            ->get(route(
                'admin.trash.index',
                ['type' => 'story-sections']
            ))
            ->assertOk()
            ->assertSee('章・編')
            ->assertSee('削除済み第1章')
            ->assertSee('ゴミ箱作品')
            ->assertSee('復元');
    }

    public function test_story_section_can_be_restored(): void
    {
        $work = Work::factory()->create();
        $section = $this->section(
            $work,
            '復元対象章'
        );
        $section->delete();

        $this->actingAs($this->superAdmin())
            ->post(route(
                'admin.trash.restore',
                ['story-sections', $section->id]
            ))
            ->assertRedirect(route(
                'admin.trash.index',
                ['type' => 'story-sections']
            ))
            ->assertSessionHas('success');

        $this->assertDatabaseHas(
            'work_story_sections',
            [
                'id' => $section->id,
                'deleted_at' => null,
            ]
        );
    }

    public function test_restoring_child_restores_trashed_parent_first(): void
    {
        $work = Work::factory()->create();

        $parent = $this->section(
            $work,
            '削除済み編',
            null,
            'arc'
        );

        $child = $this->section(
            $work,
            '削除済み章',
            $parent->id
        );

        $child->delete();
        $parent->delete();

        $this->actingAs($this->superAdmin())
            ->post(route(
                'admin.trash.restore',
                ['story-sections', $child->id]
            ))
            ->assertSessionHas('success');

        $this->assertDatabaseHas(
            'work_story_sections',
            [
                'id' => $parent->id,
                'deleted_at' => null,
            ]
        );

        $this->assertDatabaseHas(
            'work_story_sections',
            [
                'id' => $child->id,
                'deleted_at' => null,
            ]
        );
    }

    public function test_section_cannot_restore_while_work_is_trashed(): void
    {
        $work = Work::factory()->create();
        $section = $this->section(
            $work,
            '所属作品削除中の章'
        );

        $section->delete();
        $work->delete();

        $this->actingAs($this->superAdmin())
            ->post(route(
                'admin.trash.restore',
                ['story-sections', $section->id]
            ))
            ->assertSessionHas('error');

        $this->assertNotNull(
            WorkStorySection::onlyTrashed()
                ->findOrFail($section->id)
                ->deleted_at
        );
    }

    public function test_force_delete_section_cascades_related_rows(): void
    {
        $work = Work::factory()->create();

        $section = $this->section(
            $work,
            '完全削除章'
        );

        $section->events()->create([
            'title' => '削除対象イベント',
            'sort_order' => 1,
        ]);

        $writer = User::factory()->create([
            'status' => 'active',
        ]);

        $prompt = SavedPrompt::query()->create([
            'user_id' => $writer->id,
            'title' => '章参照削除テスト',
            'category' => 'scene',
            'purpose' => '完全削除時の参照解除確認',
            'work_source' => SavedPrompt::WORK_SOURCE_V1,
            'work_id' => $work->id,
            'work_story_section_id' => $section->id,
            'selected_character_refs' => [],
            'include_relationship_timeline' => false,
            'include_work_worldbuilding' => false,
            'selected_work_worldbuilding_categories' => [],
            'writing_style' => 'dream_novel',
            'genre' => 'daily_life',
            'prompt_body' => 'テスト用プロンプト',
            'status' => 'active',
            'used_count' => 0,
        ]);

        $eventId = $section->events()
            ->firstOrFail()
            ->id;

        $section->delete();

        $this->actingAs($this->superAdmin())
            ->delete(route(
                'admin.trash.destroy',
                ['story-sections', $section->id]
            ))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing(
            'work_story_sections',
            ['id' => $section->id]
        );

        $this->assertDatabaseMissing(
            'work_story_section_events',
            ['id' => $eventId]
        );

        $this->assertNull(
            $prompt->refresh()->work_story_section_id
        );
    }

    public function test_force_delete_parent_removes_trashed_children_first(): void
    {
        $work = Work::factory()->create();

        $parent = $this->section(
            $work,
            '完全削除編',
            null,
            'arc'
        );

        $child = $this->section(
            $work,
            '完全削除子章',
            $parent->id
        );

        $child->delete();
        $parent->delete();

        $this->actingAs($this->superAdmin())
            ->delete(route(
                'admin.trash.destroy',
                ['story-sections', $parent->id]
            ))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing(
            'work_story_sections',
            ['id' => $parent->id]
        );

        $this->assertDatabaseMissing(
            'work_story_sections',
            ['id' => $child->id]
        );
    }

    public function test_force_delete_work_cascades_story_sections(): void
    {
        $work = Work::factory()->create();
        $section = $this->section(
            $work,
            '作品連動章'
        );

        $work->delete();

        $this->actingAs($this->superAdmin())
            ->delete(route(
                'admin.trash.destroy',
                ['works', $work->id]
            ))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing(
            'works',
            ['id' => $work->id]
        );

        $this->assertDatabaseMissing(
            'work_story_sections',
            ['id' => $section->id]
        );
    }

    public function test_destroy_all_includes_story_sections(): void
    {
        $work = Work::factory()->create();
        $section = $this->section(
            $work,
            '全削除対象章'
        );
        $section->delete();

        $this->actingAs($this->superAdmin())
            ->post(route('admin.trash.destroy-all'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing(
            'work_story_sections',
            ['id' => $section->id]
        );
    }

    public function test_non_super_admin_cannot_restore(): void
    {
        $work = Work::factory()->create();
        $section = $this->section(
            $work,
            '権限確認章'
        );
        $section->delete();

        $writer = User::factory()->create([
            'is_super_admin' => false,
            'status' => 'active',
        ]);

        $this->actingAs($writer)
            ->post(route(
                'admin.trash.restore',
                ['story-sections', $section->id]
            ))
            ->assertForbidden();
    }

    private function section(
        Work $work,
        string $title,
        ?int $parentId = null,
        string $type = 'chapter'
    ): WorkStorySection {
        return WorkStorySection::query()->create([
            'work_id' => $work->id,
            'parent_section_id' => $parentId,
            'section_type' => $type,
            'title' => $title,
            'status' => 'draft',
        ]);
    }

    private function superAdmin(): User
    {
        return User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);
    }
}
