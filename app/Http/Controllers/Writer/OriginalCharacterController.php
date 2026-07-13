<?php

namespace App\Http\Controllers\Writer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Writer\OriginalCharacter\StoreOriginalCharacterRequest;
use App\Http\Requests\Writer\OriginalCharacter\UpdateOriginalCharacterRequest;
use App\Models\OriginalCharacter;
use App\Services\OriginalCharacterService;
use App\Support\WritingAssistLimits;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OriginalCharacterController extends Controller
{
    public function __construct(
        private readonly OriginalCharacterService $service
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('writer.original_characters.index', [
            'originalCharacters' => $this->service->paginateForUser($user),
            'count' => $this->service->countForUser($user),
            'limit' => WritingAssistLimits::originalCharactersPerUser($user),
        ]);
    }

    public function create(Request $request): View
    {
        return view('writer.original_characters.create', [
            'limit' => WritingAssistLimits::originalCharactersPerUser($request->user()),
        ]);
    }

    public function store(StoreOriginalCharacterRequest $request): RedirectResponse
    {
        $originalCharacter = $this->service->createForUser(
            $request->user(),
            $request->validated(),
            $request->file('character_image')
        );

        return redirect()
            ->route('writer.original-characters.show', $originalCharacter)
            ->with('success', 'オリジナルキャラクターを登録しました。');
    }

    public function show(
        Request $request,
        OriginalCharacter $originalCharacter
    ): View {
        $this->authorizeOwner($request, $originalCharacter);

        return view('writer.original_characters.show', [
            'originalCharacter' => $originalCharacter,
        ]);
    }

    public function image(
        Request $request,
        OriginalCharacter $originalCharacter
    ): BinaryFileResponse {
        $this->authorizeOwner($request, $originalCharacter);

        abort_unless($originalCharacter->image_path, 404);
        abort_unless(
            Storage::disk('local')->exists($originalCharacter->image_path),
            404
        );

        $absolutePath = Storage::disk('local')->path(
            $originalCharacter->image_path
        );

        return response()->file($absolutePath, [
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function edit(
        Request $request,
        OriginalCharacter $originalCharacter
    ): View {
        $this->authorizeOwner($request, $originalCharacter);

        return view('writer.original_characters.edit', [
            'originalCharacter' => $originalCharacter,
        ]);
    }

    public function update(
        UpdateOriginalCharacterRequest $request,
        OriginalCharacter $originalCharacter
    ): RedirectResponse {
        $this->authorizeOwner($request, $originalCharacter);

        $this->service->update(
            $originalCharacter,
            $request->validated(),
            $request->file('character_image'),
            $request->boolean('remove_image')
        );

        return redirect()
            ->route('writer.original-characters.show', $originalCharacter)
            ->with('success', 'オリジナルキャラクターを更新しました。');
    }

    public function destroy(
        Request $request,
        OriginalCharacter $originalCharacter
    ): RedirectResponse {
        $this->authorizeOwner($request, $originalCharacter);

        $this->service->delete($originalCharacter);

        return redirect()
            ->route('writer.original-characters.index')
            ->with('success', 'オリジナルキャラクターを削除しました。');
    }

    private function authorizeOwner(
        Request $request,
        OriginalCharacter $originalCharacter
    ): void {
        $user = $request->user();

        abort_unless(
            (int) $originalCharacter->user_id === (int) $user?->id,
            403
        );
    }
}
