<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WorkStorySection\StoreWorkStorySectionRequest;
use App\Http\Requests\Admin\WorkStorySection\UpdateWorkStorySectionRequest;
use App\Models\Work;
use App\Models\WorkStorySection;
use App\Services\WorkStorySectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class WorkStorySectionController extends Controller
{
    public function __construct(
        private readonly WorkStorySectionService $service
    ) {
    }

    public function index(Work $work): View
    {
        return view('admin.work_story_sections.index', [
            'work' => $work,
            'sections' => $this->service->allForWork($work),
            'limit' =>
                WorkStorySectionService::MAX_SECTIONS_PER_WORK,
        ]);
    }

    public function create(Work $work): View
    {
        $this->authorizeManage();

        return view(
            'admin.work_story_sections.create',
            $this->formData($work)
        );
    }

    public function store(
        StoreWorkStorySectionRequest $request,
        Work $work
    ): RedirectResponse {
        try {
            $section = $this->service->create(
                $work,
                $request->validated()
            );
        } catch (ValidationException $exception) {
            return back()
                ->withInput()
                ->withErrors($exception->errors());
        }

        return redirect()
            ->route(
                'admin.works.story-sections.show',
                [$work, $section]
            )
            ->with('success', '章・編を登録しました。');
    }

    public function show(
        Work $work,
        WorkStorySection $storySection
    ): View {
        $this->service->assertBelongsToWork(
            $work,
            $storySection
        );

        return view('admin.work_story_sections.show', [
            'work' => $work,
            'section' => $storySection->load([
                'parentSection',
                'childSections',
                'events',
                'characters',
            ]),
        ]);
    }

    public function edit(
        Work $work,
        WorkStorySection $storySection
    ): View {
        $this->authorizeManage();
        $this->service->assertBelongsToWork(
            $work,
            $storySection
        );

        return view(
            'admin.work_story_sections.edit',
            array_merge(
                $this->formData($work, $storySection),
                ['section' => $storySection->load([
                    'events',
                    'characters',
                    'childSections',
                ])]
            )
        );
    }

    public function update(
        UpdateWorkStorySectionRequest $request,
        Work $work,
        WorkStorySection $storySection
    ): RedirectResponse {
        try {
            $this->service->update(
                $work,
                $storySection,
                $request->validated()
            );
        } catch (ValidationException $exception) {
            return back()
                ->withInput()
                ->withErrors($exception->errors());
        }

        return redirect()
            ->route(
                'admin.works.story-sections.show',
                [$work, $storySection]
            )
            ->with('success', '章・編を更新しました。');
    }

    public function destroy(
        Work $work,
        WorkStorySection $storySection
    ): RedirectResponse {
        $this->authorizeManage();

        try {
            $this->service->delete($work, $storySection);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return redirect()
            ->route(
                'admin.works.story-sections.index',
                $work
            )
            ->with('success', '章・編を削除しました。');
    }

    private function formData(
        Work $work,
        ?WorkStorySection $current = null
    ): array {
        return [
            'work' => $work,
            'parentSectionOptions' =>
                WorkStorySection::query()
                    ->where('work_id', $work->id)
                    ->whereNull('parent_section_id')
                    ->when(
                        $current,
                        fn ($query) =>
                            $query->whereKeyNot($current->id)
                    )
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get(),
            'characters' => $work
                ->linkedCharacters()
                ->whereNull('characters.deleted_at')
                ->orderBy('characters.name')
                ->get(),
            'sectionTypes' => WorkStorySection::TYPES,
            'spoilerLevels' =>
                WorkStorySection::SPOILER_LEVELS,
            'eventLimit' =>
                WorkStorySectionService::MAX_EVENTS_PER_SECTION,
        ];
    }

    private function authorizeManage(): void
    {
        abort_unless(
            auth()->user()?->canManageAllAdminFeatures(),
            403,
            '章・編の編集は最高管理者のみ可能です。'
        );
    }
}
