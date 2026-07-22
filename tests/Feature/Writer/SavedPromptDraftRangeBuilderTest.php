<?php

namespace Tests\Feature\Writer;

use Tests\TestCase;

class SavedPromptDraftRangeBuilderTest extends TestCase
{
    public function test_story_section_builder_accepts_draft_and_published_sections(): void
    {
        $builder = file_get_contents(
            app_path('Services/WorkStorySectionPromptBuilder.php')
        );

        $this->assertStringContainsString(
            "->whereIn(\n                'status',\n                ['draft', 'published']\n            )",
            $builder
        );

        $this->assertStringNotContainsString(
            "->where('status', 'published')\n            ->whereHas(",
            $builder
        );
    }

    public function test_service_and_builder_use_the_same_available_statuses(): void
    {
        $service = file_get_contents(
            app_path('Services/SavedPromptService.php')
        );

        $builder = file_get_contents(
            app_path('Services/WorkStorySectionPromptBuilder.php')
        );

        $expected = "['draft', 'published']";

        $this->assertStringContainsString($expected, $service);
        $this->assertStringContainsString($expected, $builder);
    }

    public function test_selected_ranges_are_passed_to_story_section_builder(): void
    {
        $service = file_get_contents(
            app_path('Services/SavedPromptService.php')
        );

        $this->assertStringContainsString(
            "\$data['selected_story_event_ranges'] ?? []",
            $service
        );

        $this->assertStringContainsString(
            "\$this->storySectionBuilder->build(",
            $service
        );
    }
}
