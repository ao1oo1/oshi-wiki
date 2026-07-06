<?php

namespace App\Http\Controllers\Admin;

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

    public function create(): View
    {
        // SUPER_ADMIN_ONLY_create
        $this->abortUnlessSuperAdmin();
        return view('admin.works.create');
    }

    public function store(StoreWorkRequest $request): RedirectResponse
    {
        // SUPER_ADMIN_ONLY_store
        $this->abortUnlessSuperAdmin();
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
        // SUPER_ADMIN_ONLY_edit
        $this->abortUnlessSuperAdmin();
        return view('admin.works.edit', [
            'work' => $work->load('tags'),
            'tags' => app(TagService::class)->all(),
        ]);
    }

    public function update(UpdateWorkRequest $request, Work $work): RedirectResponse
    {
        // SUPER_ADMIN_ONLY_update
        $this->abortUnlessSuperAdmin();
        $this->service->update($work, $request->validated());

        return redirect()
            ->route('admin.works.index')
            ->with('success', '作品を更新しました。');
    }

    public function destroy(Work $work): RedirectResponse
    {
        // SUPER_ADMIN_ONLY_destroy
        $this->abortUnlessSuperAdmin();
        abort_unless(auth()->user()?->isSuperAdmin(), 403, '削除操作は最高管理者のみ可能です。');

        $this->service->delete($work);

        return redirect()
            ->route('admin.works.index')
            ->with('success', '作品を削除しました。');
    }

    private function abortUnlessSuperAdmin(): void
    {
        if (! auth()->user()?->is_super_admin) {
            abort(403, 'この操作は最高管理者のみ実行できます。');
        }
    }
}
