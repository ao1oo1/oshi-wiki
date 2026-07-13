<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tag;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkRequest;
use App\Http\Requests\UpdateWorkRequest;
use App\Models\Work;
use App\Services\WorkService;
use App\Services\TagService;
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
        $keyword = request('keyword');
        $selectedTagId = request('tag_id');


        $currentUser = auth()->user();

        if (isset($works) && method_exists($works, 'getCollection')) {
            $works->getCollection()->transform(function ($model) use ($currentUser) {
                $model->can_modify_by_current_user = $currentUser
                    ? $currentUser->canModifyOwnedAdminContent($model)
                    : false;

                return $model;
            });
        }

        return view('admin.works.index', [
            'works' => $this->service->paginate(
                20,
                $keyword,
                $selectedTagId ? (int) $selectedTagId : null
            ),
            'keyword' => $keyword,
            'selectedTagId' => $selectedTagId,
            'tags' => app(TagService::class)->all(),
        ]);
    }

    public function create()
    {
        $tags = Tag::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        return view('admin.works.create', compact('tags'));
    }

    public function store(StoreWorkRequest $request): RedirectResponse
    {
        abort_unless(auth()->user()?->canManageAllAdminFeatures(), 403, '作品管理のこの操作は最高管理者のみ可能です。');
        $this->service->create($request->validated());

        return redirect()
            ->route('admin.works.index')
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
        abort_unless(auth()->user()?->canManageAllAdminFeatures(), 403, '作品管理のこの操作は最高管理者のみ可能です。');
        return view('admin.works.edit', [
            'work' => $work->load('tags'),
            'tags' => app(TagService::class)->all(),
        ]);
    }

    public function update(UpdateWorkRequest $request, Work $work): RedirectResponse
    {
        $this->ensureCanModifyWork($work);
        abort_unless(auth()->user()?->canManageAllAdminFeatures(), 403, '作品管理のこの操作は最高管理者のみ可能です。');
        $this->service->update($work, $request->validated());

        return redirect()
            ->route('admin.works.index')
            ->with('success', '作品を更新しました。');
    }

    public function destroy(Work $work): RedirectResponse
    {
        $this->ensureCanModifyWork($work);
        abort_unless(auth()->user()?->canManageAllAdminFeatures(), 403, '作品管理のこの操作は最高管理者のみ可能です。');
$this->service->delete($work);

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
