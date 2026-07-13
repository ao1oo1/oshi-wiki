<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tag\StoreTagRequest;
use App\Http\Requests\Admin\Tag\UpdateTagRequest;
use App\Models\Tag;
use App\Services\TagService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Schema;

class TagController extends Controller
{
    public function __construct(
        private readonly TagService $service
    ) {
    }

    public function index(): View
    {
        $selectedType = request('type');
        $keyword = trim((string) request('keyword', ''));

        $query = \App\Models\Tag::query()
            ->latest();

        if ($selectedType !== null && $selectedType !== '') {
            $query->where('type', $selectedType);
        }

        if ($keyword !== '') {
            $query->where(function ($keywordQuery) use ($keyword) {
                foreach (Schema::getColumnListing('tags') as $column) {
                    if (in_array($column, ['id', 'created_at', 'updated_at', 'deleted_at'], true)) {
                        continue;
                    }

                    $keywordQuery->orWhere($column, 'like', '%' . $keyword . '%');
                }
            });
        }

        $tags = $query
            ->paginate(20)
            ->withQueryString();

        $tagTypes = \App\Models\Tag::query()
            ->select('type')
            ->whereNotNull('type')
            ->where('type', '!=', '')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');


        $currentUser = auth()->user();

        if (isset($tags) && method_exists($tags, 'getCollection')) {
            $tags->getCollection()->transform(function ($model) use ($currentUser) {
                $model->can_modify_by_current_user = $currentUser
                    ? $currentUser->canModifyOwnedAdminContent($model)
                    : false;

                return $model;
            });
        }

        return view('admin.tags.index', [
            'tags' => $tags,
            'tagTypes' => $tagTypes,
            'selectedType' => $selectedType,
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
        $this->ensureCanModifyTag($tag);
        abort_unless(auth()->user()?->canManageAllAdminFeatures(), 403, 'タグ管理のこの操作は最高管理者のみ可能です。');
        return view('admin.tags.edit', [
            'tag' => $tag,
        ]);
    }

    public function update(UpdateTagRequest $request, Tag $tag): RedirectResponse
    {
        $this->ensureCanModifyTag($tag);
        abort_unless(auth()->user()?->canManageAllAdminFeatures(), 403, 'タグ管理のこの操作は最高管理者のみ可能です。');
        $this->service->update($tag, $request->validated());

        return redirect()
            ->route('admin.tags.index')
            ->with('success', 'タグを更新しました。');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $this->ensureCanModifyTag($tag);
$this->service->delete($tag);

        return redirect()
            ->route('admin.tags.index')
            ->with('success', 'タグを削除しました。');
    }
    private function ensureCanModifyTag(Tag $tag): void
    {
        $user = auth()->user();

        abort_unless($user, 403);

        abort_unless(
            $user->canModifyOwnedAdminContent($tag),
            403,
            '他のスタッフまたは最高管理者が登録したタグは編集・削除できません。'
        );
    }

}
