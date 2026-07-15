<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkWorldbuildingTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_work_with_worldbuilding_data(): void
    {
        $user = $this->createSuperAdmin();

        $response = $this->actingAs($user)->post(route('admin.works.store'), [
            'title' => '世界観テスト作品',
            'timeline_setting' => '原作10話以降',
            'building_layout' => '校舎は3階建て。',
            'status' => 'draft',
            'canon_events' => [
                [
                    'timing' => '第1章',
                    'event_name' => '入学式',
                    'event_status' => 'occurred',
                    'notes' => '主人公が初登場する。',
                ],
            ],
            'term_usages' => [
                [
                    'term' => '魔法石',
                    'meaning' => '魔力を蓄える石。',
                    'usage_example' => '魔法石を掲げる。',
                ],
            ],
        ]);

        $work = Work::query()->where('title', '世界観テスト作品')->firstOrFail();

        $response->assertRedirect(route('admin.works.show', $work));

        $this->assertDatabaseHas('works', [
            'id' => $work->id,
            'timeline_setting' => '原作10話以降',
            'building_layout' => '校舎は3階建て。',
        ]);
        $this->assertDatabaseHas('work_canon_events', [
            'work_id' => $work->id,
            'event_name' => '入学式',
            'event_status' => 'occurred',
        ]);
        $this->assertDatabaseHas('work_term_usages', [
            'work_id' => $work->id,
            'term' => '魔法石',
        ]);
    }

    public function test_super_admin_can_update_related_worldbuilding_rows(): void
    {
        $user = $this->createSuperAdmin();
        $work = Work::factory()->create(['created_by' => $user->id]);

        $work->canonEvents()->create([
            'sort_order' => 1,
            'event_name' => '古い出来事',
        ]);

        $response = $this->actingAs($user)->put(route('admin.works.update', $work), [
            'title' => $work->title,
            'status' => 'draft',
            'canon_events' => [
                ['event_name' => '新しい出来事', 'event_status' => 'not_yet'],
            ],
            'term_usages' => [
                ['term' => '新用語', 'usage_example' => '新用語を使う。'],
            ],
        ]);

        $response->assertRedirect(route('admin.works.show', $work));
        $this->assertDatabaseMissing('work_canon_events', ['event_name' => '古い出来事']);
        $this->assertDatabaseHas('work_canon_events', ['work_id' => $work->id, 'event_name' => '新しい出来事']);
        $this->assertDatabaseHas('work_term_usages', ['work_id' => $work->id, 'term' => '新用語']);
    }

    public function test_canon_events_are_limited_to_fifty(): void
    {
        $user = $this->createSuperAdmin();

        $events = [];
        for ($i = 1; $i <= 51; $i++) {
            $events[] = ['event_name' => '出来事' . $i];
        }

        $response = $this->actingAs($user)
            ->from(route('admin.works.create'))
            ->post(route('admin.works.store'), [
                'title' => '上限テスト',
                'canon_events' => $events,
            ]);

        $response->assertRedirect(route('admin.works.create'));
        $response->assertSessionHasErrors('canon_events');
        $this->assertDatabaseMissing('works', ['title' => '上限テスト']);
    }

    public function test_work_form_contains_accordion_categories(): void
    {
        $user = $this->createSuperAdmin();

        $response = $this->actingAs($user)->get(route('admin.works.create'));

        $response->assertOk()
            ->assertSee('物語の設計')
            ->assertSee('建物・空間')
            ->assertSee('生活・ルール')
            ->assertSee('組織・制度')
            ->assertSee('行事・時間の流れ')
            ->assertSee('地理・周辺環境')
            ->assertSee('小物・感覚的な情報')
            ->assertSee('用語');
    }

    private function createSuperAdmin(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->forceFill(['is_super_admin' => true])->save();

        return $user->refresh();
    }
}
