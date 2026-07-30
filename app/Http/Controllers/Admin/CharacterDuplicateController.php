<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Character\MergeCharacterDuplicatesRequest;
use App\Services\Admin\CharacterDuplicateMergeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class CharacterDuplicateController extends Controller
{
    public function __construct(
        private readonly CharacterDuplicateMergeService $service
    ) {
    }

    public function index(): View
    {
        $this->authorizeSuperAdmin();

        $groups = $this->service->groups();

        return view(
            'admin.characters.duplicates',
            [
                'duplicateGroups' => $groups,
                'duplicateGroupCount' => $groups->count(),
                'duplicateCharacterCount' =>
                    $groups->sum('duplicate_count'),
            ]
        );
    }

    public function merge(
        MergeCharacterDuplicatesRequest $request
    ): RedirectResponse {
        $this->authorizeSuperAdmin();

        try {
            $result = $this->service->mergeTokens(
                $request->validated(
                    'duplicate_groups'
                )
            );

            $message =
                $result['merged_groups']
                . 'グループを統合し、'
                . $result['deleted_characters']
                . '件の重複キャラクターに'
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
                    'admin.characters.duplicates.index'
                )
                ->with('success', $message);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route(
                    'admin.characters.duplicates.index'
                )
                ->with(
                    'error',
                    '重複キャラクターを'
                    . '統合できませんでした。'
                    . '処理はすべて取り消されています。'
                );
        }
    }

    private function authorizeSuperAdmin(): void
    {
        abort_unless(
            auth()->user()?->canManageAllAdminFeatures(),
            403,
            '重複チェック機能は'
            . '最高管理者のみ利用できます。'
        );
    }
}
