<?php

namespace Tests\Feature\Admin;

use App\Models\Character;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkStorySection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkStorySectionPhase1Test extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_section_with_events_and_character_snapshot(): void
    {
        $user = $this->superAdmin();
        $work = Work::factory()->create();
        $character = Character::factory()->create([
            'work_id' => $work->id,
            'name' => '章テストキャラ',
            'status' => 'published',
        ]);

        $work->linkedCharacters()->sync([
            $character->id => [
                'is_primary' => true,
                'sort_order' => 0,
            ],
        ]);

        $response = $this->actingAs($user)->post(
            route('admin.works.story-sections.store', $work),
            [
                'section_type' => 'chapter',
                'section_number' => 1,
                'title' => '第1章 テスト',
                'short_label' => '1章',
                'synopsis' => '章の概要',
                'cumulative_settings' => '章までの設定',
                'spoiler_level' => 'minor',
                'sort_order' => 1,
                'status' => 'draft',
                'events' => [
                    [
                        'title' => '最初の出来事',
                        'summary' => '出来事の詳細',
                        'sort_order' => 1,
                    ],
                ],
                'section_characters' => [
                    [
                        'character_id' => $character->id,
                        'selected' => 1,
                        'appearance_type' => 'main',
                        'age_at_section' => '16歳',
                        'school_grade_at_section' => '1年',
                        'class_at_section' => 'A組',
                        'notes' => '章時点の備考',
                    ],
                ],
            ]
        );

        $section = WorkStorySection::query()
            ->where('title', '第1章 テスト')
            ->firstOrFail();

        $response->assertRedirect(
            route(
                'admin.works.story-sections.show',
                [$work, $section]
            )
        );

        $this->assertDatabaseHas(
            'work_story_section_events',
            [
                'work_story_section_id' => $section->id,
                'title' => '最初の出来事',
            ]
        );

        $this->assertDatabaseHas(
            'character_work_story_section',
            [
                'work_story_section_id' => $section->id,
                'character_id' => $character->id,
                'age_at_section' => '16歳',
                'school_grade_at_section' => '1年',
            ]
        );
    }

    public function test_section_limit_is_thirty_per_work(): void
    {
        $user = $this->superAdmin();
        $work = Work::factory()->create();

        for ($i = 1; $i <= 30; $i++) {
            WorkStorySection::query()->create([
                'work_id' => $work->id,
                'section_type' => 'chapter',
                'title' => '章' . $i,
                'status' => 'draft',
            ]);
        }

        $this->actingAs($user)
            ->post(
                route(
                    'admin.works.story-sections.store',
                    $work
                ),
                [
                    'section_type' => 'chapter',
                    'title' => '31件目',
                    'spoiler_level' => 'none',
                    'status' => 'draft',
                ]
            )
            ->assertSessionHasErrors('section');

        $this->assertDatabaseMissing(
            'work_story_sections',
            ['title' => '31件目']
        );
    }

    public function test_event_limit_is_two_thousand(): void
    {
        $user = $this->superAdmin();
        $work = Work::factory()->create();

        $events = [];

        for ($i = 1; $i <= 2001; $i++) {
            $events[] = ['title' => '出来事' . $i];
        }

        $this->actingAs($user)
            ->post(
                route(
                    'admin.works.story-sections.store',
                    $work
                ),
                [
                    'section_type' => 'chapter',
                    'title' => 'イベント上限テスト',
                    'spoiler_level' => 'none',
                    'status' => 'draft',
                    'events' => $events,
                ]
            )
            ->assertSessionHasErrors('events');
    }

    public function test_character_must_belong_to_work(): void
    {
        $user = $this->superAdmin();
        $work = Work::factory()->create();
        $otherWork = Work::factory()->create();

        $character = Character::factory()->create([
            'work_id' => $otherWork->id,
        ]);

        $otherWork->linkedCharacters()->sync([
            $character->id => [
                'is_primary' => true,
                'sort_order' => 0,
            ],
        ]);

        $this->actingAs($user)
            ->post(
                route(
                    'admin.works.story-sections.store',
                    $work
                ),
                [
                    'section_type' => 'chapter',
                    'title' => '他作品キャラテスト',
                    'spoiler_level' => 'none',
                    'status' => 'draft',
                    'section_characters' => [
                        [
                            'character_id' => $character->id,
                            'selected' => 1,
                        ],
                    ],
                ]
            )
            ->assertSessionHasErrors(
                'section_characters'
            );
    }

    public function test_story_section_routes_and_work_detail_link_exist(): void
    {
        $user = $this->superAdmin();
        $work = Work::factory()->create();

        $this->actingAs($user)
            ->get(
                route(
                    'admin.works.story-sections.index',
                    $work
                )
            )
            ->assertOk()
            ->assertSee('章・編ごとの物語詳細');

        $this->actingAs($user)
            ->get(route('admin.works.show', $work))
            ->assertOk()
            ->assertSee('章・編を管理する');
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
