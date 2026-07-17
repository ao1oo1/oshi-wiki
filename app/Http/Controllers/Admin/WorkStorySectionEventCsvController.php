<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WorkStorySectionEvent\ImportIndividualEventCsvRequest;
use App\Models\Work;
use App\Models\WorkStorySection;
use App\Services\WorkStorySectionEventCsvService;
use App\Services\WorkStorySectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class WorkStorySectionEventCsvController extends Controller
{
    public function create(
        Work $work,
        WorkStorySection $storySection,
        WorkStorySectionService $sectionService
    ): View {
        $this->authorizeManage();
        $sectionService->assertBelongsToWork(
            $work,
            $storySection
        );

        return view(
            'admin.work_story_section_events.csv_import',
            [
                'work' => $work,
                'section' => $storySection->loadCount(
                    'events'
                ),
                'eventLimit' =>
                    WorkStorySectionService::MAX_EVENTS_PER_SECTION,
            ]
        );
    }

    public function store(
        ImportIndividualEventCsvRequest $request,
        Work $work,
        WorkStorySection $storySection,
        WorkStorySectionService $sectionService,
        WorkStorySectionEventCsvService $csvService
    ): RedirectResponse {
        $this->authorizeManage();
        $sectionService->assertBelongsToWork(
            $work,
            $storySection
        );

        $result = $csvService->import(
            $request->file('csv_file')->getRealPath(),
            $storySection
        );

        $message =
            "物語詳細を{$result['created']}件追加し、"
            . "{$result['updated']}件更新しました。";

        if ($result['skipped'] > 0) {
            $message .=
                " 空行{$result['skipped']}件を"
                . 'スキップしました。';
        }

        return redirect()
            ->route(
                'admin.works.story-sections.events.csv.create',
                [$work, $storySection]
            )
            ->with('success', $message)
            ->with('csv_errors', $result['errors']);
    }

    public function export(
        Work $work,
        WorkStorySection $storySection,
        WorkStorySectionService $sectionService,
        WorkStorySectionEventCsvService $csvService
    ): Response {
        $this->authorizeManage();
        $sectionService->assertBelongsToWork(
            $work,
            $storySection
        );

        return $this->csvResponse(
            $csvService->export($storySection),
            "oshi-wiki-work-{$work->id}-"
            . "section-{$storySection->id}-events.csv"
        );
    }

    public function sample(
        WorkStorySectionEventCsvService $csvService
    ): Response {
        $this->authorizeManage();

        return $this->csvResponse(
            $csvService->sample(),
            'oshi-wiki-story-event-individual-sample.csv'
        );
    }

    private function csvResponse(
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
            '物語詳細のCSV操作は'
            . '最高管理者のみ利用できます。'
        );
    }
}
