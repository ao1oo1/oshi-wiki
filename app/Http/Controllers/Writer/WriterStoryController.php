<?php

namespace App\Http\Controllers\Writer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Writer\Story\StoreWriterStoryRequest;
use App\Http\Requests\Writer\Story\UpdateWriterStoryRequest;
use App\Models\WriterStory;
use App\Services\WriterStoryService;
use App\Support\WritingAssistLimits;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WriterStoryController extends Controller
{
    public function __construct(
        private readonly WriterStoryService $service
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $filters = array_filter([
            'keyword' =>
                $request->string('keyword')->trim()->toString(),
            'status' =>
                $request->string('status')->trim()->toString(),
            'sort' =>
                $request->string('sort')->trim()->toString(),
        ], fn ($value) => $value !== '');

        return view('writer.stories.index', [
            'stories' =>
                $this->service->paginateForUser($user, $filters),
            'count' => $this->service->countForUser($user),
            'limit' => WritingAssistLimits::storiesPerUser($user),
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): View
    {
        return view('writer.stories.create', [
            'count' => $this->service->countForUser($request->user()),
            'limit' =>
                WritingAssistLimits::storiesPerUser($request->user()),
        ]);
    }

    public function store(
        StoreWriterStoryRequest $request
    ): RedirectResponse {
        $story = $this->service->createForUser(
            $request->user(),
            $request->validated()
        );

        return redirect()
            ->route('writer.stories.show', $story)
            ->with('success', 'ストーリーを登録しました。');
    }

    public function show(
        Request $request,
        WriterStory $story
    ): View {
        $this->authorizeOwner($request, $story);

        return view('writer.stories.show', [
            'story' => $story,
        ]);
    }

    public function edit(
        Request $request,
        WriterStory $story
    ): View {
        $this->authorizeOwner($request, $story);

        return view('writer.stories.edit', [
            'story' => $story,
        ]);
    }

    public function update(
        UpdateWriterStoryRequest $request,
        WriterStory $story
    ): RedirectResponse {
        $this->authorizeOwner($request, $story);

        $this->service->update(
            $story,
            $request->validated()
        );

        return redirect()
            ->route('writer.stories.show', $story)
            ->with('success', 'ストーリーを更新しました。');
    }

    public function destroy(
        Request $request,
        WriterStory $story
    ): RedirectResponse {
        $this->authorizeOwner($request, $story);

        $this->service->delete($story);

        return redirect()
            ->route('writer.stories.index')
            ->with('success', 'ストーリーを削除しました。');
    }

    private function authorizeOwner(
        Request $request,
        WriterStory $story
    ): void {
        abort_unless(
            (int) $story->user_id ===
                (int) $request->user()?->id,
            403
        );
    }
}
