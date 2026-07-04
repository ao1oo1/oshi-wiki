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

        return view('admin.characters.index', [
            'characters' => $this->service->paginate(
                20,
                $selectedWorkId ? (int) $selectedWorkId : null,
                $keyword,
                $selectedTagId ? (int) $selectedTagId : null
            ),
            'works' => Work::query()->latest()->get(),
            'tags' => app(TagService::class)->all(),
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
            'tags' => app(TagService::class)->all(),
        ]);
    }

    public function store(StoreCharacterRequest $request): RedirectResponse
    {
        $data = $request->validated();
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
        return view('admin.characters.edit', [
            'character' => $character->load('tags'),
            'works' => Work::query()->latest()->get(),
            'tags' => app(TagService::class)->all(),
        ]);
    }

    public function update(UpdateCharacterRequest $request, Character $character): RedirectResponse
    {
        $this->service->update($character, $request->validated());

        return redirect()
            ->route('admin.characters.show', $character)
            ->with('success', 'キャラクターを更新しました。');
    }

    public function destroy(Character $character): RedirectResponse
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403, '削除操作は最高管理者のみ可能です。');

        $this->service->delete($character);

        return redirect()
            ->route('admin.characters.index')
            ->with('success', 'キャラクターを削除しました。');
    }
}
