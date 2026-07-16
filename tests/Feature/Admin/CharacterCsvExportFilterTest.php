<?php

namespace Tests\Feature\Admin;

use App\Models\Character;
use App\Models\Tag;
use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CharacterCsvExportFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_csv_export_applies_exact_keyword_filter(): void
    {
        $user = $this->superAdmin();
        $work = Work::factory()->create();

        Character::factory()->create([
            'work_id' => $work->id,
            'name' => '完全一致対象',
        ]);

        Character::factory()->create([
            'work_id' => $work->id,
            'name' => '完全一致対象ではない',
        ]);

        $response = $this->actingAs($user)->get(
            route('admin.characters.csv-export', [
                'exact_keyword' => '完全一致対象',
            ])
        );

        $response
            ->assertOk()
            ->assertSee('完全一致対象')
            ->assertDontSee('完全一致対象ではない');
    }

    public function test_csv_export_combines_all_active_filters(): void
    {
        $user = $this->superAdmin();

        $targetWork = Work::factory()->create();
        $otherWork = Work::factory()->create();

        $targetTag = Tag::query()->create([
            'name' => '対象タグ',
            'slug' => 'target-character-tag',
            'type' => 'character',
        ]);

        $target = Character::factory()->create([
            'work_id' => $targetWork->id,
            'name' => '検索対象',
            'affiliation' => '対象所属',
            'status' => 'published',
        ]);

        $target->tags()->sync([$targetTag->id]);

        Character::factory()->create([
            'work_id' => $targetWork->id,
            'name' => '状態違い',
            'affiliation' => '対象所属',
            'status' => 'draft',
        ])->tags()->sync([$targetTag->id]);

        Character::factory()->create([
            'work_id' => $otherWork->id,
            'name' => '作品違い',
            'affiliation' => '対象所属',
            'status' => 'published',
        ])->tags()->sync([$targetTag->id]);

        $response = $this->actingAs($user)->get(
            route('admin.characters.csv-export', [
                'keyword' => '対象所属',
                'exact_keyword' => '検索対象',
                'work_id' => $targetWork->id,
                'tag_id' => $targetTag->id,
                'status' => 'published',
            ])
        );

        $response
            ->assertOk()
            ->assertSee('検索対象')
            ->assertDontSee('状態違い')
            ->assertDontSee('作品違い');
    }

    public function test_index_csv_export_link_keeps_current_query(): void
    {
        $user = $this->superAdmin();

        $response = $this->actingAs($user)->get(
            route('admin.characters.index', [
                'keyword' => '検索語',
                'exact_keyword' => '完全一致',
                'status' => 'published',
            ])
        );

        $response
            ->assertOk()
            ->assertSee(
                'keyword=%E6%A4%9C%E7%B4%A2%E8%AA%9E',
                false
            )
            ->assertSee(
                'exact_keyword=%E5%AE%8C%E5%85%A8%E4%B8%80%E8%87%B4',
                false
            )
            ->assertSee('status=published', false);
    }

    private function superAdmin(): User
    {
        return User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);
    }
}
