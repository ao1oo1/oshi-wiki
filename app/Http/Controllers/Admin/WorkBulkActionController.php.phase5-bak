<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Work\BulkActionWorkRequest;
use App\Models\Work;
use Illuminate\Http\RedirectResponse;

class WorkBulkActionController extends Controller
{
    public function __invoke(BulkActionWorkRequest $request): RedirectResponse
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $ids = $request->input('work_ids', []);
        $action = $request->input('bulk_action');

        $works = Work::query()->whereIn('id', $ids)->get();

        if ($works->isEmpty()) {
            return back()->withErrors([
                'work_ids' => '対象の作品が見つかりませんでした。',
            ]);
        }

        if ($action === 'publish') {
            Work::query()->whereIn('id', $ids)->update(['status' => 'published']);

            return back()->with('success', $works->count() . '件の作品を公開にしました。');
        }

        if ($action === 'private') {
            Work::query()->whereIn('id', $ids)->update(['status' => 'private']);

            return back()->with('success', $works->count() . '件の作品を非公開にしました。');
        }

        if ($action === 'delete') {
            foreach ($works as $work) {
                $work->delete();
            }

            return back()->with('success', $works->count() . '件の作品に削除フラグを付けました。');
        }

        return back();
    }
}
