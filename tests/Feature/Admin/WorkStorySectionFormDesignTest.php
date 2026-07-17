<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Work;
use App\Models\WorkStorySection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkStorySectionFormDesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_and_edit_use_responsive_form_layout(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);

        $work = Work::factory()->create([
            'title' => 'フォームデザイン確認作品',
        ]);

        $section = WorkStorySection::query()->create([
            'work_id' => $work->id,
            'section_type' => 'chapter',
            'title' => '第1章',
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->get(route(
                'admin.works.story-sections.create',
                $work
            ))
            ->assertOk()
            ->assertSee('oshi-story-section-page', false)
            ->assertSee('oshi-story-section-form', false)
            ->assertSee('oshi-story-section-actions', false);

        $this->actingAs($user)
            ->get(route(
                'admin.works.story-sections.edit',
                [$work, $section]
            ))
            ->assertOk()
            ->assertSee('oshi-story-section-page', false)
            ->assertSee('oshi-story-section-form', false)
            ->assertSee('oshi-story-section-actions', false);
    }

    public function test_css_defines_full_width_inputs_and_mobile_layout(): void
    {
        $css = file_get_contents(
            resource_path('css/app.css')
        );

        $this->assertStringContainsString(
            '.oshi-input {',
            $css
        );

        $this->assertStringContainsString(
            'width: 100%;',
            $css
        );

        $this->assertStringContainsString(
            '.oshi-story-section-actions',
            $css
        );

        $this->assertStringContainsString(
            '@media (max-width: 640px)',
            $css
        );
    }
}
