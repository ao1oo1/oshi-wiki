<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CharacterRelationship\StoreCharacterRelationshipRequest;
use App\Http\Requests\Admin\CharacterRelationship\UpdateCharacterRelationshipRequest;
use App\Models\Character;
use App\Models\CharacterRelationship;
use App\Models\Work;
use App\Services\CharacterRelationshipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CharacterRelationshipController extends Controller
{
    public function __construct(
        private readonly CharacterRelationshipService $service
    ) {
    }

    public function index(): View
    {
        $selectedWorkId = request('work_id');
        $keyword = trim((string) request('keyword', ''));
        $selectedStatus = request('status');
        $exactKeyword = trim((string) request('exact_keyword', ''));


        $currentUser = auth()->user();

        if (isset($relationships) && method_exists($relationships, 'getCollection')) {
            $relationships->getCollection()->transform(function ($model) use ($currentUser) {
                $model->can_modify_by_current_user = $currentUser
                    ? $currentUser->canModifyOwnedAdminContent($model)
                    : false;

                return $model;
            });
        }

        return view('admin.character_relationships.index', [
            'characterRelationships' => $this->service->paginate(
                20,
                $selectedWorkId ? (int) $selectedWorkId : null,
                $keyword !== '' ? $keyword : null,
                $selectedStatus ?: null,
                $exactKeyword !== '' ? $exactKeyword : null
            ),
            'works' => Work::query()->latest()->get(),
            'selectedWorkId' => $selectedWorkId,
            'selectedStatus' => $selectedStatus,
            'exactKeyword' => $exactKeyword,
            'keyword' => $keyword,
        ]);
    }

    public function create(): View
    {
        $selectedWorkId = request('work_id');

        return view('admin.character_relationships.create', [
            'works' => Work::query()->latest()->get(),
            'characters' => Character::query()
                ->with('work')
                ->when($selectedWorkId, function ($query) use ($selectedWorkId) {
                    $query->where('work_id', $selectedWorkId);
                })
                ->latest()
                ->get(),
            'selectedWorkId' => $selectedWorkId,
        ]);
    }

    public function store(StoreCharacterRelationshipRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // STAFF_RELATIONSHIP_REVIEW_STATUS_FIX
        if (! auth()->user()?->canManageAllAdminFeatures()) {
            $data['status'] = 'draft';
            $data['review_status'] = 'pending';
        }
        // /STAFF_RELATIONSHIP_REVIEW_STATUS_FIX
        $returnToWorkId = $data['return_to_work_id'] ?? null;
        unset($data['return_to_work_id']);

        $this->service->create($data);

        if ($returnToWorkId) {
            return redirect()
            ->route('admin.character-relationships.index')
            ->with('success', '関係性を追加しました。');
        }

        return redirect()
            ->route('admin.character-relationships.index')
            ->with('success', '関係性を追加しました。');
    }

    public function edit(CharacterRelationship $characterRelationship): View
    {
        $this->ensureCanModifyRelationship($characterRelationship);

        return view('admin.character_relationships.edit', [
            'characterRelationship' => $characterRelationship,
            'works' => Work::query()->latest()->get(),
            'characters' => Character::query()
                ->with('work')
                ->where('work_id', $characterRelationship->work_id)
                ->latest()
                ->get(),
            'selectedWorkId' => $characterRelationship->work_id,
        ]);
    }

    public function update(
        UpdateCharacterRelationshipRequest $request,
        CharacterRelationship $characterRelationship
    ): RedirectResponse {
        $this->ensureCanModifyRelationship($characterRelationship);

        $data = $request->validated();

        // STAFF_RELATIONSHIP_REVIEW_STATUS_FIX
        if (! auth()->user()?->canManageAllAdminFeatures()) {
            $data['status'] = 'draft';
            $data['review_status'] = 'pending';
        }
        // /STAFF_RELATIONSHIP_REVIEW_STATUS_FIX

        $this->service->update($characterRelationship, $data);

        return redirect()
            ->route('admin.character-relationships.index')
            ->with('success', 'キャラクター関係性を更新しました。');
    }

    public function destroy(CharacterRelationship $characterRelationship): RedirectResponse
    {
        $this->ensureCanModifyRelationship($characterRelationship);

        $this->service->delete($characterRelationship);

        return redirect()
            ->route('admin.character-relationships.index')
            ->with('success', 'キャラクター関係性を削除しました。');
    }

    private function ensureCanModifyRelationship(CharacterRelationship $characterRelationship): void
    {
        $user = auth()->user();

        abort_unless($user, 403);

        abort_unless(
            $user->canModifyOwnedAdminContent($characterRelationship),
            403,
            '他のスタッフまたは最高管理者が登録した関係性は編集・削除できません。'
        );
    }


}
