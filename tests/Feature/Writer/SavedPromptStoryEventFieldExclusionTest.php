<?php

namespace Tests\Feature\Writer;

use Tests\TestCase;

class SavedPromptStoryEventFieldExclusionTest extends TestCase
{
    public function test_story_event_title_is_not_added_to_prompt(): void
    {
        $builder = file_get_contents(
            app_path('Services/WorkStorySectionPromptBuilder.php')
        );

        $this->assertStringNotContainsString(
            "\$event->title ?: '名称未設定'",
            $builder
        );

        $this->assertStringNotContainsString(
            ". (\$event->title",
            $builder
        );
    }

    public function test_story_event_notes_are_not_added_to_prompt(): void
    {
        $builder = file_get_contents(
            app_path('Services/WorkStorySectionPromptBuilder.php')
        );

        $this->assertStringNotContainsString(
            "\$this->append(\$lines, '備考', \$event->notes",
            $builder
        );

        $this->assertStringNotContainsString(
            "\$this->append(\$eventLines, '備考', \$event->notes",
            $builder
        );
    }

    public function test_required_story_event_fields_remain_in_prompt(): void
    {
        $builder = file_get_contents(
            app_path('Services/WorkStorySectionPromptBuilder.php')
        );

        foreach ([
            "'タイミング'",
            "'場所'",
            "'詳細'",
            "'結果'",
        ] as $field) {
            $this->assertStringContainsString($field, $builder);
        }
    }

    public function test_event_number_is_kept_without_title(): void
    {
        $builder = file_get_contents(
            app_path('Services/WorkStorySectionPromptBuilder.php')
        );

        $this->assertStringContainsString(
            "\$lines[] = \$number . '.';",
            $builder
        );
    }
}
