<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tag\StoreTagRequest;
use App\Http\Requests\Admin\Tag\UpdateTagRequest;
use App\Models\Tag;
use App\Services\TagService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TagController extends Controller
{
    public function __construct(
        private readonly TagService $service
    ) {
    }

    public function index(): View
    {
        $keyword = request('keyword');

        return view('admin.tags.index', [
            'tags' => $this->service->paginate(20, $keyword),
            'keyword' => $keyword,
        ]);
    }

    public function store(StoreTagRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()
            ->route('admin.tags.index')
            ->with('success', 'タグを登録しました。');
    }

    public function edit(Tag $tag): View
    {
        return view('admin.tags.edit', [
            'tag' => $tag,
        ]);
    }

    public function update(UpdateTagRequest $request, Tag $tag): RedirectResponse
    {
        $this->service->update($tag, $request->validated());

        return redirect()
            ->route('admin.tags.index')
            ->with('success', 'タグを更新しました。');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403, '削除操作は最高管理者のみ可能です。');

        $this->service->delete($tag);

        return redirect()
            ->route('admin.tags.index')
            ->with('success', 'タグを削除しました。');
    }
}
