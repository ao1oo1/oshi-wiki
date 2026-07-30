<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CharacterRelationship\MergeCharacterRelationshipDuplicatesRequest;
use App\Services\Admin\CharacterRelationshipDuplicateMergeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class CharacterRelationshipDuplicateController extends Controller
{
    public function __construct(
        private readonly CharacterRelationshipDuplicateMergeService $service
    ) {
    }

    public function index(): View
    {
        $this->authorizeSuperAdmin();

        $groups = $this->service->groups();

        return view(
            'admin.character_relationships.duplicates',
            [
                'duplicateGroups' => $groups,
                'duplicateGroupCount' => $groups->count(),
                'duplicateRelationshipCount' =>
                    $groups->sum('duplicate_count'),
            ]
        );
    }

    public function merge(
        MergeCharacterRelationshipDuplicatesRequest $request
    ): RedirectResponse {
        $this->authorizeSuperAdmin();

        try {
            $result = $this->service->mergeTokens(
                $request->validated('duplicate_groups')
            );

            $message =
                $result['merged_groups']
                . 'グループを整理し、'
                . $result['deleted_relationships']
                . '件の重複関係性に'
                . '削除フラグを付けました。';

            if ($result['skipped_groups'] > 0) {
                $message .=
                    ' '
                    . $result['skipped_groups']
                    . 'グループは既に重複が'
                    . '解消されていたため'
                    . 'スキップしました。';
            }

            return redirect()
                ->route(
                    'admin.character-relationships'
                    . '.duplicates.index'
                )
                ->with('success', $message);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route(
                    'admin.character-relationships'
                    . '.duplicates.index'
                )
                ->with(
                    'error',
                    '重複関係性を整理できませんでした。'
                    . '処理はすべて取り消されています。'
                );
        }
    }

    private function authorizeSuperAdmin(): void
    {
        abort_unless(
            auth()->user()?->canManageAllAdminFeatures(),
            403,
            '関係性の重複チェック機能は'
            . '最高管理者のみ利用できます。'
        );
    }
}
