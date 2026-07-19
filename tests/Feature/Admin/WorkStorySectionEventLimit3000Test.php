<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Work;
use App\Models\WorkStorySection;
use App\Services\WorkStorySectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkStorySectionEventLimit3000Test extends TestCase
{
    use RefreshDatabase;

    public function test_service_event_limit_is_three_thousand(): void
    {
        $this->assertSame(
            3000,
            WorkStorySectionService::MAX_EVENTS_PER_SECTION
        );
    }

    public function test_section_accepts_three_thousand_events(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);

        $work = Work::factory()->create();
        $events = $this->events(3000);

        $response = $this->actingAs($user)->post(
            route('admin.works.story-sections.store', $work),
            [
                'section_type' => 'chapter',
                'title' => '3000件確認章',
                'spoiler_level' => 'none',
                'status' => 'draft',
                'events' => $events,
                'section_characters' => [],
            ]
        );

        $section = WorkStorySection::query()
            ->where('work_id', $work->id)
            ->where('title', '3000件確認章')
            ->firstOrFail();

        $response->assertRedirect(
            route(
                'admin.works.story-sections.show',
                [$work, $section]
            )
        );

        $this->assertSame(
            3000,
            $section->events()->count()
        );
    }

    public function test_section_rejects_three_thousand_and_one_events(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);

        $work = Work::factory()->create();

        $this->actingAs($user)
            ->post(
                route(
                    'admin.works.story-sections.store',
                    $work
                ),
                [
                    'section_type' => 'chapter',
                    'title' => '3001件拒否章',
                    'spoiler_level' => 'none',
                    'status' => 'draft',
                    'events' => $this->events(3001),
                    'section_characters' => [],
                ]
            )
            ->assertSessionHasErrors('events');

        $this->assertDatabaseMissing(
            'work_story_sections',
            [
                'work_id' => $work->id,
                'title' => '3001件拒否章',
            ]
        );
    }

    public function test_admin_pages_display_three_thousand_limit(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);

        $work = Work::factory()->create();

        $this->actingAs($user)
            ->get(
                route(
                    'admin.works.story-sections.create',
                    $work
                )
            )
            ->assertOk()
            ->assertSee('最大3000件');

        $section = WorkStorySection::query()->create([
            'work_id' => $work->id,
            'section_type' => 'chapter',
            'title' => '上限表示確認章',
            'spoiler_level' => 'none',
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->get(
                route(
                    'admin.works.story-sections.events.csv.create',
                    [$work, $section]
                )
            )
            ->assertOk()
            ->assertSee('3000');
    }

    private function events(int $count): array
    {
        $events = [];

        foreach (range(1, $count) as $number) {
            $events[] = [
                'title' => '物語詳細' . $number,
                'summary' => '内容' . $number,
                'sort_order' => $number,
            ];
        }

        return $events;
    }
}
