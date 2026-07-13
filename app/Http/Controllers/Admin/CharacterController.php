<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Character\StoreCharacterRequest;
use App\Http\Requests\Admin\Character\UpdateCharacterRequest;
use App\Models\Character;
use App\Models\Work;
use App\Services\CharacterService;
use App\Services\TagService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CharacterController extends Controller
{
    public function __construct(
        private readonly CharacterService $service
    ) {
    }

    public function index(): View
    {
        $selectedWorkId = request('work_id');
        $keyword = request('keyword');
        $selectedTagId = request('tag_id');

        $characters = $this->service->paginate(
            20,
            $selectedWorkId ? (int) $selectedWorkId : null,
            $keyword,
            $selectedTagId ? (int) $selectedTagId : null
        );

        // CHARACTER_INDEX_CAN_MODIFY_FLAG_FIX
        $currentUser = auth()->user();

        $characters->getCollection()->transform(function (Character $character) use ($currentUser) {
            $character->can_modify_by_current_user = $currentUser
                && (
                    $currentUser->canManageAllAdminFeatures()
                    || (
                        $currentUser->isStaff()
                        && ! is_null($character->created_by)
                        && (int) $character->created_by === (int) $currentUser->id
                    )
                );

            return $character;
        });
        // /CHARACTER_INDEX_CAN_MODIFY_FLAG_FIX

        return view('admin.characters.index', [
            'characters' => $characters,
            'works' => Work::query()->latest()->get(),
            'tags' => \App\Models\Tag::query()
                ->where('type', 'character')
                ->orderBy('name')
                ->get(),
            'selectedWorkId' => $selectedWorkId,
            'selectedTagId' => $selectedTagId,
            'keyword' => $keyword,
        ]);
    }

    public function create(): View
    {
        return view('admin.characters.create', [
            'works' => Work::query()->latest()->get(),
            'selectedWorkId' => request('work_id'),
            'tags' => \App\Models\Tag::query()
                ->where('type', 'character')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(StoreCharacterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // STAFF_CHARACTER_REVIEW_STATUS_FIX
        if (! auth()->user()?->canManageAllAdminFeatures()) {
            $data['status'] = 'draft';
            $data['review_status'] = 'pending';
        }
        // /STAFF_CHARACTER_REVIEW_STATUS_FIX
        $returnToWorkId = $data['return_to_work_id'] ?? null;
        unset($data['return_to_work_id']);

        $this->service->create($data);

        if ($returnToWorkId) {
            return redirect()
                ->route('admin.works.show', $returnToWorkId)
                ->with('success', 'キャラクターを登録しました。');
        }

        return redirect()
            ->route('admin.characters.index')
            ->with('success', 'キャラクターを登録しました。');
    }

    public function show(Character $character): View
    {
        return view('admin.characters.show', [
            'character' => $this->service->findWithWork($character),
        ]);
    }

    public function edit(Character $character): View
    {
        $this->ensureCanModifyCharacter($character);

        return view('admin.characters.edit', [
            'character' => $character->load('tags'),
            'works' => Work::query()->latest()->get(),
            'tags' => \App\Models\Tag::query()
                ->where('type', 'character')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(UpdateCharacterRequest $request, Character $character): RedirectResponse
    {
        $this->ensureCanModifyCharacter($character);

        $data = $request->validated();

        // STAFF_CHARACTER_REVIEW_STATUS_FIX
        if (! auth()->user()?->canManageAllAdminFeatures()) {
            $data['status'] = 'draft';
            $data['review_status'] = 'pending';
        }
        // /STAFF_CHARACTER_REVIEW_STATUS_FIX

        $this->service->update($character, $data);

        return redirect()
            ->route('admin.characters.show', $character)
            ->with('success', 'キャラクターを更新しました。');
    }

    public function destroy(Character $character): RedirectResponse
    {
        $this->ensureCanModifyCharacter($character);

        $this->service->delete($character);

        return redirect()
            ->route('admin.characters.index')
            ->with('success', 'キャラクターを削除しました。');
    }

    private function ensureCanModifyCharacter(Character $character): void
    {
        $user = auth()->user();

        abort_unless($user, 403);

        abort_unless(
            $user->canModifyOwnedAdminContent($character),
            403,
            '他のスタッフまたは最高管理者が登録したキャラクターは編集・削除できません。'
        );
    }


}
