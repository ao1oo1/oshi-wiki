<?php
namespace Tests\Feature\Admin;

use App\Models\Character;
use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminListAdvancedFilterTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);
    }

    public function test_work_list_supports_status_and_exact_keyword(): void
    {
        Work::factory()->create(['title' => '完全一致作品', 'status' => 'published']);
        Work::factory()->create(['title' => '完全一致作品 続編', 'status' => 'published']);

        $this->actingAs($this->admin())
            ->get(route('admin.works.index', [
                'status' => 'published',
                'exact_keyword' => '完全一致作品',
            ]))
            ->assertOk()
            ->assertSee('完全一致作品')
            ->assertDontSee('完全一致作品 続編')
            ->assertSee('キーワード（完全一致）');
    }

    public function test_character_list_supports_status_and_exact_keyword(): void
    {
        $work = Work::factory()->create();
        Character::factory()->create(['work_id' => $work->id, 'name' => '完全一致人物', 'status' => 'draft']);
        Character::factory()->create(['work_id' => $work->id, 'name' => '完全一致人物 別名', 'status' => 'draft']);

        $this->actingAs($this->admin())
            ->get(route('admin.characters.index', [
                'status' => 'draft',
                'exact_keyword' => '完全一致人物',
            ]))
            ->assertOk()
            ->assertSee('完全一致人物')
            ->assertDontSee('完全一致人物 別名');
    }

    public function test_relationship_and_tag_lists_show_common_filters(): void
    {
        $user = $this->admin();

        $this->actingAs($user)
            ->get(route('admin.character-relationships.index'))
            ->assertOk()
            ->assertSee('キーワード（完全一致）')
            ->assertSee('すべての状態');

        $this->actingAs($user)
            ->get(route('admin.tags.index'))
            ->assertOk()
            ->assertSee('キーワード（完全一致）')
            ->assertSee('すべての状態');
    }
}
