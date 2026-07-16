<?php

namespace App\Http\Controllers\Writer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Writer\OriginalCharacterRelationship\StoreOriginalCharacterRelationshipRequest;
use App\Http\Requests\Writer\OriginalCharacterRelationship\UpdateOriginalCharacterRelationshipRequest;
use App\Models\OriginalCharacterRelationship;
use App\Models\Work;
use App\Repositories\OriginalCharacterRepository;
use App\Services\OriginalCharacterRelationshipService;
use App\Support\WritingAssistLimits;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OriginalCharacterRelationshipController extends Controller
{
    public function __construct(
        private readonly OriginalCharacterRelationshipService $service,
        private readonly OriginalCharacterRepository $characterRepository
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('writer.original_character_relationships.index', [
            'relationships' => $this->service->paginateForUser($user),
            'count' => $this->service->countForUser($user),
            'limit' => WritingAssistLimits::relationshipsPerUser($user),
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();

        return view('writer.original_character_relationships.create', [
            'characters' => $this->characterRepository->allForUser($user),
            'publishedWorks' => $this->publishedWorks(),
            'limit' => WritingAssistLimits::relationshipsPerUser($user),
        ]);
    }

    public function store(
        StoreOriginalCharacterRelationshipRequest $request
    ): RedirectResponse {
        $relationship = $this->service->createForUser(
            $request->user(),
            $request->validated()
        );

        return redirect()
            ->route(
                'writer.original-character-relationships.show',
                $relationship
            )
            ->with('success', '関係性を登録しました。');
    }

    public function show(
        Request $request,
        OriginalCharacterRelationship $originalCharacterRelationship
    ): View {
        $this->authorizeOwner(
            $request,
            $originalCharacterRelationship
        );

        return view(
            'writer.original_character_relationships.show',
            [
                'relationship' =>
                    $originalCharacterRelationship->load([
                        'fromCharacter',
                        'toCharacter',
                        'fromV1Character.work',
                        'fromV1Character.linkedWorks',
                        'toV1Character.work',
                        'toV1Character.linkedWorks',
                    ]),
            ]
        );
    }

    public function edit(
        Request $request,
        OriginalCharacterRelationship $originalCharacterRelationship
    ): View {
        $this->authorizeOwner(
            $request,
            $originalCharacterRelationship
        );

        $user = $request->user();

        return view(
            'writer.original_character_relationships.edit',
            [
                'relationship' =>
                    $originalCharacterRelationship->load([
                        'fromCharacter',
                        'toCharacter',
                        'fromV1Character.work',
                        'fromV1Character.linkedWorks',
                        'toV1Character.work',
                        'toV1Character.linkedWorks',
                    ]),
                'characters' =>
                    $this->characterRepository->allForUser($user),
                'publishedWorks' => $this->publishedWorks(),
            ]
        );
    }

    public function update(
        UpdateOriginalCharacterRelationshipRequest $request,
        OriginalCharacterRelationship $originalCharacterRelationship
    ): RedirectResponse {
        $this->authorizeOwner(
            $request,
            $originalCharacterRelationship
        );

        $this->service->updateForUser(
            $request->user(),
            $originalCharacterRelationship,
            $request->validated()
        );

        return redirect()
            ->route(
                'writer.original-character-relationships.show',
                $originalCharacterRelationship
            )
            ->with('success', '関係性を更新しました。');
    }

    public function destroy(
        Request $request,
        OriginalCharacterRelationship $originalCharacterRelationship
    ): RedirectResponse {
        $this->authorizeOwner(
            $request,
            $originalCharacterRelationship
        );

        $this->service->delete(
            $originalCharacterRelationship
        );

        return redirect()
            ->route(
                'writer.original-character-relationships.index'
            )
            ->with('success', '関係性を削除しました。');
    }

    public function duplicate(
        Request $request,
        OriginalCharacterRelationship $originalCharacterRelationship
    ): RedirectResponse {
        $this->authorizeOwner(
            $request,
            $originalCharacterRelationship
        );

        $copy = $originalCharacterRelationship->replicate();
        $copy->user_id = $request->user()->id;
        $copy->save();

        return redirect()
            ->route(
                'writer.original-character-relationships.edit',
                $copy
            )
            ->with(
                'success',
                '関係性を複製しました。内容を確認して保存してください。'
            );
    }

    private function authorizeOwner(
        Request $request,
        OriginalCharacterRelationship $relationship
    ): void {
        abort_unless(
            (int) $relationship->user_id
                === (int) $request->user()?->id,
            403
        );
    }

    private function publishedWorks()
    {
        return Work::query()
            ->with([
                'parentWork',
                'linkedCharacters' => function ($query): void {
                    $query
                        ->where('characters.status', 'published')
                        ->orderBy('characters.name');
                },
            ])
            ->where('status', 'published')
            ->whereHas('linkedCharacters', function ($query): void {
                $query->where('characters.status', 'published');
            })
            ->where(function ($query): void {
                $query
                    ->whereNull('parent_work_id')
                    ->orWhereHas(
                        'parentWork',
                        fn ($parentQuery) =>
                            $parentQuery->where(
                                'status',
                                'published'
                            )
                    );
            })
            ->orderByRaw(
                'COALESCE(parent_work_id, id)'
            )
            ->orderBy('parent_work_id')
            ->orderBy('child_sort_order')
            ->orderBy('title')
            ->get()
            ->each(function (Work $work): void {
                $work->setRelation(
                    'characters',
                    $work->linkedCharacters
                );
            });
    }
}
