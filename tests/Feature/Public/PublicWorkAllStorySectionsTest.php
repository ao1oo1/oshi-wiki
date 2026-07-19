<?php

namespace Tests\Feature\Public;

use App\Models\Work;
use App\Models\WorkStorySection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWorkAllStorySectionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_detail_lists_eight_available_sections(): void
    {
        $work = Work::factory()->create([
            'status' => 'published',
        ]);

        $titles = [
            '第１章　真紅の暴君',
            '第２章　荒野の反逆者',
            '第３章　深海の商人',
            '第４章　熱砂の策謀家',
            '第５章　美貌の圧制者',
            '第６章　冥府の番人',
            '第７章　深淵の支配者',
            '第８章　禁忌の執行人',
        ];

        foreach ($titles as $index => $title) {
            WorkStorySection::query()->create([
                'work_id' => $work->id,
                'section_type' => 'chapter',
                'section_number' => $index + 1,
                'title' => $title,
                'status' => $index === 0
                    ? 'published'
                    : 'draft',
                'sort_order' => $index + 1,
            ]);
        }

        WorkStorySection::query()->create([
            'work_id' => $work->id,
            'section_type' => 'chapter',
            'title' => '管理者のみの章',
            'status' => 'private',
            'sort_order' => 9,
        ]);

        $response = $this->get(
            route('public.works.show', $work)
        );

        $response->assertOk();

        foreach ($titles as $title) {
            $response->assertSee($title);
        }

        $response->assertDontSee('管理者のみの章');
    }
}
