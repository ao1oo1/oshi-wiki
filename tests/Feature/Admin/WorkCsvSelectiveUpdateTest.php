<?php

namespace Tests\Feature\Admin;

use App\Support\WorkCsvUpdateOptions;
use Illuminate\Http\Request;
use Tests\TestCase;

class WorkCsvSelectiveUpdateTest extends TestCase
{
    public function test_default_options_are_safe(): void
    {
        $options = WorkCsvUpdateOptions::fromRequest(
            Request::create('/admin/works/import/csv', 'POST')
        );

        $this->assertSame(
            WorkCsvUpdateOptions::MODE_SELECTED,
            $options->mode
        );

        $this->assertSame(
            WorkCsvUpdateOptions::BLANK_KEEP,
            $options->blankMode
        );

        $this->assertSame(
            WorkCsvUpdateOptions::CHARACTER_IGNORE,
            $options->characterMode
        );

        $this->assertSame(
            WorkCsvUpdateOptions::RELATION_ERROR_FIELD,
            $options->relationErrorMode
        );
    }

    public function test_defaults_factory_matches_safe_settings(): void
    {
        $options = WorkCsvUpdateOptions::defaults();

        $this->assertSame(
            WorkCsvUpdateOptions::MODE_SELECTED,
            $options->mode
        );

        $this->assertSame([], $options->updateFields);

        $this->assertSame(
            WorkCsvUpdateOptions::BLANK_KEEP,
            $options->blankMode
        );

        $this->assertSame(
            WorkCsvUpdateOptions::CHARACTER_IGNORE,
            $options->characterMode
        );
    }

    public function test_legacy_service_defaults_preserve_previous_import_behavior(): void
    {
        $options = WorkCsvUpdateOptions::legacyImportDefaults();

        $this->assertSame(
            WorkCsvUpdateOptions::MODE_ALL,
            $options->mode
        );

        $this->assertSame(
            WorkCsvUpdateOptions::BLANK_CLEAR,
            $options->blankMode
        );

        $this->assertSame(
            WorkCsvUpdateOptions::CHARACTER_REPLACE,
            $options->characterMode
        );

        $this->assertSame(
            WorkCsvUpdateOptions::RELATION_ERROR_ROW,
            $options->relationErrorMode
        );
    }

    public function test_selected_fields_are_whitelisted(): void
    {
        $options = WorkCsvUpdateOptions::fromRequest(
            Request::create(
                '/admin/works/import/csv',
                'POST',
                [
                    'update_mode' => 'selected',
                    'update_fields' => [
                        'title',
                        'description',
                        'id',
                        'character_ids',
                        'not_a_column',
                    ],
                ]
            )
        );

        $this->assertSame(
            ['title', 'description'],
            $options->updateFields
        );
    }

    public function test_work_import_form_contains_update_controls(): void
    {
        $view = file_get_contents(
            resource_path('views/admin/works/csv-import.blade.php')
        );

        $partial = file_get_contents(
            resource_path(
                'views/admin/works/import/_update-options.blade.php'
            )
        );

        $formStart = strpos(
            $view,
            '<form method="POST" action="{{ route(\'admin.works.csv-import.store\') }}"'
        );
        $this->assertNotFalse($formStart);

        $formEnd = strpos($view, '</form>', $formStart);
        $this->assertNotFalse($formEnd);

        $form = substr($view, $formStart, $formEnd - $formStart);

        $this->assertStringContainsString(
            "admin.works.import._update-options",
            $form
        );

        $this->assertStringContainsString(
            'name="update_fields[]"',
            $partial
        );

        $this->assertStringContainsString(
            'name="character_ids_mode"',
            $partial
        );

        $this->assertStringContainsString(
            "'ignore' => '変更しない（おすすめ）'",
            $partial
        );

        $this->assertStringContainsString(
            'value="{{ $value }}"',
            $partial
        );
    }

    public function test_controller_passes_options_to_import_service(): void
    {
        $controller = file_get_contents(
            app_path(
                'Http/Controllers/Admin/WorkCsvImportController.php'
            )
        );

        $this->assertStringContainsString(
            '$updateOptions = $hasUpdateOptions',
            $controller
        );

        $this->assertStringContainsString(
            'WorkCsvUpdateOptions::fromRequest($request)',
            $controller
        );

        $this->assertStringContainsString(
            'WorkCsvUpdateOptions::legacyImportDefaults()',
            $controller
        );

        $this->assertStringContainsString(
            '$updateOptions',
            $controller
        );
    }

    public function test_controller_preserves_legacy_posts_without_options(): void
    {
        $controller = file_get_contents(
            app_path(
                'Http/Controllers/Admin/WorkCsvImportController.php'
            )
        );

        $this->assertStringContainsString(
            '$request->hasAny([',
            $controller
        );

        $this->assertStringContainsString(
            'WorkCsvUpdateOptions::legacyImportDefaults()',
            $controller
        );

        $this->assertStringContainsString(
            'WorkCsvUpdateOptions::fromRequest($request)',
            $controller
        );
    }

    public function test_service_contains_selective_update_paths(): void
    {
        $service = file_get_contents(
            app_path('Services/WorkCsvImportService.php')
        );

        $this->assertStringContainsString(
            'prepareExistingUpdatePayload',
            $service
        );

        $this->assertStringContainsString(
            'applyCharacterUpdateMode',
            $service
        );

        $this->assertStringContainsString(
            'RELATION_ERROR_FIELD',
            $service
        );

        $this->assertStringContainsString(
            'CHARACTER_IGNORE',
            $service
        );

        $this->assertStringContainsString(
            'shouldCreate()',
            $service
        );

        $this->assertStringContainsString(
            'shouldUpdate()',
            $service
        );
    }
}
