<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CharacterRelationship\BulkActionCharacterRelationshipRequest;
use App\Models\CharacterRelationship;
use Illuminate\Http\RedirectResponse;

class CharacterRelationshipBulkActionController extends Controller
{
    public function __invoke(BulkActionCharacterRelationshipRequest $request): RedirectResponse
    {
        abort_unless(auth()->user()?->canManageAllAdminFeatures(), 403, '一括操作は最高管理者のみ可能です。');
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $ids = $request->input('relationship_ids', []);
        $action = $request->input('bulk_action');

        $relationships = CharacterRelationship::query()
            ->whereIn('id', $ids)
            ->get();

        if ($relationships->isEmpty()) {
            return back()->withErrors([
                'relationship_ids' => '対象の関係性が見つかりませんでした。',
            ]);
        }

        if ($action === 'publish') {
            CharacterRelationship::query()
                ->whereIn('id', $ids)
                ->update(['status' => 'published']);

            return back()->with('success', $relationships->count() . '件の関係性を公開にしました。');
        }

        if ($action === 'private') {
            CharacterRelationship::query()
                ->whereIn('id', $ids)
                ->update(['status' => 'private']);

            return back()->with('success', $relationships->count() . '件の関係性を非公開にしました。');
        }

        if ($action === 'delete') {
            foreach ($relationships as $relationship) {
                $relationship->delete();
            }

            return back()->with('success', $relationships->count() . '件の関係性に削除フラグを付けました。');
        }

        return back();
    }
}
