<?php

namespace Tests\Feature\Writer;

use App\Models\SavedPrompt;
use Tests\TestCase;

class SavedPromptStoryEventRangeFiveTest extends TestCase
{
    public function test_form_generates_ranges_in_five_event_units(): void
    {
        $source = file_get_contents(
            resource_path(
                'views/writer/saved_prompts/_form.blade.php'
            )
        );

        foreach ([
            '5件単位で選択してください。',
            'ceil($eventCount / 5)',
            '$rangeIndex * 5 + 1',
            '$start + 4',
        ] as $expected) {
            $this->assertStringContainsString(
                $expected,
                $source
            );
        }

        foreach ([
            'ceil($eventCount / 20)',
            '$rangeIndex * 20 + 1',
            '$start + 19',
        ] as $legacy) {
            $this->assertStringNotContainsString(
                $legacy,
                $source
            );
        }
    }

    public function test_service_validates_ranges_in_five_event_units(): void
    {
        $source = file_get_contents(
            app_path('Services/SavedPromptService.php')
        );

        $this->assertStringContainsString(
            '$end > $start + 4',
            $source
        );
        $this->assertStringContainsString(
            '(($start - 1) % 5) !== 0',
            $source
        );
        $this->assertStringContainsString(
            '物語詳細は5件単位で選択してください。',
            $source
        );

        $this->assertStringNotContainsString(
            '$end > $start + 19',
            $source
        );
        $this->assertStringNotContainsString(
            '(($start - 1) % 20) !== 0',
            $source
        );
    }

    public function test_legacy_saved_ranges_are_expanded_for_editing(): void
    {
        $source = file_get_contents(
            resource_path(
                'views/writer/saved_prompts/_form.blade.php'
            )
        );

        $this->assertStringContainsString(
            '旧20件単位の保存データも',
            $source
        );
        $this->assertStringContainsString(
            '$rangeStart += 5',
            $source
        );
        $this->assertStringContainsString(
            '$rangeStart + 4',
            $source
        );
    }
}
