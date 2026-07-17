<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Work;
use App\Models\WorkStorySection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkStorySectionEventLimitDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_csv_page_displays_five_hundred_event_limit(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);

        $work = Work::factory()->create();

        $section = WorkStorySection::query()->create([
            'work_id' => $work->id,
            'section_type' => 'chapter',
            'title' => '表示確認章',
            'spoiler_level' => 'none',
            'status' => 'draft',
            'sort_order' => 1,
        ]);

        $this->actingAs($user)
            ->get(route(
                'admin.works.story-sections.events.csv.create',
                [$work, $section]
            ))
            ->assertOk()
            ->assertSee('最大500件')
            ->assertDontSee('最大100件');
    }
}
