<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkParentChildPhase1Test extends TestCase
{
    use RefreshDatabase;

    public function test_work_can_have_parent_and_children(): void
    {
        $parent = Work::factory()->create([
            'title' => '親作品',
            'status' => 'published',
        ]);

        $child = Work::factory()->create([
            'title' => '子作品',
            'parent_work_id' => $parent->id,
            'child_sort_order' => 2,
            'status' => 'published',
        ]);

        $this->assertTrue($parent->isRootWork());
        $this->assertTrue($child->isChildWork());
        $this->assertSame(
            $parent->id,
            $child->parentWork->id
        );
        $this->assertSame(
            $child->id,
            $parent->childWorks->first()->id
        );
    }

    public function test_admin_form_can_select_parent_work(): void
    {
        $user = $this->superAdmin();

        Work::factory()->create([
            'title' => '選択対象の親作品',
        ]);

        $this->actingAs($user)
            ->get(route('admin.works.create'))
            ->assertOk()
            ->assertSee('作品の親子関係')
            ->assertSee('name="parent_work_id"', false)
            ->assertSee('選択対象の親作品');
    }

    public function test_child_work_is_not_shown_on_public_index(): void
    {
        $parent = Work::factory()->create([
            'title' => '公開親作品',
            'status' => 'published',
        ]);

        Work::factory()->create([
            'title' => '公開子作品',
            'parent_work_id' => $parent->id,
            'status' => 'published',
        ]);

        $this->get('/works')
            ->assertOk()
            ->assertSee('公開親作品')
            ->assertDontSee('公開子作品');
    }

    public function test_parent_page_shows_related_child_work(): void
    {
        $parent = Work::factory()->create([
            'title' => '関連作品親',
            'status' => 'published',
        ]);

        $child = Work::factory()->create([
            'title' => '関連作品子',
            'parent_work_id' => $parent->id,
            'status' => 'published',
        ]);

        $this->get(route('public.works.show', $parent))
            ->assertOk()
            ->assertSee('関連作品')
            ->assertSee('関連作品子')
            ->assertSee(
                route('public.works.show', $child),
                false
            );
    }

    public function test_child_page_shows_parent_link(): void
    {
        $parent = Work::factory()->create([
            'title' => 'リンク先親作品',
            'status' => 'published',
        ]);

        $child = Work::factory()->create([
            'title' => 'リンク元子作品',
            'parent_work_id' => $parent->id,
            'status' => 'published',
        ]);

        $this->get(route('public.works.show', $child))
            ->assertOk()
            ->assertSee('親作品')
            ->assertSee('リンク先親作品');
    }

    public function test_child_is_hidden_when_parent_is_not_published(): void
    {
        $parent = Work::factory()->create([
            'status' => 'draft',
        ]);

        $child = Work::factory()->create([
            'parent_work_id' => $parent->id,
            'status' => 'published',
        ]);

        $this->get(route('public.works.show', $child))
            ->assertNotFound();
    }

    public function test_parent_with_children_cannot_be_deleted(): void
    {
        $user = $this->superAdmin();

        $parent = Work::factory()->create();

        Work::factory()->create([
            'parent_work_id' => $parent->id,
        ]);

        $this->actingAs($user)
            ->delete(route('admin.works.destroy', $parent))
            ->assertSessionHasErrors('work');

        $this->assertDatabaseHas('works', [
            'id' => $parent->id,
            'deleted_at' => null,
        ]);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $user->forceFill([
            'is_super_admin' => true,
        ])->save();

        return $user->refresh();
    }
}
