<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkRequest;
use App\Http\Requests\UpdateWorkRequest;
use App\Models\Tag;
use App\Models\Work;
use App\Services\TagService;
use App\Services\WorkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WorkController extends Controller
{
    public function __construct(
        private readonly WorkService $service
    ) {
    }

    public function index(): View
    {
        $keyword = trim((string) request('keyword', ''));
        $selectedTagId = request('tag_id');
        $selectedStatus = request('status');
        $exactKeyword = trim((string) request('exact_keyword', ''));
        $selectedWorkType = trim((string) request('work_type', ''));
        $selectedParentWorkId = request('parent_work_id');

        return view('admin.works.index', [
            'works' => $this->service->paginate(
                20,
                $keyword !== '' ? $keyword : null,
                $selectedTagId ? (int) $selectedTagId : null,
                $selectedStatus ?: null,
                $exactKeyword !== '' ? $exactKeyword : null,
                $selectedWorkType !== '' ? $selectedWorkType : null,
                $selectedParentWorkId
                    ? (int) $selectedParentWorkId
                    : null
            ),
            'keyword' => $keyword,
            'selectedTagId' => $selectedTagId,
            'selectedStatus' => $selectedStatus,
            'exactKeyword' => $exactKeyword,
            'selectedWorkType' => $selectedWorkType,
            'selectedParentWorkId' => $selectedParentWorkId,
            'parentWorkOptions' => Work::query()
                ->whereNull('parent_work_id')
                ->whereHas('childWorks')
                ->orderBy('title')
                ->get(),
            'tags' => app(TagService::class)->all(),
        ]);
    }

    public function create(): View
    {
        abort_unless(
            auth()->user()?->canManageAllAdminFeatures(),
            403,
            '作品管理のこの操作は最高管理者のみ可能です。'
        );

        return view('admin.works.create', [
            'tags' => Tag::query()
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get(),
            'parentWorkOptions' => Work::query()
                ->whereNull('parent_work_id')
                ->orderBy('title')
                ->get(),
        ]);
    }

    public function store(StoreWorkRequest $request): RedirectResponse
    {
        abort_unless(
            auth()->user()?->canManageAllAdminFeatures(),
            403,
            '作品管理のこの操作は最高管理者のみ可能です。'
        );

        $work = $this->service->create($request->validated());

        return redirect()
            ->route('admin.works.show', $work)
            ->with('success', '作品を登録しました。');
    }

    public function show(Work $work): View
    {
        return view('admin.works.show', [
            'work' => $this->service->findWithDetails($work),
        ]);
    }

    public function edit(Work $work): View
    {
        $this->ensureCanModifyWork($work);

        abort_unless(
            auth()->user()?->canManageAllAdminFeatures(),
            403,
            '作品管理のこの操作は最高管理者のみ可能です。'
        );

        return view('admin.works.edit', [
            'work' => $work->load([
                'tags',
                'parentWork',
                'childWorks',
                'canonEvents',
                'termUsages',
            ]),
            'tags' => app(TagService::class)->all(),
            'parentWorkOptions' => Work::query()
                ->whereNull('parent_work_id')
                ->whereKeyNot($work->id)
                ->orderBy('title')
                ->get(),
        ]);
    }

    public function update(UpdateWorkRequest $request, Work $work): RedirectResponse
    {
        $this->ensureCanModifyWork($work);

        abort_unless(
            auth()->user()?->canManageAllAdminFeatures(),
            403,
            '作品管理のこの操作は最高管理者のみ可能です。'
        );

        $this->service->update($work, $request->validated());

        return redirect()
            ->route('admin.works.show', $work)
            ->with('success', '作品を更新しました。');
    }

    public function destroy(Work $work): RedirectResponse
    {
        $this->ensureCanModifyWork($work);

        abort_unless(
            auth()->user()?->canManageAllAdminFeatures(),
            403,
            '作品管理のこの操作は最高管理者のみ可能です。'
        );

        try {
            $this->service->delete($work);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return back()
                ->withErrors($exception->errors());
        }

        return redirect()
            ->route('admin.works.index')
            ->with('success', '作品を削除しました。');
    }

    private function ensureCanModifyWork(Work $work): void
    {
        $user = auth()->user();

        abort_unless($user, 403);
        abort_unless(
            $user->canModifyOwnedAdminContent($work),
            403,
            '他のスタッフまたは最高管理者が登録した作品は編集・削除できません。'
        );
    }
}
