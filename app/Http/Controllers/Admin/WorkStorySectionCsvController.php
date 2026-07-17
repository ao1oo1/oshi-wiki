<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WorkStorySection\ImportWorkStorySectionCsvRequest;
use App\Models\Work;
use App\Services\WorkStorySectionCsvService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkStorySectionCsvController extends Controller
{
    public function create(
        Work $work,
        Request $request
    ): View {
        $this->authorizeManage();

        return view(
            'admin.work_story_sections.csv',
            [
                'work' => $work,
                'selectedType' =>
                    $request->string('type')
                        ->toString() ?: 'sections',
            ]
        );
    }

    public function import(
        Work $work,
        ImportWorkStorySectionCsvRequest $request,
        WorkStorySectionCsvService $service
    ): RedirectResponse {
        $type = $request->validate([
            'type' => [
                'required',
                Rule::in([
                    'sections',
                    'events',
                    'characters',
                ]),
            ],
        ])['type'];

        $result = $service->import(
            $type,
            $request->file('csv_file')->getRealPath(),
            $work,
            $request->input('default_status', 'draft')
        );

        $message =
            "CSVから{$result['created']}件を新規登録し、"
            . "{$result['updated']}件を更新しました。";

        return redirect()
            ->route(
                'admin.works.story-sections.csv.create',
                [$work, 'type' => $type]
            )
            ->with('success', $message)
            ->with('csv_errors', $result['errors']);
    }

    public function export(
        Work $work,
        string $type,
        WorkStorySectionCsvService $service
    ): Response {
        $this->authorizeManage();

        $csv = match ($type) {
            'sections' => $service->exportSections($work),
            'events' => $service->exportEvents($work),
            'characters' =>
                $service->exportCharacters($work),
            default => abort(404),
        };

        return $this->response(
            $csv,
            "oshi-wiki-work-{$work->id}-story-{$type}.csv"
        );
    }

    public function sample(
        string $type,
        WorkStorySectionCsvService $service
    ): Response {
        $this->authorizeManage();

        return $this->response(
            $service->sample($type),
            "oshi-wiki-story-{$type}-sample.csv"
        );
    }

    private function response(
        string $csv,
        string $filename
    ): Response {
        return response($csv, 200, [
            'Content-Type' =>
                'text/csv; charset=UTF-8',
            'Content-Disposition' =>
                'attachment; filename="'
                . $filename
                . '"',
        ]);
    }

    private function authorizeManage(): void
    {
        abort_unless(
            auth()->user()?->canManageAllAdminFeatures(),
            403,
            '章・編のCSV操作は最高管理者のみ可能です。'
        );
    }
}
