<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WorkStorySection\BulkActionWorkStorySectionRequest;
use App\Models\Work;
use App\Models\WorkStorySection;
use Illuminate\Http\RedirectResponse;

class WorkStorySectionBulkActionController extends Controller
{
    public function __invoke(
        Work $work,
        BulkActionWorkStorySectionRequest $request
    ): RedirectResponse {
        $ids = $request->validated('section_ids');
        $action = $request->validated('bulk_action');

        $sections = WorkStorySection::query()
            ->where('work_id', $work->id)
            ->whereIn('id', $ids)
            ->get();

        if ($sections->count() !== count($ids)) {
            return back()->withErrors([
                'section_ids' =>
                    '対象作品に属さない章・編が含まれています。',
            ]);
        }

        if (
            in_array(
                $action,
                ['publish', 'private', 'draft'],
                true
            )
        ) {
            $status = match ($action) {
                'publish' => 'published',
                'private' => 'private',
                default => 'draft',
            };

            WorkStorySection::query()
                ->whereIn('id', $ids)
                ->update([
                    'status' => $status,
                    'updated_by' => auth()->id(),
                ]);

            return back()->with(
                'success',
                $sections->count()
                . '件の章・編を更新しました。'
            );
        }

        $blocked = $sections->filter(
            fn (WorkStorySection $section) =>
                $section->childSections()->exists()
        );

        if ($blocked->isNotEmpty()) {
            return back()->withErrors([
                'section_ids' =>
                    '子章を持つ編・部が含まれるため'
                    . '削除できません。',
            ]);
        }

        foreach ($sections as $section) {
            $section->delete();
        }

        return back()->with(
            'success',
            $sections->count()
            . '件の章・編に削除フラグを付けました。'
        );
    }
}
