<?php

namespace Tests\Feature\Admin;

use App\Models\Tag;
use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrashDestroyAllTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);
    }

    public function test_super_admin_can_permanently_delete_all_trashed_data(): void
    {
        $activeWork = Work::factory()->create([
            'title' => '残す作品',
        ]);

        $trashedWork = Work::factory()->create([
            'title' => '削除する作品',
        ]);
        $trashedWork->delete();

        $trashedTag = Tag::query()->create([
            'name' => '削除するタグ',
            'slug' => 'delete-test-tag',
            'type' => 'work',
            'status' => 'draft',
        ]);
        $trashedTag->delete();

        $this->actingAs($this->superAdmin())
            ->post(route('admin.trash.destroy-all'))
            ->assertRedirect(route('admin.trash.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('works', [
            'id' => $activeWork->id,
            'deleted_at' => null,
        ]);

        $this->assertDatabaseMissing('works', [
            'id' => $trashedWork->id,
        ]);

        $this->assertDatabaseMissing('tags', [
            'id' => $trashedTag->id,
        ]);
    }

    public function test_non_super_admin_cannot_delete_all_trash(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => false,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->post(route('admin.trash.destroy-all'))
            ->assertForbidden();
    }

    public function test_trash_page_shows_total_count_and_destroy_all_button(): void
    {
        $work = Work::factory()->create();
        $work->delete();

        $this->actingAs($this->superAdmin())
            ->get(route('admin.trash.index'))
            ->assertOk()
            ->assertSee('ゴミ箱内の全データを完全削除')
            ->assertSee('章・編')
            ->assertSee('合計1件')
            ->assertSee(route('admin.trash.destroy-all'), false)
            ->assertSee('confirmTrashDestroyAll', false);
    }
}
