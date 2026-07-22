<?php

namespace Tests\Feature\Writer;

use Tests\TestCase;

class SavedPromptPreviewUiTest extends TestCase
{
    public function test_preview_ui_serializes_selected_story_ranges(): void
    {
        $source = file_get_contents(
            resource_path(
                'views/writer/saved_prompts/_form.blade.php'
            )
        );

        $this->assertIsString($source);
        $this->assertStringContainsString(
            'プレビューを作成',
            $source
        );
        $this->assertStringContainsString(
            "formData.delete('selected_story_event_ranges[]')",
            $source
        );
        $this->assertStringContainsString(
            "'.story-event-range-checkbox:checked'",
            $source
        );
        $this->assertStringContainsString(
            "formData.append(",
            $source
        );
        $this->assertStringContainsString(
            "'selected_story_event_ranges[]'",
            $source
        );
    }

    public function test_preview_ui_displays_validation_details(): void
    {
        $source = file_get_contents(
            resource_path(
                'views/writer/saved_prompts/_form.blade.php'
            )
        );

        $this->assertIsString($source);
        $this->assertStringContainsString(
            'Object.values(',
            $source
        );
        $this->assertStringContainsString(
            'data.errors || {}',
            $source
        );
        $this->assertStringContainsString(
            'data.detail',
            $source
        );
        $this->assertStringContainsString(
            '通信エラーが発生しました。',
            $source
        );
    }
}
