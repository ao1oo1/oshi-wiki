<?php

namespace Tests\Feature\Writer;

use Tests\TestCase;

class SavedPromptPreviewRuntimeTest extends TestCase
{
    public function test_preview_script_declares_all_referenced_dom_variables(): void
    {
        $view = file_get_contents(
            resource_path('views/writer/saved_prompts/_form.blade.php')
        );

        $expectedDeclarations = [
            "const writingStyleSelect = document.getElementById(",
            "const writingStyleOtherWrap = document.getElementById(",
            "const genreSelect = document.getElementById(",
            "const genreOtherWrap = document.getElementById(",
            "const workWorldbuildingSection = document.getElementById(",
            "const includeWorkWorldbuilding = document.getElementById(",
            "const workWorldbuildingCategories = document.getElementById(",
            "const workWorldbuildingCategoryCheckboxes = Array.from(",
        ];

        foreach ($expectedDeclarations as $declaration) {
            $this->assertStringContainsString(
                $declaration,
                $view
            );
        }
    }

    public function test_preview_listener_is_registered_before_optional_ui_listeners(): void
    {
        $view = file_get_contents(
            resource_path('views/writer/saved_prompts/_form.blade.php')
        );

        $previewPosition = strpos(
            $view,
            "previewButton?.addEventListener('click', generatePreview)"
        );

        $writingStylePosition = strpos(
            $view,
            "writingStyleSelect?.addEventListener("
        );

        $this->assertNotFalse($previewPosition);
        $this->assertNotFalse($writingStylePosition);
        $this->assertLessThan(
            $writingStylePosition,
            $previewPosition
        );
    }

    public function test_preview_fetch_keeps_csrf_and_json_headers(): void
    {
        $view = file_get_contents(
            resource_path('views/writer/saved_prompts/_form.blade.php')
        );

        $this->assertStringContainsString(
            "'X-CSRF-TOKEN': '{{ csrf_token() }}'",
            $view
        );

        $this->assertStringContainsString(
            "'Accept': 'application/json'",
            $view
        );

        $this->assertStringContainsString(
            "'X-Requested-With': 'XMLHttpRequest'",
            $view
        );
    }
}
