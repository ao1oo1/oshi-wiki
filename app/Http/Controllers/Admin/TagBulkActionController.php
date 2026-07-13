<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tag\BulkActionTagRequest;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;

class TagBulkActionController extends Controller
{
    public function __invoke(BulkActionTagRequest $request): RedirectResponse
    {
        abort_unless(auth()->user()?->canManageAllAdminFeatures(), 403, '一括操作は最高管理者のみ可能です。');
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $ids = $request->input('tag_ids', []);
        $action = $request->input('bulk_action');

        $tags = Tag::query()
            ->whereIn('id', $ids)
            ->get();

        if ($tags->isEmpty()) {
            return back()->withErrors([
                'tag_ids' => '対象のタグが見つかりませんでした。',
            ]);
        }

        if ($action === 'publish') {
            Tag::query()
                ->whereIn('id', $ids)
                ->update(['status' => 'published']);

            return back()->with('success', $tags->count() . '件のタグを公開にしました。');
        }

        if ($action === 'private') {
            Tag::query()
                ->whereIn('id', $ids)
                ->update(['status' => 'private']);

            return back()->with('success', $tags->count() . '件のタグを非公開にしました。');
        }

        if ($action === 'delete') {
            foreach ($tags as $tag) {
                $tag->delete();
            }

            return back()->with('success', $tags->count() . '件のタグに削除フラグを付けました。');
        }

        return back();
    }
}
